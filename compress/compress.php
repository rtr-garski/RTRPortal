<?php

ob_clean();
ini_set('display_errors', 0);

$publicKey = "project_public_c135d3e92da03f46e10eeddcf2b825f8_f3L4-00fb8b7b4014f3ddee19ec39312d103e";
$secretKey = "secret_key_c4cdac4063ecec23de88e5ff04b8d2da_b_pT51d8e072c04e2b4194f89c09497cea260";

if (!isset($_FILES['pdf'])) {
    http_response_code(400);
    exit;
}

$filePath = $_FILES['pdf']['tmp_name'];

// AUTH
$auth = json_decode(file_get_contents("https://api.ilovepdf.com/v1/auth", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode([
            "public_key" => $publicKey,
            "secret_key" => $secretKey
        ])
    ]
])));

$token = $auth->token;

// START
$start = json_decode(file_get_contents("https://api.ilovepdf.com/v1/start/compress", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Authorization: Bearer $token\r\n"
    ]
])));

$task = $start->task;
$server = $start->server;

// UPLOAD
$ch = curl_init("$server/v1/upload");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'task' => $task,
    'file' => new CURLFile($filePath)
]);
curl_exec($ch);
curl_close($ch);

// PROCESS
file_get_contents("$server/v1/process", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Authorization: Bearer $token\r\nContent-Type: application/json\r\n",
        'content' => json_encode([
            "task" => $task,
            "tool" => "compress",
            "compression_level" => "recommended"
        ])
    ]
]));

// DOWNLOAD (clean)
$ch = curl_init("$server/v1/download/$task");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$data = curl_exec($ch);
curl_close($ch);

// CLEAN OUTPUT AGAIN
if (ob_get_length()) ob_end_clean();

// HEADERS
header("Content-Type: application/pdf");
header("Content-Length: " . strlen($data));
header("Content-Disposition: attachment; filename=compressed.pdf");

// OUTPUT
echo $data;
exit;