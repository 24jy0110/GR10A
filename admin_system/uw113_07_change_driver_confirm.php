<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r']) || !isset($_GET['driver'])) {
    header("Location: uw117_01_reservation_list.php");
    exit;
}

$resNo = $_GET['r'];
$newDriver = $_GET['driver'];

/* ---------------------------------------------------
   予約データ（現ドライバー含む）取得
--------------------------------------------------- */
$sql = "
SELECT 
    r.reservation_number,
    r.driver_id AS current_driver_id,

    d.language_id_1 AS cur_lang1,
    d.language_id_2 AS cur_lang2,
    d.language_id_3 AS cur_lang3,
    d.driver_email AS cur_email,

    e.employee_name AS current_driver_name
FROM reservation r
LEFT JOIN driver d ON d.employee_id = r.driver_id
LEFT JOIN employee e ON e.employee_id = r.driver_id
WHERE r.reservation_number = :no
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

/* 現在のドライバー言語名処理 */
$langMap = $pdo->query("SELECT language_category_id, language_category_name FROM language_category")->fetchAll(PDO::FETCH_KEY_PAIR);

function buildLangText($d, $langMap) {
    $ids = [];
    foreach (["cur_lang1","cur_lang2","cur_lang3"] as $col) {
        if (!empty($d[$col]) && $d[$col] !== "LCAT00") {
            $ids[] = $langMap[$d[$col]];
        }
    }
    return $ids ? implode(" / ", $ids) : "未定";
}

$currentLangText = buildLangText($res, $langMap);

/* ---------------------------------------------------
   新しいドライバー情報取得
--------------------------------------------------- */
$sql2 = "
SELECT 
    d.employee_id,
    d.driver_email,
    d.language_id_1,
    d.language_id_2,
    d.language_id_3,
    e.employee_name
FROM driver d
JOIN employee e ON e.employee_id = d.employee_id
WHERE d.employee_id = :id
";

$stmt2 = $pdo->prepare($sql2);
$stmt2->execute([":id" => $newDriver]);
$newInfo = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$newInfo) {
    die("選択したドライバーが存在しません。");
}

/* 新しいドライバーの言語 */
/* 新しいドライバーの言語 */
$newLangList = [];
foreach (["language_id_1","language_id_2","language_id_3"] as $col) {
    if (!empty($newInfo[$col]) && $newInfo[$col] !== "LCAT00") {
        $newLangList[] = $langMap[$newInfo[$col]] ?? "―";
    }
}
$newLangText = $newLangList ? implode(" / ", $newLangList) : "なし";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー変更確認 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
}
.section-title { font-size:22px; font-weight:bold; margin-bottom:18px; }

table.confirm-table {
    width:100%; border-collapse:collapse; margin-bottom:30px;
}
.confirm-table th, .confirm-table td {
    border:1px solid #ccc; padding:12px; text-align:center;
}
.confirm-table th {
    background:#f2f2f2; width:40%;
}

.btn-area { text-align:center; margin-top:30px; }
.btn {
    padding:12px 30px; border-radius:6px;
    font-size:16px; text-decoration:none; margin:0 10px;
}
.btn-ok { background:#0A84FF; color:#fff; }
.btn-back { background:#555; color:#fff; }
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container">

    <h1 class="section-title">ドライバー変更確認</h1>

    <p><b>予約番号：</b><?= htmlspecialchars($resNo) ?></p>

    <!-- 現在のドライバー -->
    <h2 style="margin-top:25px;">現在のドライバー</h2>
    <table class="confirm-table">
        <tr><th>氏名</th><td><?= htmlspecialchars($res["current_driver_name"] ?: "未定") ?></td></tr>
        <tr><th>社員ID</th><td><?= htmlspecialchars($res["current_driver_id"] ?: "未定") ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($currentLangText) ?></td></tr>
    </table>

    <!-- 新しいドライバー -->
    <h2>変更後のドライバー</h2>
    <table class="confirm-table">
        <tr><th>氏名</th><td><?= htmlspecialchars($newInfo["employee_name"]) ?></td></tr>
        <tr><th>社員ID</th><td><?= htmlspecialchars($newInfo["employee_id"]) ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($newLangText) ?></td></tr>
        <tr><th>メール</th><td><?= htmlspecialchars($newInfo["driver_email"]) ?></td></tr>
    </table>

    <div class="btn-area">
        <a href="uw113_08_change_driver_done.php?r=<?= urlencode($resNo) ?>&driver=<?= urlencode($newInfo["employee_id"]) ?>"
           class="btn btn-ok">確定する</a>

        <a href="uw113_06_change_driver.php?r=<?= urlencode($resNo) ?>" 
           class="btn btn-back">戻る</a>
    </div>

</div>

</body>
</html>
