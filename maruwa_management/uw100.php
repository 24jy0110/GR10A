<?php
session_start();

/* ---------- ログインチェック ---------- */
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit;
}

/* ---------- セッションから情報取得 ---------- */
$employee_name      = $_SESSION['employee_name'] ?? '';
$sales_office_name  = $_SESSION['sales_office_name'] ?? '';
$department_name    = $_SESSION['department_name'] ?? '';
$job_code           = $_SESSION['job_code'] ?? '';

/* ---------- 職種チェック（01 = 受付） ---------- */
if ($job_code !== "01") {
    header("Location: index.php?error=access");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>受付トップ | 内部管理システム</title>

    <!-- 共通CSS -->
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/header.css">

    <!-- 受付TOP専用CSS -->
    <link rel="stylesheet" href="assets/css/uw100.css">

</head>
<body>

<!-- ヘッダー -->
<?php include "header.php"; ?>

<!-- メイン内容 -->
<div class="uw100-container">

    <h2 class="welcome-title">
        ようこそ、<?= htmlspecialchars($sales_office_name) ?>
        <?= htmlspecialchars($department_name) ?>　
        <?= htmlspecialchars($employee_name) ?> 様
    </h2>

    <!-- 予約一覧へ -->
    <form action="uw101_01.php" method="GET">
        <button class="main-btn">予約一覧</button>
    </form>

</div>

</body>
</html>
