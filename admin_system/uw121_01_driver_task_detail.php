<?php
session_start();
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

$driver = $_SESSION['employee'];
$driver_id = $driver['employee_id'];

/* ---------------------------------------------------
   GET パラメータ
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw121_driver_tasks.php");
    exit;
}
$resNo = $_GET['r'];

/* ---------------------------------------------------
   言語マップ
--------------------------------------------------- */
$langMap = [
    "LCAT00" => "日本語",
    "LCAT01" => "英語",
    "LCAT02" => "中国語",
    "LCAT03" => "韓国語",
    "LCAT04" => "ドイツ語",
    "LCAT05" => "スペイン語",
    "LCAT06" => "フランス語"
];

/* ---------------------------------------------------
   SQL：予約 + 状態 + 車種 + 配車済みドライバー情報
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
  AND r.driver_id = :driver
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":no" => $resNo,
    ":driver" => $driver_id
]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("この予約はあなたの担当ではありません。");
}

/* ---------------------------------------------------
   日付処理
--------------------------------------------------- */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));
$start = new DateTime(date("Y-m-d", strtotime($res["service_start_time"])));
$end   = new DateTime(date("Y-m-d", strtotime($res["service_end_date"])));

// 日付のみの差（時間無視）
$diffDays = (int)$start->diff($end)->days;

// 利用日数 = 差分 + 1
$days = $diffDays + 1;


/* ドライバー言語 */
$langs = [];
foreach (["language_id_1","language_id_2","language_id_3"] as $col) {
    if (!empty($res[$col]) && isset($langMap[$res[$col]])) {
        $langs[] = $langMap[$res[$col]];
    }
}
$driverLangText = $langs ? implode(" / ", $langs) : "未定";

/* 状態バッジ色 */
$stateColor = [
    "STC01" => "#ff9800",
    "STC02" => "#2196f3",
    "STC04" => "#00bcd4",
    "STC05" => "#4caf50",
    "STC03" => "#9e9e9e"
];
$badgeColor = $stateColor[$res["state_code"]] ?? "#333";

/* ---------------------------------------------------
   行程完了ボタンの条件：STC04（運行中）
--------------------------------------------------- */
$canFinish = ($res["state_code"] === "STC04");

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>行程詳細 | 丸和交通</title>

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
    width:100%; border-collapse:collapse; margin-top:18px; text-align:center;
}
.detail-table th, .detail-table td {
    border:1px solid #ccc; padding:12px; font-size:15px;
}
.detail-table th { background:#f2f2f2; width:220px; }
.state-badge {
    padding:8px 16px; border-radius:4px; color:#fff; font-weight:bold;
}

/* 按钮区域 */
.btn-area { margin-top:35px; text-align:center; }

.btn-back {
    padding:12px 30px; background:#555; color:#fff;
    text-decoration:none; border-radius:5px; margin-right:20px;
}

.btn-finish {
    padding:12px 30px; 
    background:#007e33;
    color:#fff;
    text-decoration:none; 
    border-radius:5px;
    cursor:pointer;
}
.btn-finish:hover {
    background:#005e25;
}
</style>

<script>
function finishTask(no) {
    if (confirm("この行程を完了として登録しますか？")) {
        location.href = "uw121_02_finish_task.php?r=" + no;
    }
}
</script>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<div class="container">

    <h1 style="display:flex; justify-content:space-between; align-items:center;">
        行程詳細
        <span class="state-badge" style="background:<?= $badgeColor ?>">
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
        <tr><th>名前</th><td><?= htmlspecialchars($res["customer_name"]) ?></td></tr>
        <tr><th>カタカナ</th><td><?= htmlspecialchars($res["customer_name_kana"] ?: "なし") ?></td></tr>
        <tr><th>電話番号</th><td><?= htmlspecialchars($res["customer_phone"]) ?></td></tr>
        <tr><th>メール</th><td><?= htmlspecialchars($res["customer_email"]) ?></td></tr>
        <tr><th>人数</th><td><?= htmlspecialchars($res["ride_count"]) ?> 名</td></tr>
    </table>

    <!-- ③ 配車情報（司机端为只读） -->
    <div class="section-title">③ 配車情報</div>
    <table class="detail-table">
        <tr><th>車種</th><td><?= htmlspecialchars($res["car_model_name"] ?: "未定") ?></td></tr>
        <tr><th>ナンバープレート</th><td><?= htmlspecialchars($res["number_plate"] ?: "未定") ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($driverLangText) ?></td></tr>
        <tr><th>ドライバー連絡先</th><td><?= htmlspecialchars($res["driver_email"] ?: "未定") ?></td></tr>
    </table>

    <div class="btn-area">
        <a href="uw121_driver_tasks.php" class="btn-back">行程一覧に戻る</a>

        <?php if ($canFinish): ?>
            <button class="btn-finish"
                onclick="finishTask('<?= $resNo ?>')">
                行程完了
            </button>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
