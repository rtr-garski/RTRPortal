<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

header('Content-Type: application/json');
// Catch any PHP warnings/notices that would break JSON output
ob_start();

$method  = strtoupper($_POST['method']  ?? 'GET');
$url     = trim($_POST['url']          ?? '');
$payload = trim($_POST['payload']      ?? '');

if (!$url) {
    echo json_encode(['success' => false, 'message' => 'URL is required']);
    exit;
}

$headers = ['Content-Type: application/json', 'Accept: application/json'];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload ?: '{}');
} elseif ($method !== 'GET') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($payload) curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
}

$start      = microtime(true);
$raw        = curl_exec($ch);
$elapsed    = round((microtime(true) - $start) * 1000);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$curlError  = curl_error($ch);
curl_close($ch);

ob_end_clean();

if ($raw === false || $curlError) {
    echo json_encode(['success' => false, 'message' => 'cURL error: ' . ($curlError ?: 'unknown')]);
    exit;
}

$body    = substr($raw, $headerSize);
$decoded = json_decode($body, true);
$pretty  = $decoded !== null ? json_encode($decoded, JSON_PRETTY_PRINT) : $body;

echo json_encode([
    'success' => true,
    'status'  => $httpCode,
    'elapsed' => $elapsed,
    'body'    => $pretty,
]);
