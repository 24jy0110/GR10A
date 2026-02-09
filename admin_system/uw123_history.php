<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ============================================================
   获取司机完了订单（STC05）
============================================================ */
$sql = "
SELECT 
    reservation_number,
    service_start_time,
    service_end_date,
    customer_name,
    ride_location,
    drop_off_location,
    usage_fee
FROM reservation
WHERE driver_id = :id
  AND state_code = 'STC05'
ORDER BY service_end_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $driver_id]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>過去の乗務一覧 | 丸和交通</title>

<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    background:#fafafa;
}
.container {
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:28px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
h1 {
    font-size:26px;
    font-weight:700;
    margin-bottom:25px;
}
.table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
.table th, .table td {
    border:1px solid #ccc;
    padding:12px;
    text-align:center;
}
.table th {
    background:#f2f2f2;
}
.detail-btn {
    padding:8px 16px;
    background:#000;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
}
.back-btn {
    display:inline-block;
    margin-top:25px;
    padding:10px 20px;
    background:#555;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

<h1>過去の乗務一覧</h1>

<?php if (empty($list)): ?>
    <p style="text-align:center; margin:40px 0; font-size:18px;">
        完了した乗務記録はありません。
    </p>
<?php else: ?>

<table class="table">
<tr>
    <th>予約番号</th>
    <th>期間</th>
    <th>顧客名</th>
    <th>乗車場所</th>
    <th>操作</th>
</tr>

<?php foreach ($list as $r): ?>
<tr>
    <td><?= htmlspecialchars($r["reservation_number"]) ?></td>
    <td>
        <?= date("Y/m/d", strtotime($r["service_start_time"])) ?>
        〜
        <?= date("Y/m/d", strtotime($r["service_end_date"])) ?>
    </td>
    <td><?= htmlspecialchars($r["customer_name"]) ?></td>
    <td><?= nl2br(htmlspecialchars($r["ride_location"])) ?></td>
    <td>
        <a class="detail-btn"
           href="uw12301_history_detail.php?r=<?= $r["reservation_number"] ?>">
           詳細
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php endif; ?>

<a href="uw120.php" class="back-btn">戻る</a>

</div>

</body>
</html>
