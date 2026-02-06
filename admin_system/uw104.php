<?php
session_start();

/* ---------------------------------------------------
   受付（01）以外アクセス禁止
--------------------------------------------------- */
if (!isset($_SESSION['employee']) || $_SESSION['employee']['job_code'] !== "01") {
    header("Location: login.php");
    exit;
}

$employee = $_SESSION['employee'];

/* ---------------------------------------------------
   GET パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw101.php");
    exit;
}

$resNo = $_GET['r'];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約キャンセル完了 | 丸和交通</title>
<link rel="stylesheet" href="assets/app.css">

<style>
.body-area {
    max-width: 850px;
    margin: 60px auto;
    padding: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    font-size: 17px;
    line-height: 1.8;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 28px;
    font-weight: bold;
}

.cancel-number {
    font-size: 24px;
    font-weight: bold;
    color: #b30000;
    text-align: center;
    margin: 15px 0 30px;
}

.message-area {
    text-align: center;
    margin-bottom: 30px;
    color: #444;
}

.btn-area {
    text-align: center;
    margin-top: 40px;
}

.back-btn {
    padding: 12px 36px;
    font-size: 17px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
    text-decoration: none;
    color: #000;
    border-radius: 6px;
}

.back-btn:hover {
    background: #f5f5f5;
}
</style>
</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="body-area">

    <h1>予約キャンセルが完了しました</h1>

    <p class="message-area">
        以下の仮予約を <span style="color:#d00000; font-weight:bold;">キャンセル</span> いたしました。<br>
        内容をご確認のうえ、必要に応じてお客様へご案内ください。
    </p>

    <div class="cancel-number">
        【予約番号：<?= htmlspecialchars($resNo) ?>】
    </div>

    <div class="btn-area">
        <a href="uw101.php" class="back-btn">予約一覧へ戻る</a>
    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
