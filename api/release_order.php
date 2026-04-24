<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

header('Content-Type: application/json');

$action    = $_POST['action']      ?? '';
$fm_server = rtrim($_POST['fm_server']   ?? '', '/');
$fm_db     = $_POST['fm_database'] ?? '';
$fm_layout = $_POST['fm_layout']   ?? '';
$fm_user   = $_POST['fm_user']     ?? '';
$fm_pass   = $_POST['fm_pass']     ?? '';

if (!$fm_server || !$fm_db || !$fm_user || !$fm_pass) {
    echo json_encode(['success' => false, 'message' => 'Missing connection fields']);
    exit;
}

function fm_auth($fm_server, $fm_db, $fm_user, $fm_pass) {
    $url = "$fm_server/fmi/data/v1/databases/" . rawurlencode($fm_db) . "/sessions";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '{}',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("$fm_user:$fm_pass"),
        ],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($resp, true);
    return $data['response']['token'] ?? null;
}

function fm_logout($fm_server, $fm_db, $token) {
    $url = "$fm_server/fmi/data/v1/databases/" . rawurlencode($fm_db) . "/sessions/" . rawurlencode($token);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

if ($action === 'test') {
    $token = fm_auth($fm_server, $fm_db, $fm_user, $fm_pass);
    if ($token) {
        fm_logout($fm_server, $fm_db, $token);
        echo json_encode(['success' => true, 'message' => 'Connected to FileMaker successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Authentication failed. Check credentials.']);
    }
    exit;
}

if ($action === 'release') {
    if (!$fm_layout) {
        echo json_encode(['success' => false, 'message' => 'Layout name is required']);
        exit;
    }

    $raw_payload = $_POST['payload'] ?? '';
    $payload     = json_decode($raw_payload, true);
    if (!$payload) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
        exit;
    }

    $token = fm_auth($fm_server, $fm_db, $fm_user, $fm_pass);
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'FileMaker authentication failed']);
        exit;
    }

    $url = "$fm_server/fmi/data/v1/databases/" . rawurlencode($fm_db) . "/layouts/" . rawurlencode($fm_layout) . "/records";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    fm_logout($fm_server, $fm_db, $token);

    $data = json_decode($resp, true);
    if (isset($data['response']['recordId'])) {
        echo json_encode(['success' => true, 'message' => 'Record created in FileMaker.', 'recordId' => $data['response']['recordId'], 'raw' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'FileMaker rejected the record.', 'raw' => $data]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
