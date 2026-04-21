<?php
ob_start();

session_start();
require_once 'config/db.php';
ob_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

$stmt = $pdo2->prepare("SELECT * FROM sys_users WHERE user_name = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['user_password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['user_name'] = $user['user_name'];

$pdo2->prepare("UPDATE sys_users SET last_login = NOW() WHERE user_id = ?")
     ->execute([$user['user_id']]);

echo json_encode(['success' => true, 'redirect' => 'index.php']);
