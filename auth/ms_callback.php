<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/ms_sso.php';

function ms_post(string $url, array $fields): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

function ms_get(string $url, string $accessToken): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

// Validate CSRF state
if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['ms_oauth_state'] ?? '')) {
    header('Location: ../login.php?sso_error=state_mismatch');
    exit;
}
unset($_SESSION['ms_oauth_state']);

// Microsoft returned an error (e.g. user cancelled)
if (!empty($_GET['error'])) {
    $desc = urlencode($_GET['error_description'] ?? $_GET['error']);
    header('Location: ../login.php?sso_error=' . $desc);
    exit;
}

if (empty($_GET['code'])) {
    header('Location: ../login.php?sso_error=no_code');
    exit;
}

// Exchange auth code for tokens
$tokenData = ms_post(
    'https://login.microsoftonline.com/' . MS_TENANT_ID . '/oauth2/v2.0/token',
    [
        'client_id'     => MS_CLIENT_ID,
        'client_secret' => MS_CLIENT_SECRET,
        'code'          => $_GET['code'],
        'redirect_uri'  => MS_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]
);

if (empty($tokenData['access_token'])) {
    header('Location: ../login.php?sso_error=token_failed');
    exit;
}

// Get user profile from Microsoft Graph
$graphData = ms_get('https://graph.microsoft.com/v1.0/me', $tokenData['access_token']);

// 'mail' is the Exchange mailbox address; 'userPrincipalName' is the fallback (UPN)
$email = strtolower(trim($graphData['mail'] ?? $graphData['userPrincipalName'] ?? ''));

if (!$email) {
    header('Location: ../login.php?sso_error=no_email');
    exit;
}

// Match against sys_users
$stmt = $pdo2->prepare("SELECT * FROM sys_users WHERE LOWER(user_name) = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ../login.php?sso_error=unauthorized');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['user_name'] = $user['user_name'];
$_SESSION['name']      = $user['name'];

$pdo2->prepare("UPDATE sys_users SET last_login = NOW() WHERE user_id = ?")
     ->execute([$user['user_id']]);

header('Location: ../index.php');
exit;
