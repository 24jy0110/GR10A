<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ============================================================
   取得：该司机的所有订单
============================================================ */
$sql = "
SELECT 
    reservation_number,
    service_start_time,
    service_end_date,
    ride_location,
    customer_name,
    ride_count,
    state_code
FROM reservation
WHERE driver_id = :id
ORDER BY service_start_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $driver_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ============================================================
   分类
============================================================ */
$current_task = null;
$future_tasks = [];
$history_tasks = [];

$now = time();

foreach ($jobs as $j) {

    $start = strtotime($j["service_start_time"]);
    $end   = strtotime($j["service_end_date"] . " 23:59:59");

    /* 当前行程 */
    if ($j["state_code"] === "STC04") {
        $current_task = $j;
        continue;
    }

    /* 未来行程 */
    if ($j["state_code"] === "STC02") {
        $future_tasks[] = $j;
        continue;
    }

    /* 历史 */
    if (in_array($j["state_code"], ["STC05","STC03"])) {
        $history_tasks[] = $j;
        continue;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>乗務確認 | 丸和交通</title>

<style>
body {
    font-family:'Noto Sans JP',sans-serif;
    margin:40px;
    background:#fafafa;
}

h1 { font-size:28px; margin-bottom:30px; }

.section-title {
    font-size:20px;
    font-weight:bold;
    margin:35px 0 15px;
}

.task-card {
    background:#fff;
    border:1px solid #ddd;
    border-radius:8px;
    padding:18px;
    margin-bottom:15px;
    box-shadow:0 2px 6px rgba(0,0,0,.06);
}

.task-card b { display:inline-block; width:100px; }

.detail-btn {
    margin-top:10px;
    display:inline-block;
    padding:8px 16px;
    background:#0A84FF;
    color:#fff;
    border-radius:4px;
    text-decoration:none;
}

.detail-btn:hover { background:#0066cc; }

.badge {
    display:inline-block;
    padding:4px 10px;
    border-radius:12px;
    font-size:12px;
    color:#fff;
    margin-left:5px;
}

.badge-STC02 { background:#2196f3; }
.badge-STC04 { background:#00bcd4; }
.badge-STC05 { background:#4caf50; }
.badge-STC03 { background:#9e9e9e; }

.menu-btn {
    display:inline-block;
    padding:12px 28px;
    background:#000;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    margin-top:40px;
}
</style>
</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<h1>乗務確認</h1>

<!-- ============================================================
     当前行程
============================================================ -->
<?php if ($current_task): ?>
<div class="section-title">現在の行程</div>

<div class="task-card">
    <b>予約番号：</b><?= htmlspecialchars($current_task["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($current_task["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($current_task["service_end_date"]) ?><br>
    <b>乗車場所：</b><?= nl2br(htmlspecialchars($current_task["ride_location"])) ?><br>
    <b>人数：</b><?= htmlspecialchars($current_task["ride_count"]) ?> 名<br>

    <span class="badge badge-STC04">運行中</span><br>

    <a class="detail-btn"
       href="uw121_02_driver_task_detail.php?r=<?= urlencode($current_task["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>
<?php endif; ?>


<!-- ============================================================
     未来行程
============================================================ -->
<?php if (!empty($future_tasks)): ?>
<div class="section-title">今後の行程</div>

<?php foreach ($future_tasks as $f): ?>
<div class="task-card">
    <b>予約番号：</b><?= htmlspecialchars($f["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($f["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($f["service_end_date"]) ?><br>
    <b>人数：</b><?= htmlspecialchars($f["ride_count"]) ?> 名<br>

    <span class="badge badge-STC02">予約確定</span><br>

    <a class="detail-btn"
       href="uw121_02_driver_task_detail.php?r=<?= urlencode($f["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>
<?php endforeach; ?>
<?php endif; ?>


<!-- ============================================================
     历史行程
============================================================ -->
<?php if (!empty($history_tasks)): ?>
<div class="section-title">過去の行程</div>

<?php foreach ($history_tasks as $h): ?>
<div class="task-card">
    <b>予約番号：</b><?= htmlspecialchars($h["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($h["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($h["service_end_date"]) ?><br>

    <?php if ($h["state_code"] === "STC05"): ?>
        <span class="badge badge-STC05">完了</span>
    <?php else: ?>
        <span class="badge badge-STC03">キャンセル</span>
    <?php endif; ?>

    <br>

    <a class="detail-btn"
       href="uw121_02_driver_task_detail.php?r=<?= urlencode($h["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>
<?php endforeach; ?>
<?php endif; ?>


<?php if (!$current_task && empty($future_tasks) && empty($history_tasks)): ?>
<p>現在、担当している行程はありません。</p>
<?php endif; ?>


<div style="text-align:center;">
    <a href="uw120.php" class="menu-btn">メニューへ戻る</a>
</div>

</body>
</html>
