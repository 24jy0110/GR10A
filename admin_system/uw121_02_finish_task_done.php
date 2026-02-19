<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";

if (!isset($_GET["r"])) {
    header("Location: uw121_driver_tasks.php");
    exit;
}

$resNo = $_GET["r"];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>行程完了 | 丸和交通</title>

<style>
body { 
    font-family:"Noto Sans JP", sans-serif;
    background:#fafafa; 
    margin:0;
    padding:0;
}

.container {
    max-width:700px;
    margin:60px auto;
    background:#fff;
    padding:40px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.12);
    text-align:center;
}

h1 {
    font-size:28px;
    font-weight:700;
    margin-bottom:20px;
}

.message {
    font-size:18px;
    margin-top:15px;
    margin-bottom:30px;
}

.res-number {
    font-size:22px;
    font-weight:700;
    margin:20px 0;
    padding:12px 0;
    background:#f0f0f0;
    border-radius:6px;
}

.btn-back {
    display:inline-block;
    padding:12px 30px;
    font-size:16px;
    background:#333;
    color:#fff;
    border-radius:6px;
    text-decoration:none;
    margin-top:20px;
}
.btn-back:hover {
    background:#000;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

    <h1>行程が完了しました</h1>

    <div class="message">
        乗務のご対応、お疲れ様でした。<br>
        以下の予約が正常に完了処理されました。
    </div>

    <div class="res-number">
        予約番号：<?= htmlspecialchars($resNo) ?>
    </div>

    <a href="uw121_driver_tasks.php" class="btn-back">乗務確認へ戻る</a>

</div>

</body>
</html>
