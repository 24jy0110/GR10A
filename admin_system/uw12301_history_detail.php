<?php
session_start();
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET パラメータ
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw123_history.php");
    exit;
}
$resNo = $_GET['r'];

/* ---------------------------------------------------
   言語マップ
--------------------------------------------------- */
$langMap = [
    "LCAT01" => "日本語",
    "LCAT02" => "英語",
    "LCAT03" => "中国語",
    "LCAT04" => "韓国語",
    "LCAT05" => "ドイツ語",
    "LCAT06" => "スペイン語",
    "LCAT07" => "フランス語"
];

/* ---------------------------------------------------
   SQL：予約 + 車種 + ドライバー情報
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    cm.car_model_name,
    d.driver_email,
    e.employee_name AS driver_name
FROM reservation r
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
LEFT JOIN driver d ON d.employee_id = r.driver_id
LEFT JOIN employee e ON e.employee_id = d.employee_id
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当する乗務記録が見つかりません。");
}

/* ---------------------------------------------------
   表示加工
--------------------------------------------------- */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));

$startDate = new DateTime(date('Y-m-d', strtotime($res["service_start_time"])));
$endDate   = new DateTime(date('Y-m-d', strtotime($res["service_end_date"])));
$days = $startDate->diff($endDate)->days + 1;


/* 言語 */
$langs = [];
foreach (["language_id_1","language_id_2","language_id_3"] as $col) {
    if (!empty($res[$col]) && isset($langMap[$res[$col]])) {
        $langs[] = $langMap[$res[$col]];
    }
}
$driverLangText = $langs ? implode(" / ", $langs) : "なし";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>乗務履歴詳細 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.1);
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
.btn-area { margin-top:35px; text-align:center; }
.btn-back {
    padding:12px 30px; background:#555; color:#fff;
    text-decoration:none; border-radius:5px;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

    <h1 style="margin-bottom:10px;">乗務履歴 詳細</h1>
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
        <tr><th>電話番号</th><td><?= htmlspecialchars($res["customer_phone"]) ?></td></tr>
        <tr><th>メール</th><td><?= htmlspecialchars($res["customer_email"]) ?></td></tr>
        <tr><th>人数</th><td><?= htmlspecialchars($res["ride_count"]) ?> 名</td></tr>
    </table>

    <!-- ③ 配車情報 -->
    <div class="section-title">③ 配車情報</div>
    <table class="detail-table">
        <tr><th>車種</th><td><?= htmlspecialchars($res["car_model_name"]) ?></td></tr>
        <tr><th>ナンバープレート</th><td><?= htmlspecialchars($res["number_plate"]) ?></td></tr>
        <tr><th>担当ドライバー</th><td><?= htmlspecialchars($res["driver_name"]) ?></td></tr>
        <tr><th>連絡先</th><td><?= htmlspecialchars($res["driver_email"]) ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($driverLangText) ?></td></tr>
        <tr><th>合計料金</th><td><?= number_format($res["usage_fee"]) ?> 円</td></tr>
    </table>

    <div class="btn-area">
        <a href="uw123_history.php" class="btn-back">一覧に戻る</a>
    </div>

</div>

</body>
</html>
