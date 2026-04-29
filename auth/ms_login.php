<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/ms_sso.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$state = bin2hex(random_bytes(16));

// Store state in a Lax cookie — SameSite=Strict sessions are not sent on
// cross-site redirects (e.g. the return from login.microsoftonline.com).
setcookie('ms_oauth_state', $state, [
    'expires'  => time() + 600,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

$params = http_build_query([
    'client_id'     => MS_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri'  => MS_REDIRECT_URI,
    'scope'         => 'openid profile email User.Read',
    'response_mode' => 'query',
    'state'         => $state,
]);

header('Location: https://login.microsoftonline.com/' . MS_TENANT_ID . '/oauth2/v2.0/authorize?' . $params);
exit;
