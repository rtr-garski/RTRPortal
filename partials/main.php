<?php
require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    header('Location: ' . $base . '/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$dept = $_SESSION['department'] ?? '';
if ($dept === 'all') {
    $navCount = (int) $pdo2->query("SELECT COUNT(*) FROM nav_items WHERE is_active = 1 AND parent_id IS NULL")->fetchColumn();
} else {
    $stmt = $pdo2->prepare("
        SELECT COUNT(*) FROM nav_items ni
        INNER JOIN nav_item_departments d ON d.item_id = ni.id
        WHERE ni.is_active = 1
          AND ni.parent_id IS NULL
          AND d.department = ?
          AND d.is_active = 1
    ");
    $stmt->execute([$dept]);
    $navCount = (int) $stmt->fetchColumn();
}

if ($navCount === 0) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    header('Location: ' . $base . '/no_department.php');
    exit;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
