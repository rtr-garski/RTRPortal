<?php
require_once __DIR__ . '/../config/session.php';
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$row = $pdo->query(
    "SELECT Token FROM API_Tokens WHERE active = 1 AND Timestamp_Expiration > NOW() ORDER BY RAND() LIMIT 1"
)->fetch();

echo json_encode([
    'success' => (bool) $row,
    'token'   => $row ? $row['Token'] : '',
]);
