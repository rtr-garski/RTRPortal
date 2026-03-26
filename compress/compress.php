<?php

$publicKey = "project_public_c135d3e92da03f46e10eeddcf2b825f8_f3L4-00fb8b7b4014f3ddee19ec39312d103e";
$secretKey = "secret_key_c4cdac4063ecec23de88e5ff04b8d2da_b_pT51d8e072c04e2b4194f89c09497cea260";

if (!isset($_FILES['pdf'])) {
    http_response_code(400);
    exit("No file");
}

$filePath = $_FILES['pdf']['tmp_name'];

// 1. AUTH
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

// 2. START TASK
$start = json_decode(file_get_contents("https://api.ilovepdf.com/v1/start/compress", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Authorization: Bearer $token\r\n"
    ]
])));

$task = $start->task;
$server = $start->server;

// 3. UPLOAD FILE
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

// 4. PROCESS
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

// 5. DOWNLOAD RESULT
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=compressed.pdf");

readfile("$server/v1/download/$task");
exit;