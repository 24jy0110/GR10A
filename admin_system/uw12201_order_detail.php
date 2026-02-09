<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

/* ---------------------------------------------------
   GET（调用方式：uw122_new_orders.php?r=xxxx）
--------------------------------------------------- */
if (!isset($_GET["r"])) {
    header("Location: uw122_new_orders.php");
    exit;
}
$resNo = $_GET["r"];

/* ---------------------------------------------------
   获取订单详细
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    s.state_name,
    cm.car_model_name,
    cm.car_model_capacity
FROM reservation r
LEFT JOIN reservation_state s ON r.state_code = s.state_code
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当する予約がありません。");
}

/* 状态颜色 */
$stateColor = [
    "STC01" => "#ff9800",
    "STC02" => "#2196f3",
    "STC04" => "#00bcd4",
    "STC05" => "#4caf50",
    "STC03" => "#9e9e9e"
];
$badgeColor = $stateColor[$res["state_code"]] ?? "#333";

/* 其他加工 */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));

$start = new DateTime($res["service_start_time"]);
$end   = new DateTime($res["service_end_date"]);
$days  = $start->diff($end)->days + 1;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>依頼詳細 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; margin:0; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
.section-title {
    margin-top:35px; font-size:20px; font-weight:bold;
    padding-bottom:8px; border-bottom:2px solid #333;
}
.detail-table {
    width:100%; border-collapse:collapse; margin-top:18px;
}
.detail-table th, .detail-table td {
    border:1px solid #ccc; padding:12px; font-size:15px;
}
.detail-table th { background:#f2f2f2; width:220px; }

.state-badge {
    padding:8px 16px; border-radius:4px; color:#fff; font-weight:bold;
}

.btn-area { margin-top:35px; text-align:center; }

.btn-back, .btn-accept {
    padding:12px 30px; border-radius:6px;
    text-decoration:none; color:#fff; margin:0 12px;
    font-size:16px; display:inline-block;
}

.btn-back { background:#555; }
.btn-back:hover { background:#333; }

.btn-accept { background:#000; }
.btn-accept:hover { background:#222; }
</style>
</head>

<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

<h1 style="display:flex; justify-content:space-between; align-items:center;">
    依頼詳細
    <span class="state-badge" style="background:<?= $badgeColor ?>;">
        <?= htmlspecialchars($res["state_name"]) ?>
    </span>
</h1>

<p><b>予約番号：</b><?= htmlspecialchars($res["reservation_number"]) ?></p>

<!-- ① 乗車情報 -->
<div class="section-title">① 乗車情報</div>
<table class="detail-table">
<tr><th>乗車日時</th><td><?= $rideDate ?></td></tr>
<tr><th>利用日数</th><td><?= $days ?> 日</td></tr>
<tr><th>降車日</th><td><?= htmlspecialchars($res["service_end_date"]) ?></td></tr>
<tr><th>乗車場所</th><td><?= nl2br(htmlspecialchars($res["ride_location"])) ?></td></tr>
<tr><th>降車場所</th><td><?= nl2br(htmlspecialchars($res["drop_off_location"])) ?></td></tr>
</table>

<!-- ② 乗客情報 -->
<div class="section-title">② 乗客情報</div>
<table class="detail-table">
<tr><th>顧客名</th><td><?= htmlspecialchars($res["customer_name"]) ?></td></tr>
<tr><th>人数</th><td><?= htmlspecialchars($res["ride_count"]) ?> 名</td></tr>
<tr><th>電話番号</th><td><?= htmlspecialchars($res["customer_phone"]) ?></td></tr>
<tr><th>メール</th><td><?= htmlspecialchars($res["customer_email"]) ?></td></tr>
</table>

<!-- ③ 言語要求 -->
<div class="section-title">③ 言語</div>
<table class="detail-table">
<tr><th>主な希望言語</th><td><?= htmlspecialchars($res["lang_pref_1"]) ?></td></tr>
<tr><th>副言語</th><td><?= htmlspecialchars($res["lang_pref_2"] ?: "なし") ?></td></tr>
</table>

<!-- ④ 操作 -->
<div class="btn-area">
    <a href="uw122_new_orders.php" class="btn-back">一覧へ戻る</a>

    <a href="uw12202_order_accept_confirm.php?r=<?= urlencode($resNo) ?>"
       class="btn-accept">
        この依頼を受ける
    </a>
</div>

</div>

</body>
</html>
