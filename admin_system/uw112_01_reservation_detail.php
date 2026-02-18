<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

$res_no = $_GET['reservation_number'] ?? '';

if (!$res_no) {
    echo "予約番号が指定されていません。";
    exit;
}

$sql = "
    SELECT r.*, 
           cm.car_model_name, 
           so.sales_office_name
    FROM reservation r
    LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code
    LEFT JOIN sales_office so ON r.sales_office_code = so.sales_office_code
    WHERE r.reservation_number = :no
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':no', $res_no, PDO::PARAM_STR);
$stmt->execute();
$res = $stmt->fetch();

if (!$res) {
    echo "予約情報が見つかりません。";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約詳細</title>
<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 40px 60px;
    color: #000;
}

h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    margin-top: 35px;
    margin-bottom: 10px;
}

.detail-block {
    font-size: 19px;
    line-height: 1.9;
    margin-left: 10px;
}

.detail-block span.label {
    font-weight: 700;
}

.btn-back {
    display: inline-block;
    margin-top: 40px;
    padding: 12px 40px;
    font-size: 18px;
    border: 2px solid #000;
    background: #fff;
    text-decoration: none;
    color: #000;
    border-radius: 6px;
}
.btn-back:hover {
    background: #000;
    color: #fff;
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>予約詳細：<?= htmlspecialchars($res_no) ?></h1>

<!-- 基本情報 -->
<div class="section-title">基本情報</div>
<div class="detail-block">
    <span class="label">予約日：</span><?= htmlspecialchars($res['reservation_date']) ?><br>
    <span class="label">車種：</span><?= htmlspecialchars($res['car_model_name']) ?><br>
    <span class="label">営業所：</span><?= htmlspecialchars($res['sales_office_name']) ?>
</div>

<!-- 乗車情報 -->
<div class="section-title">予定運行状況</div>
<div class="detail-block">
    <span class="label">担当ドライバー：</span>
    <?= htmlspecialchars($res['driver_id'] ?? "ー") ?><br>

    <span class="label">乗車日時：</span>
    <?= htmlspecialchars($res['service_start_time']) ?><br>

    <span class="label">乗車場所：</span>
    <?= htmlspecialchars($res['ride_location']) ?><br>

    <span class="label">降車場所：</span>
    <?= htmlspecialchars($res['drop_off_location']) ?>
</div>

<a class="btn-back" href="javascript:history.back();">戻る</a>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
