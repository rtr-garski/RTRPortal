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

// 6. Debug: log environment info
file_put_contents($logFile, date('c') . " - Running as user: " . trim(shell_exec('whoami')) . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . " - exec() enabled: " . (function_exists('exec') ? 'yes' : 'no') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . " - git path: " . trim(shell_exec('which git')) . "\n", FILE_APPEND);

// 7. Deploy based on branch
$output = [];
$returnCode = null;

if ($ref === 'refs/heads/rtrdev/garski') {
    $script = '/var/www/html/RTRGarski/deploy.sh';
    file_put_contents($logFile, date('c') . " - Script exists: " . (file_exists($script) ? 'yes' : 'no') . "\n", FILE_APPEND);
    file_put_contents($logFile, date('c') . " - Script executable: " . (is_executable($script) ? 'yes' : 'no') . "\n", FILE_APPEND);
    exec('bash ' . $script . ' 2>&1', $output, $returnCode);
} elseif ($ref === 'refs/heads/rtrdev/andrew') {
    $script = '/var/www/html/RTRAndrew/deploy.sh';
    exec('bash ' . $script . ' 2>&1', $output, $returnCode);
} elseif ($ref === 'refs/heads/staging') {
    $script = '/var/www/html/RTRStage/deploy.sh';
    exec('bash ' . $script . ' 2>&1', $output, $returnCode);
} elseif ($ref === 'refs/heads/production') {
    $script = '/var/www/html/RTRPortal/deploy.sh';
    exec('bash ' . $script . ' 2>&1', $output, $returnCode);
} else {
    file_put_contents($logFile, date('c') . " - Unknown branch: $ref\n", FILE_APPEND);
    exit('Branch not handled');
}

// 8. Log execution output
file_put_contents(
    $logFile,
    date('c') . " - Return code: $returnCode\n" .
    date('c') . " - Deploy output:\n" . implode("\n", $output) . "\n\n",
    FILE_APPEND
);

echo "OK";

?>