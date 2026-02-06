<?php  
$dsn = 'mysql:host=10.64.144.5;dbname=24jy0141;charset=utf8';  //自分の学籍番号
$username = '24jy0141'; //自分の学籍番号
$password = '24jy0141'; //自分の学籍番号

try {
    // MySQLサーバーに接続する
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'DB接続失敗: ' . $e->getMessage();    
    exit;
}
?>
