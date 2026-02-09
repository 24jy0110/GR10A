<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET チェック
--------------------------------------------------- */
if (!isset($_GET['r']) || !isset($_GET['driver'])) {
    header("Location: uw113_01_reservation_list.php");
    exit;
}

$resNo = $_GET['r'];
$newDriver = $_GET['driver'];

/* ---------------------------------------------------
   ドライバー更新
--------------------------------------------------- */
$update_sql = "
UPDATE reservation
SET driver_id = :driver
WHERE reservation_number = :no
";
$stmt = $pdo->prepare($update_sql);
$stmt->execute([
    ":driver" => $newDriver,
    ":no"     => $resNo
]);

/* ---------------------------------------------------
   新しいドライバー情報取得
--------------------------------------------------- */
$sql = "
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

$stmt2 = $pdo->prepare($sql);
$stmt2->execute([":id" => $newDriver]);
$driver = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    die("新しいドライバー情報が取得できません。");
}

/* ---------------------------------------------------
   言語名処理
--------------------------------------------------- */
$langMap = $pdo->query("SELECT language_category_id, language_category_name FROM language_category")
               ->fetchAll(PDO::FETCH_KEY_PAIR);

/* 言語名処理（NULL / 空対策済み） */
$langs = [];
foreach (["language_id_1","language_id_2","language_id_3"] as $col) {
    if (!empty($driver[$col]) && $driver[$col] !== "LCAT00") {
        $langs[] = $langMap[$driver[$col]] ?? "―";
    }
}
$langText = $langs ? implode(" / ", $langs) : "なし";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー変更完了 | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#fafafa; }
.container {
    max-width:900px; margin:40px auto; background:#fff;
    padding:30px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.1);
}
h1 { font-size:26px; font-weight:bold; margin-bottom:25px; }

table.done-table {
    width:100%; border-collapse:collapse; margin-bottom:30px;
}
.done-table th, .done-table td {
    border:1px solid #ccc; padding:12px; text-align:center;
}
.done-table th {
    background:#f2f2f2; width:40%;
}

.btn-area { text-align:center; margin-top:35px; }
.btn {
    padding:12px 30px; border-radius:6px;
    font-size:16px; text-decoration:none; margin:0 10px;
}
.btn-back { background:#555; color:#fff; }
.btn-detail { background:#0A84FF; color:#fff; }
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container">

    <h1>ドライバー変更が完了しました</h1>

    <table class="done-table">
        <tr><th>予約番号</th><td><?= htmlspecialchars($resNo) ?></td></tr>
        <tr><th>新ドライバー氏名</th><td><?= htmlspecialchars($driver["employee_name"]) ?></td></tr>
        <tr><th>社員ID</th><td><?= htmlspecialchars($driver["employee_id"]) ?></td></tr>
        <tr><th>対応言語</th><td><?= htmlspecialchars($langText) ?></td></tr>
        <tr><th>メール</th><td><?= htmlspecialchars($driver["driver_email"]) ?></td></tr>
    </table>

    <div class="btn-area">
        <a href="uw113_02_reservation_detail.php?r=<?= urlencode($resNo) ?>" 
           class="btn btn-detail">予約詳細に戻る</a>

        <a href="uw113_01_reservation_list.php"
           class="btn btn-back">配車一覧に戻る</a>
    </div>

</div>

</body>
</html>
