<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];
$sales_office = $driver["sales_office_code"];

/* ---------------------------------------------------
   GET パラメータ
--------------------------------------------------- */
if (!isset($_GET["r"])) {
    header("Location: uw122_new_orders.php");
    exit;
}
$resNo = $_GET["r"];

/* ---------------------------------------------------
   0) 予約情報取得（含 車種/人数/日付）
--------------------------------------------------- */
$sql = "
SELECT r.*, cm.car_model_name, cm.car_model_capacity
FROM reservation r
LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code
WHERE reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当予約が存在しません。");
}

/* 接单前必须确保仍然是 STC01（仮予約） */
if ($res["state_code"] !== "STC01") {
    header("Location: uw122_order_unavailable.php?r=" . urlencode($resNo));
    exit;
}

/* 利用日付区間 */
$res_start = strtotime($res["service_start_time"]);
$res_end   = strtotime($res["service_end_date"]);
$need_capacity = intval($res["ride_count"]);


/* ---------------------------------------------------
   1) 自动配车逻辑
--------------------------------------------------- */

/* (A) 该营业所全部车辆 */
$sql_car = "
SELECT *
FROM vehicle
WHERE sales_office_code = :office
  AND vehicle_capacity >= :cap
";
$stmt = $pdo->prepare($sql_car);
$stmt->execute([
    ":office" => $sales_office,
    ":cap" => $need_capacity
]);
$allCars = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* (B) 获取这些车已有的订单（STC02, STC04 为占用中）*/
$sql_busy = "
SELECT number_plate, service_start_time, service_end_date
FROM reservation
WHERE number_plate IS NOT NULL
  AND state_code IN ('STC02','STC04')
";
$busyList = $pdo->query($sql_busy)->fetchAll();

/* 构建车辆 → 占用区间清单 */
$busyMap = [];
foreach ($busyList as $b) {
    $busyMap[$b["number_plate"]][] = [
        strtotime($b["service_start_time"]),
        strtotime($b["service_end_date"])
    ];
}

function conflict($newS, $newE, $existS, $existE) {
    return !($newE <= $existS || $existE <= $newS);
}

/* (C) 选可用车辆 */
$selected_plate = null;

foreach ($allCars as $car) {
    $plate = $car["number_plate"];

    $conflictFound = false;

    if (!empty($busyMap[$plate])) {
        foreach ($busyMap[$plate] as $pair) {
            if (conflict($res_start, $res_end, $pair[0], $pair[1])) {
                $conflictFound = true;
                break;
            }
        }
    }

    if (!$conflictFound) {
        $selected_plate = $plate;
        break;
    }
}

if ($selected_plate === null) {
    die("エラー：利用可能な車両が見つかりません。");
}



$upd = "
UPDATE reservation
SET driver_id = :driver,
    number_plate = :plate,
    state_code = 'STC02',
    reservation_date = NOW()
WHERE reservation_number = :no
  AND state_code = 'STC01'   /* ← 再度排他チェック */
";
$stmt = $pdo->prepare($upd);
$ok = $stmt->execute([
    ":driver" => $driver_id,
    ":plate"  => $selected_plate,
    ":no"     => $resNo
]);


if ($stmt->rowCount() === 0) {
    header("Location: uw122_order_unavailable.php?r=" . urlencode($resNo));
    exit;
}

mb_language("Japanese");
mb_internal_encoding("UTF-8");

ini_set("SMTP", "10.64.144.9");
ini_set("smtp_port", "25");
date_default_timezone_set('Asia/Tokyo');

$to = $res["customer_email"];
$subject = "【丸和交通】ご予約確定のお知らせ（予約番号：{$resNo}）";

$driverName = $driver["employee_name"];
$plate      = $selected_plate;


$start = new DateTime($res["service_start_time"]);
$end   = new DateTime($res["service_end_date"]);
$days  = $start->diff($end)->days;
if ($start->diff($end)->days == 0) {
    $days = 1;
} else {
    $days = $start->diff($end)->days + 2;
}



$dailyFee = $res["car_model_use_fee"] ?? '';
$totalFee = $res["usage_fee"] ?? '';

$body = <<<EOT
{$res["customer_name"]} 様

このたびは、丸和交通株式会社 観光タクシーサービスをご予約いただき、
誠にありがとうございます。

以下の内容にて、ご予約が確定いたしましたのでご確認ください。

━━━━━━━━━━━━━━━━━━━━━━
■ 予約番号
━━━━━━━━━━━━━━━━━━━━━━
{$resNo}

━━━━━━━━━━━━━━━━━━━━━━
■ 行程概要
━━━━━━━━━━━━━━━━━━━━━━
乗車日時　{$res["service_start_time"]}
乗車場所　{$res["ride_location"]}
降車場所　{$res["drop_off_location"]}
利用日数　{$days}日間

━━━━━━━━━━━━━━━━━━━━━━
■ 車両・ドライバー
━━━━━━━━━━━━━━━━━━━━━━
車種　　　　　　　　{$res["car_model_name"]}
車両番号　　　　　　{$plate}
ドライバー対応言語　日本語

━━━━━━━━━━━━━━━━━━━━━━
■ 料金明細（すべて税込）
━━━━━━━━━━━━━━━━━━━━━━
1日料金　{$dailyFee}円
合計　　 {$totalFee}円

━━━━━━━━━━━━━━━━━━━━━━
■ お問い合わせ
━━━━━━━━━━━━━━━━━━━━━━
丸和交通株式会社 観光タクシー予約センター
TEL：03-1234-5678（8:00～22:00）
MAIL：support@maruwa-taxi.jp

EOT;

$fromName = mb_encode_mimeheader("丸和交通", "UTF-8");
$headers  = "From: {$fromName} <support@maruwa-taxi.jp>";


mb_send_mail($to, $subject, $body, $headers);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>依頼受付完了 | 丸和交通</title>

<style>
body {
    font-family:"Noto Sans JP", sans-serif;
    background:#fafafa;
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

.done-box {
    padding:20px;
    background:#e6f7e6;
    border:1px solid #7ac27a;
    border-radius:8px;
    margin:25px 0;
    font-size:18px;
    font-weight:bold;
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

    <h1>依頼を受け付けました</h1>

    <div class="done-box">
        ご依頼の受付が完了しました。
    </div>

    <div class="info">
        <div>予約番号：<b><?= htmlspecialchars($resNo) ?></b></div>
        <div>配車：<b><?= htmlspecialchars($selected_plate) ?></b></div>
        <div>担当ドライバー：<b><?= htmlspecialchars($driver["employee_name"]) ?> 様</b></div>
    </div>

    <a href="uw120.php" class="btn-back">トップへ戻る</a>

</div>

</body>
</html>
