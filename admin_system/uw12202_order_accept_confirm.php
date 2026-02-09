<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

/* ---------------------------------------------------
   GET パラメータ
--------------------------------------------------- */
if (!isset($_GET["r"])) {
    header("Location: uw122_new_orders.php");
    exit;
}
$resNo = $_GET["r"];

/* ---------------------------------------------------
   予約情報取得
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    cm.car_model_name
FROM reservation r
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
WHERE reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当する予約が見つかりません。");
}

/* 乗車日時 */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));

/* 日数計算 */
$start = new DateTime($res["service_start_time"]);
$end   = new DateTime($res["service_end_date"]);
$days  = $start->diff($end)->days + 1;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>依頼受付確認 | 丸和交通</title>

<style>
body {
    font-family:"Noto Sans JP",sans-serif;
    background:#fafafa;
    margin:0;
}
.container {
    max-width:800px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    text-align:center;
}

h1 {
    font-size:24px;
    font-weight:bold;
    margin-bottom:25px;
}

.info-table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
.info-table th, .info-table td {
    border:1px solid #ccc;
    padding:12px;
    font-size:15px;
}
.info-table th {
    background:#f2f2f2;
    width:230px;
    font-weight:600;
}

.msg-box {
    margin-top:30px;
    padding:18px;
    font-size:18px;
    background:#fff7e0;
    border:1px solid #f0d48a;
    border-radius:6px;
    font-weight:bold;
}

.btn-area {
    margin-top:35px;
}

.btn {
    padding:12px 32px;
    font-size:16px;
    border-radius:6px;
    text-decoration:none;
    color:#fff;
    margin:0 15px;
    display:inline-block;
}
.btn-back { background:#555; }
.btn-back:hover { background:#333; }

.btn-yes { background:#000; }
.btn-yes:hover { background:#222; }
</style>

</head>

<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

<h1>依頼受付の確認</h1>

<p class="msg-box">この依頼を受け付けてもよろしいですか？</p>

<table class="info-table">
<tr><th>予約番号</th><td><?= htmlspecialchars($res["reservation_number"]) ?></td></tr>
<tr><th>乗車日時</th><td><?= $rideDate ?></td></tr>
<tr><th>利用日数</th><td><?= $days ?> 日</td></tr>
<tr><th>乗車場所</th><td><?= nl2br(htmlspecialchars($res["ride_location"])) ?></td></tr>
<tr><th>降車場所</th><td><?= nl2br(htmlspecialchars($res["drop_off_location"])) ?></td></tr>
<tr><th>車種</th><td><?= htmlspecialchars($res["car_model_name"]) ?></td></tr>
<tr><th>人数</th><td><?= htmlspecialchars($res["ride_count"]) ?> 名</td></tr>
<tr><th>顧客名</th><td><?= htmlspecialchars($res["customer_name"]) ?></td></tr>
</table>

<div class="btn-area">
    <a href="uw12201_order_detail.php?r=<?= urlencode($resNo) ?>" class="btn btn-back">戻る</a>

    <a href="uw12202_order_accept_done.php?r=<?= urlencode($resNo) ?>"
       class="btn btn-yes">
        はい、この依頼を受け付ける
    </a>
</div>

</div>

</body>
</html>
