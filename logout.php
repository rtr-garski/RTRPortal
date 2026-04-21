<?php
require_once 'config/session.php';

$_SESSION = [];
session_destroy();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
header('Location: ' . $base . '/login.php');
exit;
