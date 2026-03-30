<?php

header('Content-Type: application/json');

$publicKey = "project_public_c135d3e92da03f46e10eeddcf2b825f8_f3L4-00fb8b7b4014f3ddee19ec39312d103e";
$secretKey = "secret_key_c4cdac4063ecec23de88e5ff04b8d2da_b_pT51d8e072c04e2b4194f89c09497cea260";

$ch = curl_init('https://api.ilovepdf.com/v1/auth');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode(['public_key' => $publicKey]),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 15,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['ok' => false, 'error' => 'cURL error: ' . $curlError]);
    exit;
}

$body = json_decode($response, true);

if ($httpCode === 200 && !empty($body['token'])) {
    echo json_encode([
        'ok'      => true,
        'message' => 'Keys are valid. Auth token received.',
        'http'    => $httpCode,
    ]);
} else {
    echo json_encode([
        'ok'       => false,
        'message'  => 'Auth failed.',
        'http'     => $httpCode,
        'response' => $body,
    ]);
}
