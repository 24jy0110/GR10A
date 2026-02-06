<?php
// includes/db_connect.php
$dsn = 'mysql:host=10.64.144.5;dbname=24jy0141;charset=utf8mb4';
$username = '24jy0141';
$password = '24jy0141';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo 'DB接続失敗: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
