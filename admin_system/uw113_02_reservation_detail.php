<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET パラメータ
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw113_01_reservation_list.php");
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
   SQL: 予約 + 状態 + 車種 + ドライバー
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    s.state_name,
    cm.car_model_name,
    cm.car_model_capacity,

    d.language_id_1,
    d.language_id_2,
    d.language_id_3,
    d.driver_email,

    e.employee_name AS driver_name

FROM reservation r
LEFT JOIN reservation_state s ON r.state_code = s.state_code
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
LEFT JOIN driver d ON d.employee_id = r.driver_id
LEFT JOIN employee e ON e.employee_id = d.employee_id
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当する予約が見つかりません。");
}

/* ---------------------------------------------------
   日付などの加工
--------------------------------------------------- */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));

$start = new DateTime($res["service_start_time"]);
$end   = new DateTime($res["service_end_date"]);
$days  = $start->diff($end)->days;
if ($start->diff($end)->days == 0) {
    $days = 1;
} else {
    $days = $start->diff($end)->days + 2;
}

/* ドライバー情報 */
$driverName  = $res["driver_name"] ?: "未定";
$driverEmail = $res["driver_email"] ?: "未定";

/* 言語 */
$langs = [];
foreach (["language_id_1", "language_id_2", "language_id_3"] as $col) {
    if (!empty($res[$col]) && isset($langMap[$res[$col]])) {
        $langs[] = $langMap[$res[$col]];
    }
}
$driverLangText = $langs ? implode(" / ", $langs) : "未定";

/* 状態 badge 色 */
$stateColor = [
    "STC01" => "#ff9800",
    "STC02" => "#2196f3",
    "STC04" => "#00bcd4",
    "STC05" => "#4caf50",
    "STC03" => "#9e9e9e"
];
$badgeColor = $stateColor[$res["state_code"]] ?? "#333";

/* 配車センターが変更可能な状態か？ */
$canChange = ($res["state_code"] === "STC02");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>配車詳細 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:8px;
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
.btn-back {
    padding:12px 30px; background:#555; color:#fff;
    text-decoration:none; border-radius:5px; margin-right:20px;
}
.btn-action {
    padding:12px 30px; background:#0A84FF; color:#fff;
    text-decoration:none; border-radius:5px; margin:0 10px;
}
.btn-disabled {
    padding:12px 30px; background:#aaa; color:#fff;
    border-radius:5px; text-decoration:none;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container">

    <h1 style="display:flex; justify-content:space-between; align-items:center;">
        配車詳細
        <span class="state-badge" style="background:<?= $badgeColor ?>">
            <?= htmlspecialchars($res["state_name"]) ?>
        </span>
    </h1>

    <p><b>予約番号：</b><?= htmlspecialchars($res["reservation_number"]) ?></p>

    <!-- ①乗車情報 -->
    <div class="section-title">① 乗車情報</div>
    <table class="detail-table">
        <tr><th>乗車日時</th><td><?= $rideDate ?></td></tr>
        <tr><th>利用日数</th><td><?= $days ?> 日</td></tr>
        <tr><th>降車日</th><td><?= htmlspecialchars($res["service_end_date"]) ?></td></tr>
        <tr><th>乗車場所</th><td><?= nl2br(htmlspecialchars($res["ride_location"])) ?></td></tr>
        <tr><th>降車場所</th><td><?= nl2br(htmlspecialchars($res["drop_off_location"])) ?></td></tr>
    </table>

    <!-- ②乗客情報 -->
    <div class="section-title">② 乗客情報</div>
    <table class="detail-table">
        <tr><th>名前</th><td><?= htmlspecialchars($res["customer_name"]) ?></td></tr>
        <tr><th>カタカナ</th><td><?= htmlspecialchars($res["customer_name_kana"] ?: "なし") ?></td></tr>
        <tr><th>電話番号</th><td><?= htmlspecialchars($res["customer_phone"]) ?></td></tr>
        <tr><th>メール</th><td><?= htmlspecialchars($res["customer_email"]) ?></td></tr>
        <tr><th>人数</th><td><?= htmlspecialchars($res["ride_count"]) ?> 名</td></tr>
    </table>

    <!-- ③配車情報 -->
    <div class="section-title">③ 配車情報</div>
    <table class="detail-table">
        <tr><th>車種</th><td><?= htmlspecialchars($res["car_model_name"] ?: "未定") ?></td></tr>
        <tr><th>ナンバープレート</th><td><?= htmlspecialchars($res["number_plate"] ?: "未定") ?></td></tr>
        <tr><th>ドライバー名</th><td><?= htmlspecialchars($driverName) ?></td></tr>
        <tr><th>連絡先</th><td><?= htmlspecialchars($driverEmail) ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($driverLangText) ?></td></tr>
        <tr><th>料金</th><td><?= number_format($res["usage_fee"]) ?> 円</td></tr>
    </table>

    <div class="btn-area">

        <a href="uw113_01_reservation_list.php" class="btn-back">一覧に戻る</a>

        <?php if ($canChange): ?>
            <a href="uw113_03_change_vehicle.php?r=<?= $resNo ?>" class="btn-action">
                配車を変更する
            </a>
            <a href="uw113_06_change_driver.php?r=<?= $resNo ?>" class="btn-action">
                ドライバーを変更する
            </a>
        <?php else: ?>
            <span class="btn-disabled">状態が確定以外のため変更不可</span>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
