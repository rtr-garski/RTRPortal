<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Public API endpoint — no PHP session required.
 * Same as b2_presign.php but uses the second B2 bucket (B2B_* credentials).
 *
 * Request (POST or GET):
 *   token      — required; must match an active row in API_Tokens
 *   order_id   — required; used to build the folder/filename
 *   uuid       — optional; generated server-side when omitted
 *   extension  — optional; file extension without dot (default: bin)
 *
 * Response (JSON):
 *   success        bool
 *   presigned_url  string  — PUT the file bytes directly to this URL, no extra headers needed
 *   b2_file_name   string  — full B2 path (folder/filename)
 *   folder         string  — orderid_uuid
 *   filename       string  — basename only
 *   expires_in     int     — seconds until the presigned URL expires
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/backblaze.php';
require_once __DIR__ . '/../config/b2_helper.php';

header('Content-Type: application/json');

$p = array_merge($_GET, $_POST);

$token     = trim($p['token']     ?? '');
$order_id  = trim($p['order_id']  ?? '');
$uuid      = trim($p['uuid']      ?? '');
$extension = trim($p['extension'] ?? 'bin');

// --- Auth ---
if ($token === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'token required.']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id FROM API_Tokens WHERE Token = ? AND Timestamp_Expiration > NOW()"
);
$stmt->execute([$token]);
if (!$stmt->fetch()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
    exit;
}

// --- Validate inputs ---
if ($order_id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'order_id required.']);
    exit;
}

$order_id  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $order_id);
$extension = preg_replace('/[^a-zA-Z0-9]/', '', $extension) ?: 'bin';

if ($uuid === '') {
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
$uuid_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $uuid);

// --- Build B2 path ---
$folder     = $order_id . '_' . $uuid_safe;
$datetime   = date('Ymd_His');
$filename   = $order_id . '_' . $uuid_safe . '_' . $datetime . '.' . $extension;
$b2FileName = $folder . '/' . $filename;
$expiresIn  = 3600;

// --- Generate presigned PUT URL using second bucket ---
try {
    $b2  = new BackblazeB2(B2B_KEY_ID, B2B_APP_KEY, B2B_BUCKET_ID, B2B_BUCKET_NAME);
    $url = $b2->generatePresignedUploadUrl($b2FileName, $expiresIn);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'B2 error: ' . $e->getMessage() . ' (' . get_class($e) . ' in ' . basename($e->getFile()) . ':' . $e->getLine() . ')']);
    exit;
}

echo json_encode([
    'success'       => true,
    'presigned_url' => $url,
    'b2_file_name'  => $b2FileName,
    'folder'        => $folder,
    'filename'      => $filename,
    'expires_in'    => $expiresIn,
]);
