<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=24jy0141;charset=utf8mb4", "root", "");
    echo "OK: DB connected.";
} catch (PDOException $e) {
    echo "NG: ".$e->getMessage();
}