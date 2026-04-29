<?php
function get_client_ip(): string {
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            // Convert IPv6-mapped IPv4 (::ffff:1.2.3.4) to plain IPv4
            if (str_starts_with($ip, '::ffff:')) {
                $ip = substr($ip, 7);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return $ip;
        }
    }
    return '0.0.0.0';
}

function write_login_log(PDO $pdo2, ?int $userId, string $userName, string $method, string $status, ?string $failReason = null): void {
    $pdo2->prepare("
        INSERT INTO sys_login_logs (user_id, user_name, login_method, status, fail_reason, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $userId,
        $userName,
        $method,
        $status,
        $failReason,
        get_client_ip(),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
}
