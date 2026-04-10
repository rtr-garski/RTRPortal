<?php

// --- Primary Database ---
$host = '127.0.0.1';
$db   = 'lp8agm5o_oms';
$user = 'lp8agm5o_ows';
$pass = '9dvTYe@o8bl%';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --- Second Dbase ---
/*$host2   = 'localhost';
$db2     = 'YOUR_SECOND_DB_NAME';
$user2   = 'YOUR_SECOND_DB_USER';
$pass2   = 'YOUR_SECOND_DB_PASS';

$dsn2 = "mysql:host=$host2;dbname=$db2;charset=$charset";

try {
    $pdo2 = new PDO($dsn2, $user2, $pass2, $options);
} catch (PDOException $e) {
    die("Secondary database connection failed: " . $e->getMessage());
}*/

?>