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

function apiPost($url, $headers, $body = null, $json = true) {
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true,
        ]
    ];
    if ($body !== null) {
        $opts['http']['content'] = $json ? json_encode($body) : $body;
    }
    $result = file_get_contents($url, false, stream_context_create($opts));
    return $result !== false ? json_decode($result) : null;
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
$auth = apiPost('https://api.ilovepdf.com/v1/auth', ['Content-Type: application/json'], [
    'public_key' => $publicKey,
    'secret_key' => $secretKey,
]);

if (empty($auth->token)) {
    sendError('Authentication with ilovepdf failed.');
}

$token = $auth->token;

// START TASK
$start = apiPost('https://api.ilovepdf.com/v1/start/compress', [
    "Authorization: Bearer $token"
]);

if (empty($start->task) || empty($start->server)) {
    sendError('Failed to start compression task.');
}

$task   = $start->task;
$server = $start->server;

// UPLOAD FILE
$ch = curl_init("$server/v1/upload");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
    CURLOPT_POSTFIELDS     => [
        'task' => $task,
        'file' => new CURLFile($filePath, 'application/pdf', $originalName),
    ],
]);
$uploadResponse = json_decode(curl_exec($ch));
$uploadStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($uploadStatus !== 200 || empty($uploadResponse->server_filename)) {
    sendError('Failed to upload file to ilovepdf.');
}

$serverFilename   = $uploadResponse->server_filename;
$downloadFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_compressed.pdf';

// PROCESS
$process = apiPost("$server/v1/process", [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
], [
    'task'              => $task,
    'tool'              => 'compress',
    'compression_level' => 'recommended',
    'files'             => [
        ['server_filename' => $serverFilename, 'filename' => $originalName]
    ],
]);

if (empty($process->download_filename) && empty($process->status)) {
    sendError('Compression processing failed.');
}

// DOWNLOAD
$ch = curl_init("$server/v1/download/$task");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
]);
$data       = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpStatus !== 200 || empty($data)) {
    sendError('Failed to download compressed file.');
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
