<?php

ob_start();
ini_set('display_errors', 0);

$publicKey = "project_public_c135d3e92da03f46e10eeddcf2b825f8_f3L4-00fb8b7b4014f3ddee19ec39312d103e";
$secretKey = "secret_key_c4cdac4063ecec23de88e5ff04b8d2da_b_pT51d8e072c04e2b4194f89c09497cea260";

function sendError($message, $code = 500) {
    ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}

function curlPost($url, $headers, $fields = null, $isJson = false) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 120,
    ]);
    if ($fields !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isJson ? json_encode($fields) : $fields);
    }
    $response   = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);
    return [$response, $httpCode, $curlError];
}

function curlGet($url, $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 120,
    ]);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    return [$response, $httpCode, $curlError];
}

// Validate upload
if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    sendError('No PDF file uploaded.', 400);
}

$filePath     = $_FILES['pdf']['tmp_name'];
$originalName = basename($_FILES['pdf']['name']);
$originalSize = $_FILES['pdf']['size'];

if (mime_content_type($filePath) !== 'application/pdf') {
    sendError('Uploaded file is not a valid PDF.', 400);
}

// AUTH
[$authRaw, $authCode, $authErr] = curlPost(
    'https://api.ilovepdf.com/v1/auth',
    ['Content-Type: application/json'],
    ['public_key' => $publicKey],
    true
);

if ($authErr || $authCode !== 200) {
    sendError('ilovepdf auth failed. HTTP ' . $authCode . '. ' . $authErr);
}

$auth = json_decode($authRaw);
if (empty($auth->token)) {
    sendError('ilovepdf auth: no token returned. Response: ' . $authRaw);
}

$token = $auth->token;

// START TASK
[$startRaw, $startCode, $startErr] = curlPost(
    'https://api.ilovepdf.com/v1/start/compress',
    ["Authorization: Bearer $token"]
);

if ($startErr || $startCode !== 200) {
    sendError('Failed to start task. HTTP ' . $startCode . '. ' . $startErr);
}

$start = json_decode($startRaw);
if (empty($start->task) || empty($start->server)) {
    sendError('Start task missing task/server. Response: ' . $startRaw);
}

$task   = $start->task;
$server = $start->server;

// UPLOAD FILE
[$uploadRaw, $uploadCode, $uploadErr] = curlPost(
    "$server/v1/upload",
    ["Authorization: Bearer $token"],
    [
        'task' => $task,
        'file' => new CURLFile($filePath, 'application/pdf', $originalName),
    ]
);

if ($uploadErr || $uploadCode !== 200) {
    sendError('Upload failed. HTTP ' . $uploadCode . '. ' . $uploadErr);
}

$upload = json_decode($uploadRaw);
if (empty($upload->server_filename)) {
    sendError('Upload: no server_filename. Response: ' . $uploadRaw);
}

$serverFilename   = $upload->server_filename;
$downloadFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_compressed.pdf';

// PROCESS
[$processRaw, $processCode, $processErr] = curlPost(
    "$server/v1/process",
    ["Authorization: Bearer $token", "Content-Type: application/json"],
    [
        'task'              => $task,
        'tool'              => 'compress',
        'compression_level' => 'recommended',
        'files'             => [
            ['server_filename' => $serverFilename, 'filename' => $originalName]
        ],
    ],
    true
);

if ($processErr || $processCode !== 200) {
    sendError('Process failed. HTTP ' . $processCode . '. Response: ' . $processRaw . '. ' . $processErr);
}

// DOWNLOAD
[$data, $downloadCode, $downloadErr] = curlGet(
    "$server/v1/download/$task",
    ["Authorization: Bearer $token"]
);

if ($downloadErr || $downloadCode !== 200 || empty($data)) {
    sendError('Download failed. HTTP ' . $downloadCode . '. ' . $downloadErr);
}

ob_end_clean();

$compressedSize = strlen($data);

header('Content-Type: application/pdf');
header('Content-Length: ' . $compressedSize);
header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
header('X-Original-Size: ' . $originalSize);
header('X-Compressed-Size: ' . $compressedSize);

echo $data;
exit;
