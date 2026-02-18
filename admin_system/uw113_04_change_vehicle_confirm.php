<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r']) || !isset($_GET['car'])) {
    header("Location: uw117_01_reservation_list.php");
    exit;
}

$resNo = $_GET['r'];
$newCar = $_GET['car'];

/* ---------------------------------------------------
   予約情報（現車両含む）取得
--------------------------------------------------- */
$sql = "
SELECT 
    r.reservation_number,
    r.number_plate AS current_plate,
    cm.car_model_name AS current_model,
    cm.car_model_capacity AS current_capacity,

    r.car_model_code,
    so.sales_office_name,
    r.service_start_time,
    r.service_end_date

FROM reservation r
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
LEFT JOIN sales_office so ON so.sales_office_code = r.sales_office_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("予約データが取得できません。");
}

/* ---------------------------------------------------
   新車両情報取得
--------------------------------------------------- */
$sql2 = "
SELECT 
    v.number_plate,
    v.vehicle_capacity,
    cm.car_model_name,
    cm.car_model_code
FROM vehicle v
LEFT JOIN car_model cm ON cm.car_model_code = v.car_model_code
WHERE v.number_plate = :car
";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute([":car" => $newCar]);
$newVehicle = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$newVehicle) {
    die("選択した車両データが存在しません。");
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>配車変更確認 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
.section-title {
    font-size:22px; font-weight:bold; margin-bottom:18px;
}
table.confirm-table {
    width:100%; border-collapse:collapse;
    margin-bottom:30px;
}
.confirm-table th, .confirm-table td {
    border:1px solid #ccc; padding:12px; text-align:center;
}
.confirm-table th {
    background:#f2f2f2; width:40%;
}

.btn-area { text-align:center; margin-top:30px; }
.btn {
    padding:12px 30px; border-radius:6px;
    font-size:16px; text-decoration:none; margin:0 10px;
}
.btn-ok { background:#0A84FF; color:#fff; }
.btn-back { background:#555; color:#fff; }
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container">

    <h1 class="section-title">配車変更確認</h1>

    <p><b>予約番号：</b><?= htmlspecialchars($resNo) ?></p>

    <!-- 現在の車両 -->
    <h2 style="margin-top:25px;">現在の車両</h2>
    <table class="confirm-table">
        <tr><th>ナンバープレート</th><td><?= htmlspecialchars($res["current_plate"] ?: "未定") ?></td></tr>
        <tr><th>車種</th><td><?= htmlspecialchars($res["current_model"] ?: "未定") ?></td></tr>
        <tr><th>定員</th><td><?= htmlspecialchars($res["current_capacity"] ?: "―") ?></td></tr>
    </table>

    <!-- 新しい車両 -->
    <h2>変更後の車両</h2>
    <table class="confirm-table">
        <tr><th>ナンバープレート</th><td><?= htmlspecialchars($newVehicle["number_plate"]) ?></td></tr>
        <tr><th>車種</th><td><?= htmlspecialchars($newVehicle["car_model_name"]) ?></td></tr>
        <tr><th>定員</th><td><?= htmlspecialchars($newVehicle["vehicle_capacity"]) ?></td></tr>
    </table>

    <div class="btn-area">
        <a href="uw113_05_change_vehicle_done.php?r=<?= urlencode($resNo) ?>&car=<?= urlencode($newVehicle["number_plate"]) ?>" 
           class="btn btn-ok">確定する</a>

        <a href="uw113_03_change_vehicle.php?r=<?= urlencode($resNo) ?>" 
           class="btn btn-back">戻る</a>
    </div>

</div>
</body>
</html>
