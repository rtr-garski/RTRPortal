<?php

// ═══════════════════════════════════════════════════════════════════════════════
//  API Receiver — Production
//  Endpoint: POST /apiportal/receiver.php
//  Auth:     X-API-Key header
// ═══════════════════════════════════════════════════════════════════════════════

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// ─── CORS ─────────────────────────────────────────────────────────────────────

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function sendResponse(bool $success, string $message, $data, int $code): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function getApiKey(): ?string {
    foreach (getallheaders() as $key => $value) {
        if (strtolower($key) === 'x-api-key') {
            return trim($value);
        }
    }
    return null;
}

// ─── Handler ──────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Only POST method is allowed", null, 405);
}

$apiKey = getApiKey();
if ($apiKey === null || $apiKey !== API_TOKEN) {
    sendResponse(false, "Unauthorized — invalid or missing API key", null, 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    sendResponse(false, "Invalid or missing JSON body", null, 400);
}

try {
    $payload = buildPayload($input);
    saveSubmission($payload);
    sendResponse(true, "Submission received successfully", $payload, 201);

} catch (ApiValidationException $e) {
    sendResponse(false, $e->getMessage(), null, 400);

} catch (Throwable $e) {
    // Log the real error server-side; never expose internals to the caller
    error_log('[RTR API] ' . $e->getMessage());
    sendResponse(false, "An internal error occurred. Please try again.", null, 500);
}
