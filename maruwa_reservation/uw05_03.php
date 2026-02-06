<?php
session_start();

/* ================================================
   SESSION チェック（0502 から来ていない場合）
================================================ */
$required = [
    'start_date',
    'end_date',
    'ride_count',
    'car_model_code',
    'car_model_name',
    'car_model_use_fee',
    'pickup_pref',
    'pickup_city',
    'pickup_detail',
    'drop_pref',
    'drop_city',
    'drop_detail',
    'lang_pref_1'
];

foreach ($required as $key) {
    if (empty($_SESSION['reserve'][$key])) {
        header("Location: uw05_01.php");
        exit;
    }
}

$res = $_SESSION['reserve'];

/* ================================================
   POST：0503 → 0504（顧客情報を保存）
================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['reserve']['customer_name']       = $_POST['customer_name'] ?? '';
    $_SESSION['reserve']['customer_name_kana']  = $_POST['customer_name_kana'] ?? '';
    $_SESSION['reserve']['customer_email']      = $_POST['customer_email'] ?? '';
    $_SESSION['reserve']['customer_phone']      = $_POST['customer_phone'] ?? '';

    header('Location: uw05_04.php');
    exit;
}

/* ================================================
   表示用：予約日付
================================================ */
$dateText = $res['start_date'];
if (!empty($res['start_time'])) $dateText .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $dateText .= ' ～ ' . $res['end_date'];

/* ================================================
   表示用：乗車／降車場所
================================================ */
$pickupText = trim("{$res['pickup_pref']} {$res['pickup_city']} {$res['pickup_detail']}");
$dropText   = trim("{$res['drop_pref']} {$res['drop_city']} {$res['drop_detail']}");

/* ================================================
   言語名称（LCAT → 名称）
================================================ */
$langMap = [
    'LCAT02' => '英語',
    'LCAT03' => '中国語',
    'LCAT04' => '韓国語',
    'LCAT05' => 'ドイツ語',
    'LCAT06' => 'スペイン語',
    'LCAT07' => 'フランス語',
];

$lang1 = $langMap[$res['lang_pref_1']] ?? '日本語';
$lang2 = !empty($res['lang_pref_2']) ? ($langMap[$res['lang_pref_2']] ?? '') : '';

$langText = $lang1 . ($lang2 ? " / {$lang2}" : '');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>お客様情報入力</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
    max-width: 850px;
    margin: 50px auto;
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    text-align: center;
}

h2 {
    margin-bottom: 25px;
    font-size: 26px;
}

.confirm-table,
.form-table {
    margin: 0 auto 30px;
    width: 85%;
    border-collapse: collapse;
}

.confirm-table th,
.confirm-table td,
.form-table th,
.form-table td {
    border: 1px solid #ccc;
    padding: 12px;
    font-size: 16px;
}

.confirm-table th {
    background: #f5f5f5;
    width: 220px;
}

.form-table th {
    text-align: left;
    width: 220px;
}

.required {
    color: red;
    font-weight: bold;
}

input {
    width: 90%;
    padding: 10px;
    font-size: 15px;
    border-radius: 6px;
    border: 1px solid #aaa;
}

.note {
    color: #d00;
    margin-bottom: 20px;
    font-weight: bold;
}

.btn-next {
    background: #000;
    color: #fff;
    padding: 14px 32px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}

.btn-next:hover {
    background: #444;
}
</style>
</head>

<body>
<?php include("includes/header.php"); ?>

<div class="container">

<h2>予約内容確認</h2>

<table class="confirm-table">
<tr><th>予約日付</th><td><?= htmlspecialchars($dateText) ?></td></tr>
<tr><th>乗車人数</th><td><?= htmlspecialchars($res['ride_count']) ?> 名</td></tr>
<tr><th>車種</th><td><?= htmlspecialchars($res['car_model_name']) ?></td></tr>
<tr><th>乗車場所</th><td><?= htmlspecialchars($pickupText) ?></td></tr>
<tr><th>降車場所</th><td><?= htmlspecialchars($dropText) ?></td></tr>
<tr><th>対応言語</th><td><?= htmlspecialchars($langText) ?></td></tr>
<tr><th>利用料金</th><td><?= number_format($res['car_model_use_fee']) ?> 円／日</td></tr>
</table>

<h3>お客様情報入力</h3>

<p class="note">※印の項目はすべてご入力ください。</p>

<form method="post">
<table class="form-table">

<tr>
  <th><span class="required">※</span> お名前</th>
  <td><input name="customer_name" required></td>
</tr>

<tr>
  <th>お名前（カタカナ）</th>
  <td><input name="customer_name_kana"></td>
</tr>

<tr>
  <th><span class="required">※</span> メールアドレス</th>
  <td><input type="email" name="customer_email" required></td>
</tr>

<tr>
  <th><span class="required">※</span> 電話番号</th>
  <td><input name="customer_phone" required></td>
</tr>

</table>

<button type="submit" class="btn-next">次へ</button>

</form>
</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
