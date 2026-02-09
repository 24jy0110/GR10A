<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ============================================================
    取得ドライバー言語
============================================================ */
$sql_lang = "SELECT language_id_1, language_id_2, language_id_3 
             FROM driver WHERE employee_id = :id";
$stmt = $pdo->prepare($sql_lang);
$stmt->execute([":id" => $driver_id]);
$langRow = $stmt->fetch();

$myLangs = array_filter([
    $langRow["language_id_1"] ?? null,
    $langRow["language_id_2"] ?? null,
    $langRow["language_id_3"] ?? null
]);

/* ============================================================
    自分の担当中（確定 or 運行中）を取得
============================================================ */
$sql_myJobs = "
SELECT service_start_time, service_end_date
FROM reservation
WHERE driver_id = :id AND state_code IN ('STC02', 'STC04')
";
$stmt = $pdo->prepare($sql_myJobs);
$stmt->execute([":id" => $driver_id]);
$myJobs = $stmt->fetchAll();

/* 時間衝突チェック */
function isTimeConflict($start1, $end1, $start2, $end2) {
    return !($end1 <= $start2 || $end2 <= $start1);
}

/* ============================================================
    STC01（仮予約）で、ドライバーが接取可能な依頼をカウント
============================================================ */
$sql_new = "
SELECT *
FROM reservation
WHERE state_code = 'STC01'
ORDER BY reservation_date ASC
";
$orders = $pdo->query($sql_new)->fetchAll();

$available_count = 0;

foreach ($orders as $o) {

    $o_start = strtotime($o["service_start_time"]);
    $o_end   = strtotime($o["service_end_date"]);

    /* ---- 言語マッチ（主 or 副言語どちらか合えばOK） ---- */
    $lang_ok = false;
    if (in_array($o["lang_pref_1"], $myLangs)) $lang_ok = true;
    if (!empty($o["lang_pref_2"]) && in_array($o["lang_pref_2"], $myLangs)) $lang_ok = true;

    if (!$lang_ok) continue;

    /* ---- 時間衝突 ---- */
    $conflict = false;
    foreach ($myJobs as $mj) {
        if (isTimeConflict(
            strtotime($mj["service_start_time"]),
            strtotime($mj["service_end_date"]),
            $o_start,
            $o_end
        )) {
            $conflict = true;
            break;
        }
    }
    if ($conflict) continue;

    /* ---- 条件クリア：可接单 ---- */
    $available_count++;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>乗務員メニュー | 丸和交通</title>
<link rel="stylesheet" href="assets/app.css">

<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    text-align: center;
    margin-top: 60px;
}

/* menu button */
.menu-btn {
    width: 260px;
    padding: 22px 0;
    margin: 22px auto;
    background: black;
    color: white;
    font-size: 20px;
    border-radius: 6px;
    text-decoration: none;
    display: block;
    position: relative;
}

/* badge */
.badge {
    position: absolute;
    top: -10px;
    right: -12px;
    background: red;
    color: white;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header_driver.php"; ?>

<h2 style="font-size:26px; margin-top:40px;">
    ようこそ、<?= htmlspecialchars($driver["sales_office_name"] ?? "") ?>　乗務員課<br>
    <?= htmlspecialchars($driver["employee_name"]) ?> 様
</h2>

<!-- ① 当前行程 -->
<a href="uw121_driver_tasks.php" class="menu-btn">乗務確認</a>

<!-- ② 新订单 -->
<a href="uw122_new_orders.php" class="menu-btn">
    新規依頼
    <?php if ($available_count > 0): ?>
        <span class="badge"><?= $available_count ?></span>
    <?php endif; ?>
</a>

<!-- ③ 过去订单 -->
<a href="uw123_history.php" class="menu-btn">
    過去の乗務一覧
</a>

</body>
</html>
