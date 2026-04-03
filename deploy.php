<?php

//$secret = 'pBVLdomFKHaTpGBqHYD7C3Jfr';

// Log file
$logFile = '/var/www/html/deploy.log';

// 1. Read payload
$payload = file_get_contents('php://input');

// 2. Get signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// 3. Create hash
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

// 4. Validate signature
/*if (!hash_equals($hash, $signature)) {
    file_put_contents($logFile, date('c') . " - Invalid signature\n", FILE_APPEND);
    http_response_code(403);
    exit('Invalid signature');
}*/

// 5. Decode payload
$data = json_decode($payload, true);

if (!isset($data['ref'])) {
    file_put_contents($logFile, date('c') . " - No ref found\n", FILE_APPEND);
    exit('No ref found');
}

$ref = $data['ref'];

// Log incoming request
file_put_contents($logFile, date('c') . " - Received push: $ref\n", FILE_APPEND);

// 6. Deploy based on branch
$output = [];

if ($ref === 'refs/heads/rtrdev/garski') {
    exec('bash /var/www/html/RTRGarski/deploy.sh 2>&1', $output);
} elseif ($ref === 'refs/heads/rtrdev/andrew') {
    exec('bash /var/www/html/RTRAndrew/deploy.sh 2>&1', $output);
} elseif ($ref === 'refs/heads/staging') {
    exec('bash /var/www/html/RTRStage/deploy.sh 2>&1', $output);
} elseif ($ref === 'refs/heads/production') {
    exec('bash /var/www/html/RTRPortal/deploy.sh 2>&1', $output);
} else {
    file_put_contents($logFile, date('c') . " - Unknown branch: $ref\n", FILE_APPEND);
    exit('Branch not handled');
}

// 7. Log execution output
file_put_contents(
    $logFile,
    date('c') . " - Deploy output:\n" . implode("\n", $output) . "\n\n",
    FILE_APPEND
);

echo "OK";

?>