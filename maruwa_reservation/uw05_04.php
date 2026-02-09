<?php
session_start();

/* ============================================================
   SESSION チェック（0503 未経由防止）
============================================================ */
$required = [
    'start_date',
    'end_date',
    'ride_count',
    'pickup_pref',
    'pickup_city',
    'pickup_detail',
    'drop_pref',
    'drop_city',
    'drop_detail',
    'car_model_code',
    'car_model_name',
    'car_model_use_fee'
];

foreach ($required as $key) {
    if (empty($_SESSION['reserve'][$key])) {
        header("Location: uw05_01.php");
        exit;
    }
}

$res = $_SESSION['reserve'];

/* ============================================================
   DB 接続（言語ID → 名称に変換）
============================================================ */
require_once __DIR__ . '/includes/db_connect.php';

$langMap = [];
$stmt = $pdo->query("SELECT language_category_id, language_category_name FROM language_category");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $langMap[$row['language_category_id']] = $row['language_category_name'];
}

/* 言語表示 */
$lang1 = $langMap[$res['lang_pref_1']] ?? '';
$lang2 = !empty($res['lang_pref_2']) ? ($langMap[$res['lang_pref_2']] ?? '') : '';
$langText = $lang1 . ($lang2 ? " / $lang2" : "");

/* 日付表示 */
$dateText = $res['start_date'];
if ($res['start_time']) $dateText .= " " . $res['start_time'];
$dateText .= " ～ " . $res['end_date'];

/* 乗車 / 降車 */
$pickupText = trim("{$res['pickup_pref']} {$res['pickup_city']} {$res['pickup_detail']}");
$dropText   = trim("{$res['drop_pref']} {$res['drop_city']} {$res['drop_detail']}");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>最終確認 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">
<style>
.container {max-width:900px; margin:40px auto; padding:0 20px;}
.confirm-table {width:100%; border-collapse:collapse;}
.confirm-table th, .confirm-table td {
    border:1px solid #000; padding:12px; font-size:15px;
}
.confirm-table th {background:#f5f5f5; width:220px;}

.button-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 40px;
}

.button-row form {
    margin: 0;
}

.btn-next {
    background:#000;
    color:#fff;
    padding:14px 40px;
    border:none;
}

.btn-back {
    background:#fff;
    border:1px solid #000;
    padding:14px 30px;
}

</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">
<h2>ご予約内容の最終確認</h2>

<table class="confirm-table">
<tr><th>予約日付</th><td><?= htmlspecialchars($dateText) ?></td></tr>
<tr><th>乗車人数</th><td><?= htmlspecialchars($res['ride_count']) ?> 名</td></tr>
<tr><th>車種</th><td><?= htmlspecialchars($res['car_model_name']) ?></td></tr>
<tr><th>乗車場所</th><td><?= htmlspecialchars($pickupText) ?></td></tr>
<tr><th>降車場所</th><td><?= htmlspecialchars($dropText) ?></td></tr>
<tr><th>対応言語</th><td><?= htmlspecialchars($langText) ?></td></tr>
<tr><th>利用料金</th><td><?= number_format($res['car_model_use_fee']) ?> 円／日</td></tr>

<tr><th>お名前</th><td><?= htmlspecialchars($res['customer_name']) ?></td></tr>
<tr><th>カタカナ</th><td><?= htmlspecialchars($res['customer_name_kana']) ?></td></tr>
<tr><th>メール</th><td><?= htmlspecialchars($res['customer_email']) ?></td></tr>
<tr><th>電話番号</th><td><?= htmlspecialchars($res['customer_phone']) ?></td></tr>
</table>
<div class="button-row">
    <button
        type="button"
        class="btn-back"
        onclick="location.href='uw05_03.php'">
        修正する
    </button>

    <form action="uw05_05.php" method="post">
        <button type="submit" class="btn-next">
            予約を確定する
        </button>
    </form>
</div>


</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
