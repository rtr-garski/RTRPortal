<?php
require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    header('Location: ' . $base . '/login.php');
    exit;
}
