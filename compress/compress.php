<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$publicKey = "project_public_c135d3e92da03f46e10eeddcf2b825f8_f3L4-00fb8b7b4014f3ddee19ec39312d103e";
$secretKey = "secret_key_c4cdac4063ecec23de88e5ff04b8d2da_b_pT51d8e072c04e2b4194f89c09497cea260";


if (!isset($_FILES['pdf'])) {
    die("❌ No file uploaded");
}

$filePath = $_FILES['pdf']['tmp_name'];

echo "STEP 1: AUTH<br>";

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

if (!$auth || !isset($auth->token)) {
    die("❌ AUTH FAILED: " . json_encode($auth));
}

$token = $auth->token;

echo "✅ AUTH OK<br>";
echo "STEP 2: START TASK<br>";

$start = json_decode(file_get_contents("https://api.ilovepdf.com/v1/start/compress", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Authorization: Bearer $token\r\n"
    ]
])));

if (!$start || !isset($start->task)) {
    die("❌ TASK START FAILED: " . json_encode($start));
}

$task = $start->task;
$server = $start->server;

echo "✅ TASK CREATED<br>";
echo "STEP 3: UPLOAD<br>";

$ch = curl_init("$server/v1/upload");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'task' => $task,
    'file' => new CURLFile($filePath)
]);

$response = curl_exec($ch);

if (!$response) {
    die("❌ UPLOAD FAILED: " . curl_error($ch));
}

curl_close($ch);

echo "✅ UPLOAD OK<br>";
echo "STEP 4: PROCESS<br>";

$process = file_get_contents("$server/v1/process", false, stream_context_create([
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

if (!$process) {
    die("❌ PROCESS FAILED");
}

echo "✅ PROCESS OK<br>";
echo "STEP 5: DOWNLOAD<br>";

$data = file_get_contents("$server/v1/download/$task");

if (!$data) {
    die("❌ DOWNLOAD FAILED");
}

echo "✅ DONE (file size: " . strlen($data) . " bytes)";

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=compressed.pdf");

readfile("$server/v1/download/$task");
exit;