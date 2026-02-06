<?php
require_once __DIR__ . '/includes/check_login.php'; // ← ログイン必須
$employee = $_SESSION['employee'];

$employee_name = $employee['employee_name'];
$sales_office  = $employee['sales_office_code']; // 例：OFC001 → 要変換
$job_code      = $employee['job_code'];

// 支社コード → 名称（必要なら DB から動的取得も可）
$sales_office_name = [
    "OFC001" => "亀沢本社",
    "OFC002" => "豊島支社",
    "OFC003" => "桃井支社",
    "OFC004" => "富士見町支社",
    "OFC005" => "本羽田支社",
    "OFC006" => "中区支社"
][$sales_office] ?? $sales_office;

$job_name = [
    "01" => "カスタマーサービス課",
    "02" => "配車センター",
    "03" => "ドライバー"
][$job_code];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>受付TOP | 丸和交通</title>

<style>
body {
    font-family: 'Noto Sans JP', sans-serif;
    text-align: center;
    background: #fff;
    margin: 0;
    padding: 0;
}

.main-container {
    margin-top: 80px;
}

.welcome-line {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 10px;
}

.welcome-name {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 60px;
}

.btn-reserve {
    display: inline-block;
    padding: 22px 160px;
    font-size: 22px;
    font-weight: 600;
    color: #fff;
    background: #000;
    border-radius: 6px;
    text-decoration: none;
}

.btn-reserve:hover {
    opacity: .8;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="main-container">

    <!-- ようこそ -->
    <div class="welcome-line">
        ようこそ、<?= htmlspecialchars($sales_office_name) ?>　
        <?= htmlspecialchars($job_name) ?>
    </div>

    <!-- 氏名 -->
    <div class="welcome-name">
        <?= htmlspecialchars($employee_name) ?> 様
    </div>

    <!-- ボタン -->
    <a href="uw101.php" class="btn-reserve">予約一覧</a>

</div>

</body>
</html>
