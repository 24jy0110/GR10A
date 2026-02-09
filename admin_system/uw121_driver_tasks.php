<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ============================================================
   取得：该司机的所有「已接订单」
   STC02 → 予約確定（未来行程）
   STC04 → 運行中（当前行程）
============================================================ */
$sql = "
SELECT 
    reservation_number,
    service_start_time,
    service_end_date,
    ride_location,
    customer_name,
    ride_count,
    car_model_code
FROM reservation
WHERE driver_id = :id
  AND state_code IN ('STC02', 'STC04')
ORDER BY service_start_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $driver_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   分类：当前行程 / 最近的未来行程 / 其他未来行程
============================================================ */
$current_task = null;
$next_task = null;
$future_tasks = [];

$now = time();

foreach ($jobs as $j) {

    $start = strtotime($j["service_start_time"]);
    $end   = strtotime($j["service_end_date"]);

    /* ---- case 1: 已经在运行中的订单（STC04） ---- */
    if ($start <= $now && $now <= $end) {
        $current_task = $j;
        continue;
    }

    /* ---- case 2: 还未开始的未来订单（STC02） ---- */
    if ($start > $now) {
        $future_tasks[] = $j;
    }
}

/* ---- 找出最近的未来订单作为 “次の行程” ---- */
if (!$current_task && !empty($future_tasks)) {
    $next_task = $future_tasks[0];       // 最早的未来订单
    array_shift($future_tasks);          // 剩下的继续显示在下方
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
}

h1 {
    font-size:26px;
    margin-bottom:25px;
}

/* 卡片样式 */
.task-card {
    border:2px solid #000;
    padding:18px;
    border-radius:6px;
    margin-bottom:20px;
}

.task-title {
    font-size:20px;
    font-weight:bold;
    margin-bottom:10px;
}

.detail-btn {
    padding:8px 16px;
    background:#0A84FF;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    display:inline-block;
    margin-top:10px;
}
.detail-btn:hover {
    background:#0066cc;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<h1>乗務確認</h1>

<!-- ============================================================
     ① 当前行程（運行中）
============================================================ -->
<?php if ($current_task): ?>
<div class="task-card">
    <div class="task-title">【現在の行程】</div>

    <b>予約番号：</b><?= htmlspecialchars($current_task["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($current_task["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($current_task["service_end_date"]) ?><br>
    <b>乗車場所：</b><?= nl2br(htmlspecialchars($current_task["ride_location"])) ?><br>
    <b>乗車人数：</b><?= htmlspecialchars($current_task["ride_count"]) ?> 名<br>

    <a class="detail-btn"
       href="uw12101_driver_task_detail.php?r=<?= urlencode($current_task["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>

<?php endif; ?>


<!-- ============================================================
     ② 次の行程（未来订单 1 条）
============================================================ -->
<?php if (!$current_task && $next_task): ?>
<div class="task-card">
    <div class="task-title">【次の行程】</div>

    <b>予約番号：</b><?= htmlspecialchars($next_task["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($next_task["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($next_task["service_end_date"]) ?><br>
    <b>乗車場所：</b><?= nl2br(htmlspecialchars($next_task["ride_location"])) ?><br>
    <b>乗車人数：</b><?= htmlspecialchars($next_task["ride_count"]) ?> 名<br>

    <a class="detail-btn"
       href="uw12101_driver_task_detail.php?r=<?= urlencode($next_task["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>
<?php endif; ?>


<!-- ============================================================
     ③ 未来行程（次の行程以外全て）
============================================================ -->
<?php if (!empty($future_tasks)): ?>
<h2 style="margin-top:40px;">今後の行程</h2>

<?php foreach ($future_tasks as $f): ?>
<div class="task-card">
    <b>予約番号：</b><?= htmlspecialchars($f["reservation_number"]) ?><br>
    <b>乗車日時：</b><?= htmlspecialchars($f["service_start_time"]) ?><br>
    <b>降車日：</b><?= htmlspecialchars($f["service_end_date"]) ?><br>
    <b>乗車場所：</b><?= nl2br(htmlspecialchars($f["ride_location"])) ?><br>
    <b>乗車人数：</b><?= htmlspecialchars($f["ride_count"]) ?> 名<br>

    <a class="detail-btn"
       href="uw12101_driver_task_detail.php?r=<?= urlencode($f["reservation_number"]) ?>">
        詳細を見る
    </a>
</div>
<?php endforeach; ?>

<?php endif; ?>


<!-- ============================================================
     ④ 行程为空
============================================================ -->
<?php if (!$current_task && !$next_task && empty($future_tasks)): ?>
<p>現在、担当している行程はありません。</p>
<p>今後の行程もありません。</p>
<?php endif; ?>
<div style="text-align:center; margin-top:35px;">
    <a href="uw120.php"
       style="
           display:inline-block;
           padding:12px 28px;
           background:#000;
           color:#fff;
           text-decoration:none;
           border-radius:6px;
           font-size:16px;
       ">
        メニューへ戻る
    </a>
</div>
</body>
</html>
