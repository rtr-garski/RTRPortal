<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../apiportal/functions2.php';
header('Content-Type: application/json');

$action   = $_POST['action'] ?? '';
$apiKey   = trim($_POST['api_key']   ?? '');
$jsonBody = trim($_POST['json_body'] ?? '');

if ($action !== 'submit') {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

if (empty($apiKey) || empty($jsonBody)) {
    echo json_encode(['success' => false, 'formError' => true, 'message' => 'API Key and JSON Body are required.']);
    exit;
}

$start = microtime(true);

if ($apiKey !== API_TOKEN) {
    $statusCode  = 401;
    $result      = json_encode(['success' => false, 'message' => 'Unauthorized — invalid or missing API key', 'data' => null, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
    $curlCommand = null;
} else {
    $input = json_decode($jsonBody, true);
    if ($input === null) {
        $statusCode  = 400;
        $result      = json_encode(['success' => false, 'message' => 'Invalid or missing JSON body', 'data' => null, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
        $curlCommand = null;
    } else {
        try {
            $payload    = buildPayload($input);
            $statusCode = 201;
            $result     = json_encode(['success' => true, 'message' => 'Submission received successfully', 'data' => $payload, 'timestamp' => date('c')], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                           || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                           ? 'https' : 'http';
            $host        = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
            $dir         = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
            $endpointRaw = $scheme . '://' . $host . $dir . '/apiportal/receiver2.php';

            $curlCommand = 'curl -X POST "' . $endpointRaw . '" \\' . "\n"
                         . '  -H "Content-Type: application/json" \\' . "\n"
                         . '  -H "X-API-Key: ' . $apiKey . '" \\' . "\n"
                         . "  -d '" . $jsonBody . "'";

        } catch (ApiValidationException $e) {
            $statusCode  = 400;
            $result      = json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => null, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
            $curlCommand = null;
        }
    }
}

$responseTime = round((microtime(true) - $start) * 1000);

$badgeClass = 'secondary';
if ($statusCode >= 200 && $statusCode < 300)     $badgeClass = 'success';
elseif ($statusCode >= 400 && $statusCode < 500) $badgeClass = 'warning';
elseif ($statusCode >= 500)                       $badgeClass = 'danger';

echo json_encode([
    'success'      => ($statusCode >= 200 && $statusCode < 300),
    'statusCode'   => $statusCode,
    'result'       => $result,
    'curlCommand'  => $curlCommand,
    'responseTime' => $responseTime,
    'badgeClass'   => $badgeClass,
]);
