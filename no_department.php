<?php
require_once 'config/session.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Access Pending — RTR Portal</title>
    <?php include('partials/head-css.php'); ?>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="text-center" style="max-width: 420px;">
            <i class="ti ti-lock-access fs-1 text-warning mb-3 d-block"></i>
            <h4 class="fw-bold mb-2">No Access Assigned</h4>
            <p class="text-muted mb-4">Your account has not been granted access to any portal features yet. Please contact your administrator.</p>
            <a href="logout.php" class="btn btn-outline-secondary btn-sm">Sign Out</a>
        </div>
    </div>
</body>
</html>
