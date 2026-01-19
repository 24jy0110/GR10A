<?php
session_start();

/* =================================================
   0503 → 0504：顧客情報を SESSION に保存
================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reserve']['customer_name']      = $_POST['customer_name'] ?? '';
    $_SESSION['reserve']['customer_name_kana'] = $_POST['customer_name_kana'] ?? '';
    $_SESSION['reserve']['customer_email']     = $_POST['customer_email'] ?? '';
    $_SESSION['reserve']['customer_phone']     = $_POST['customer_phone'] ?? '';
}

/* =================================================
   SESSION チェック（0503 未経由防止）
================================================= */
if (
    empty($_SESSION['reserve']['customer_name']) ||
    empty($_SESSION['reserve']['car_type'])
) {
    header('Location: index.php');
    exit;
}

$res = $_SESSION['reserve'];

/* =================================================
   表示用データ整形
================================================= */
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
$langText = $res['lang1'];
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
  text-align: center;
  margin-bottom: 10px;
}

.note {
  text-align: center;
  font-size: 14px;
  margin-bottom: 30px;
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
  font-size: 14px;
}

.confirm-table th {
  width: 220px;
  text-align: left;
  background: #f5f5f5;
}

.button-row {
  display: flex;
  justify-content: space-between;
  gap: 30px;
  margin-top: 40px;
}

.btn-back,
.btn-next {
  width: 220px;
  padding: 14px 0;
  font-size: 16px;
  cursor: pointer;
}

.btn-back {
  background: #fff;
  border: 1px solid #000;
}

.btn-next {
  background: #000;
  color: #fff;
  border: none;
}
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">

<h2>ご予約内容をご確認ください。</h2>

<p class="note">
ご予約内容は30分間お取り置きしております。<br>
30分以内にお手続きを完了してください。<br>
なお、30分を過ぎますと自動的にキャンセルとなります。
</p>

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
  <th>※サービス開始時間</th>
  <td><?= htmlspecialchars($res['start_time'], ENT_QUOTES) ?></td>
</tr>

<tr>
  <th>利用料金</th>
  <td><?= number_format($res['car_price']) ?> 円／日</td>
</tr>

<tr>
  <th>お客様名前</th>
  <td><?= htmlspecialchars($res['customer_name'], ENT_QUOTES) ?></td>
</tr>

<tr>
  <th>お客様名前（カタカナ）</th>
  <td><?= htmlspecialchars($res['customer_name_kana'], ENT_QUOTES) ?></td>
</tr>

<tr>
  <th>メールアドレス</th>
  <td><?= htmlspecialchars($res['customer_email'], ENT_QUOTES) ?></td>
</tr>

<tr>
  <th>電話番号</th>
  <td><?= htmlspecialchars($res['customer_phone'], ENT_QUOTES) ?></td>
</tr>
</table>

<div class="button-row">
  <button type="button" class="btn-back" onclick="location.href='uw05_03.php'">
    修正する
  </button>

  <form method="post" action="uw05_05.php">
    <button type="submit" class="btn-next">
      予約を確定する
    </button>
  </form>
</div>

</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
