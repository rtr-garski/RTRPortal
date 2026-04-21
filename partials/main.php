<?php
require_once __DIR__ . '/../config/session.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
