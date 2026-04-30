<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/access.php';
require_page_access('api_token_management', $pdo2, true);
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $label = trim($_POST['label'] ?? '');
    if ($label === '') {
        echo json_encode(['success' => false, 'message' => 'A label is required.']);
        exit;
    }
    $token = bin2hex(random_bytes(32));
    $pdo->prepare("
        INSERT INTO API_Tokens (label, Token, active, Timestamp_Issued, Timestamp_Expiration)
        VALUES (:label, :token, 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH))
    ")->execute([':label' => $label, ':token' => $token]);
    echo json_encode([
        'success' => true,
        'message' => 'Token <strong>' . htmlspecialchars($label) . '</strong> created. Copy it now — it won\'t be shown again.',
        'token'   => $token,
    ]);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
        exit;
    }
    $pdo->prepare("DELETE FROM API_Tokens WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Token revoked and deleted.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
