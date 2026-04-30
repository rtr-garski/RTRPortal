<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/access.php';
require_page_access('b2b_test', $pdo2, true);
require_once __DIR__ . '/../config/backblaze.php';
require_once __DIR__ . '/../config/b2_helper.php';

header('Content-Type: application/json');

$b2Path = trim($_GET['path'] ?? $_POST['path'] ?? '');
if ($b2Path === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'path required.']);
    exit;
}

try {
    $b2  = new BackblazeB2(B2B_KEY_ID, B2B_APP_KEY, B2B_BUCKET_ID, B2B_BUCKET_NAME);
    $url = $b2->generatePresignedUrl($b2Path);
    echo json_encode(['success' => true, 'url' => $url]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
