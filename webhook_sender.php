<?php

/*
 * webhook_sender.php
 *
 * Sends outbound webhooks to registered endpoints.
 * Can be used as a library (include + call dispatchWebhook()) or
 * called directly via HTTP POST for ad-hoc/test dispatches.
 *
 * Required DB tables (run once):
 *
 *   CREATE TABLE webhook_endpoints (
 *     id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     event      VARCHAR(100)  NOT NULL,
 *     url        VARCHAR(2048) NOT NULL,
 *     secret     VARCHAR(255)  NOT NULL DEFAULT '',
 *     active     TINYINT(1)    NOT NULL DEFAULT 1,
 *     created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
 *   );
 *
 *   CREATE TABLE webhook_log (
 *     id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     event      VARCHAR(100)  NOT NULL,
 *     url        VARCHAR(2048) NOT NULL,
 *     http_code  SMALLINT      NOT NULL DEFAULT 0,
 *     success    TINYINT(1)    NOT NULL DEFAULT 0,
 *     response   TEXT,
 *     retries    TINYINT       NOT NULL DEFAULT 0,
 *     error_msg  VARCHAR(500),
 *     sent_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
 *   );
 */

require_once __DIR__ . '/config/db.php';

// ─── Config ───────────────────────────────────────────────────────────────────

define('WEBHOOK_SECRET',      'change-me-to-a-strong-secret');
define('WEBHOOK_TIMEOUT',     10);  // seconds per request
define('WEBHOOK_MAX_RETRIES', 3);   // attempts after the first failure

// ─── Core ─────────────────────────────────────────────────────────────────────

function webhookSign(string $body, string $secret): string {
    return 'sha256=' . hash_hmac('sha256', $body, $secret);
}

function webhookDeliver(string $url, array $payload, string $secret, int $attempt = 0): array {
    $body      = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $signature = webhookSign($body, $secret ?: WEBHOOK_SECRET);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => WEBHOOK_TIMEOUT,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Webhook-Signature: ' . $signature,
            'X-Webhook-Event: '     . ($payload['event'] ?? 'unknown'),
            'X-Webhook-Attempt: '   . ($attempt + 1),
        ],
    ]);

    $response  = curl_exec($ch);
    $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $success = ($httpCode >= 200 && $httpCode < 300);

    if (!$success && $attempt < WEBHOOK_MAX_RETRIES) {
        sleep(2 ** $attempt); // exponential back-off: 1s, 2s, 4s
        return webhookDeliver($url, $payload, $secret, $attempt + 1);
    }

    return [
        'success'   => $success,
        'http_code' => $httpCode,
        'response'  => $response ?: null,
        'error'     => $curlError ?: null,
        'retries'   => $attempt,
    ];
}

function webhookLog(PDO $pdo, string $event, string $url, array $result): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO webhook_log (event, url, http_code, success, response, retries, error_msg, sent_at)
            VALUES (:event, :url, :http_code, :success, :response, :retries, :error_msg, NOW())
        ");
        $stmt->execute([
            ':event'     => $event,
            ':url'       => $url,
            ':http_code' => $result['http_code'],
            ':success'   => $result['success'] ? 1 : 0,
            ':response'  => $result['response'] !== null ? substr($result['response'], 0, 2000) : null,
            ':retries'   => $result['retries'],
            ':error_msg' => $result['error'],
        ]);
    } catch (Throwable $e) {
        error_log('[Webhook] Log write failed: ' . $e->getMessage());
    }
}

function webhookGetEndpoints(PDO $pdo, string $event): array {
    $stmt = $pdo->prepare("
        SELECT url, secret
        FROM   webhook_endpoints
        WHERE  event = :event AND active = 1
    ");
    $stmt->execute([':event' => $event]);
    return $stmt->fetchAll();
}

// ─── Public API ───────────────────────────────────────────────────────────────

/**
 * Dispatch a webhook event to all registered active endpoints.
 *
 * @param  PDO    $pdo    Database connection
 * @param  string $event  Event name, e.g. "order.created"
 * @param  array  $data   Payload data to send
 * @return array  Summary: dispatched count + per-endpoint results
 */
function dispatchWebhook(PDO $pdo, string $event, array $data): array {
    $payload = [
        'event'     => $event,
        'data'      => $data,
        'timestamp' => date('c'),
    ];

    try {
        $endpoints = webhookGetEndpoints($pdo, $event);
    } catch (Throwable $e) {
        error_log('[Webhook] Could not fetch endpoints: ' . $e->getMessage());
        return ['dispatched' => 0, 'error' => 'Could not fetch endpoints'];
    }

    if (empty($endpoints)) {
        return ['dispatched' => 0, 'message' => "No active endpoints for event: {$event}"];
    }

    $results = [];
    foreach ($endpoints as $endpoint) {
        $result    = webhookDeliver($endpoint['url'], $payload, $endpoint['secret']);
        webhookLog($pdo, $event, $endpoint['url'], $result);
        $results[] = ['url' => $endpoint['url']] + $result;
    }

    return ['dispatched' => count($results), 'results' => $results];
}

// ─── Direct HTTP call ─────────────────────────────────────────────────────────
//
// When this file is requested directly via HTTP POST, it acts as a trigger
// endpoint. Authenticate with X-API-Key and post JSON: { "event": "...", "data": {} }

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json');

    function whSendResponse(bool $success, string $message, $data, int $code): void {
        http_response_code($code);
        echo json_encode([
            'success'   => $success,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => date('c'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    if (!defined('API_TOKEN')) {
        whSendResponse(false, 'API_TOKEN not configured', null, 500);
    }

    $receivedKey = null;
    foreach (getallheaders() as $k => $v) {
        if (strtolower($k) === 'x-api-key') {
            $receivedKey = trim($v);
            break;
        }
    }

    if ($receivedKey === null || $receivedKey !== API_TOKEN) {
        whSendResponse(false, 'Unauthorized — invalid or missing API key', null, 401);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        whSendResponse(false, 'Only POST method is allowed', null, 405);
    }

    // ── Input ─────────────────────────────────────────────────────────────────

    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        whSendResponse(false, 'Invalid or missing JSON body', null, 400);
    }

    $event = trim($input['event'] ?? '');
    $data  = $input['data']  ?? [];

    if ($event === '') {
        whSendResponse(false, '"event" field is required', null, 400);
    }

    if (!is_array($data)) {
        whSendResponse(false, '"data" field must be an object', null, 400);
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    try {
        $summary = dispatchWebhook($pdo, $event, $data);
        whSendResponse(true, 'Webhook dispatched', $summary, 200);
    } catch (Throwable $e) {
        error_log('[Webhook] Dispatch error: ' . $e->getMessage());
        whSendResponse(false, 'An internal error occurred', null, 500);
    }
}
