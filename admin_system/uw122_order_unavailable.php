<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";

if (!isset($_GET["r"])) {
    header("Location: uw122_new_orders.php");
    exit;
}

$resNo = $_GET["r"];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>受付不可 | 丸和交通</title>

<style>
body {
    font-family:"Noto Sans JP",sans-serif;
    background:#fafafa;
    margin:0;
}

.container {
    max-width:750px;
    margin:60px auto;
    background:#fff;
    padding:40px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,.12);
    text-align:center;
}

h1 {
    font-size:26px;
    font-weight:700;
    margin-bottom:20px;
}

.alert-box {
    padding:20px;
    background:#ffe7e7;
    border:1px solid #ff8c8c;
    border-radius:8px;
    margin:25px 0;
    font-size:18px;
    font-weight:bold;
    color:#b40000;
}

.info {
    background:#f4f4f4;
    padding:14px;
    border-radius:6px;
    font-size:17px;
    margin:15px 0 30px 0;
}

.btn-back {
    display:inline-block;
    padding:12px 28px;
    background:#000;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:16px;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

    <h1>受付できませんでした</h1>

    <div class="alert-box">
        他のドライバーがすでにこの依頼を受け付けました。
    </div>

    <div class="info">
        予約番号：<b><?= htmlspecialchars($resNo) ?></b>
    </div>

    <a href="uw122_new_orders.php" class="btn-back">新規依頼一覧へ戻る</a>

</div>

</body>
</html>
