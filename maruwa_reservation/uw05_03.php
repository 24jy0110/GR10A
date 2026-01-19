<?php
session_start();

/* ===============================
   セッションチェック
================================ */
if (empty($_SESSION['reserve'])) {
    header('Location: index.php');
    exit;
}

$res = $_SESSION['reserve'];

/* ===============================
   表示用データ整形
================================ */
// 予約日付
$dateText = $res['start_date'];
if (!empty($res['start_time'])) {
    $dateText .= ' ' . $res['start_time'];
}
if (!empty($res['end_date'])) {
    $dateText .= ' ～ ' . $res['end_date'];
}

// 乗車・降車場所
$pickupText =
    ($res['pickup_area'] ?? '') . ' ' .
    ($res['pickup_city'] ?? '') . ' ' .
    ($res['pickup_detail'] ?? '');

$dropText =
    ($res['drop_area'] ?? '') . ' ' .
    ($res['drop_city'] ?? '') . ' ' .
    ($res['drop_detail'] ?? '');

// 対応言語
$langText = $res['lang1'] ?? '';
if (!empty($res['lang2'])) {
    $langText .= ' / ' . $res['lang2'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約内容確認 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
    max-width: 900px;
    margin: 40px auto 60px;
    padding: 0 20px;
}
h2 {
    margin-bottom: 10px;
}
.notice {
    font-size: 14px;
    margin-bottom: 20px;
}
.confirm-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
}
.confirm-table th,
.confirm-table td {
    border: 1px solid #000;
    padding: 10px;
    font-size: 15px;
}
.confirm-table th {
    width: 220px;
    text-align: left;
    background: #f5f5f5;
}
.form-box {
    border: 1px solid #000;
    padding: 20px;
    margin-bottom: 40px;
}
.form-box h3 {
    margin-top: 0;
}
.form-box table {
    width: 100%;
}
.form-box th {
    text-align: left;
    width: 220px;
    padding: 10px 0;
}
.form-box input {
    width: 100%;
    max-width: 360px;
    padding: 6px 8px;
}
.button-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}
.btn-back,
.btn-next {
    flex: 1;
    padding: 14px 0;
    font-size: 18px;
    cursor: pointer;
}
.btn-next {
    background: #000;
    color: #fff;
}
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">

<h2>ご予約内容をご確認ください。</h2>
<p class="notice">
ご予約内容は30分間お取り置きしております。<br>
30分以内にお手続きを完了してください。
</p>

<!-- 上：予約内容確認 -->
<table class="confirm-table">
<tr>
    <th>予約日付</th>
    <td><?= htmlspecialchars($dateText, ENT_QUOTES) ?></td>
</tr>
<tr>
    <th>乗車人数</th>
    <td><?= htmlspecialchars($res['people'], ENT_QUOTES) ?> 名</td>
</tr>
<tr>
    <th>車種</th>
    <td><?= htmlspecialchars($res['car_type'], ENT_QUOTES) ?></td>
</tr>
<tr>
    <th>乗車場所</th>
    <td><?= htmlspecialchars($pickupText, ENT_QUOTES) ?></td>
</tr>
<tr>
    <th>降車場所</th>
    <td><?= htmlspecialchars($dropText, ENT_QUOTES) ?></td>
</tr>
<tr>
    <th>対応言語</th>
    <td><?= htmlspecialchars($langText, ENT_QUOTES) ?></td>
</tr>
<tr>
    <th>利用料金</th>
    <td><?= htmlspecialchars($res['price'], ENT_QUOTES) ?> 円／日</td>
</tr>
</table>

<!-- 下：お客様情報入力 -->
<form method="post" action="uw05_04.php">

<div class="form-box">
<h3>ご予約に必要な情報をご入力ください。</h3>

<table>
<tr>
    <th>お客様名前 *</th>
    <td><input type="text" name="customer_name" required></td>
</tr>
<tr>
    <th>お客様名前（カタカナ）</th>
    <td><input type="text" name="customer_name_kana"></td>
</tr>
<tr>
    <th>メールアドレス *</th>
    <td><input type="email" name="customer_email" required></td>
</tr>
<tr>
    <th>電話番号 *</th>
    <td><input type="tel" name="customer_phone" required></td>
</tr>
</table>
</div>

<div class="button-row">
    <button type="button" class="btn-back" onclick="history.back()">戻る</button>
    <button type="submit" class="btn-next">予約を確定する</button>
</div>

</form>

</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
