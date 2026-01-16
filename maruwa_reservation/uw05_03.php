<?php
session_start();

/* ===============================
   SESSION チェック
================================ */
if (empty($_SESSION['reserve']['car_type'])) {
    header('Location: index.php');
    exit;
}

/* ===============================
   次へ（顧客情報入力）
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reserve']['customer_name']       = $_POST['customer_name'] ?? '';
    $_SESSION['reserve']['customer_name_kana']  = $_POST['customer_name_kana'] ?? '';
    $_SESSION['reserve']['customer_email']      = $_POST['customer_email'] ?? '';
    $_SESSION['reserve']['customer_phone']      = $_POST['customer_phone'] ?? '';

    header('Location: uw05_04.php');
    exit;
}

$res = $_SESSION['reserve'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>お客様情報入力 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
}

h2 {
  margin-bottom: 10px;
}

.note {
  font-size: 14px;
  margin-bottom: 20px;
}

.confirm-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 30px;
}

.confirm-table th,
.confirm-table td {
  border: 1px solid #000;
  padding: 10px;
  font-size: 14px;
}

.confirm-table th {
  width: 200px;
  text-align: left;
  background: #f8f8f8;
}

.form-box {
  border: 1px solid #000;
  padding: 20px;
}

.form-row {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.form-row label {
  width: 200px;
  font-size: 14px;
}

.form-row input {
  width: 260px;
  padding: 6px 8px;
  font-size: 14px;
}

.button-row {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}

.btn-back,
.btn-next {
  width: 180px;
  padding: 10px 0;
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
30分以内にお手続きを完了してください。
</p>

<table class="confirm-table">
<tr>
  <th>予約日付</th>
  <td>
    <?php
      echo htmlspecialchars(
        $res['start_date'] . ' ' . ($res['start_time'] ?? '') .
        (!empty($res['end_date']) ? ' ～ ' . $res['end_date'] : ''),
        ENT_QUOTES,
        'UTF-8'
      );
    ?>
  </td>
</tr>
<tr>
  <th>乗車人数</th>
  <td><?php echo htmlspecialchars($res['people'], ENT_QUOTES, 'UTF-8'); ?> 名</td>
</tr>
<tr>
  <th>車種</th>
  <td><?php echo htmlspecialchars($res['car_type'], ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<tr>
  <th>乗車場所</th>
  <td>
    <?php
      echo htmlspecialchars(
        $res['pickup_area'] . ' ' . $res['pickup_city'] . ' ' . $res['pickup_detail'],
        ENT_QUOTES,
        'UTF-8'
      );
    ?>
  </td>
</tr>
<tr>
  <th>降車場所</th>
  <td>
    <?php
      echo htmlspecialchars(
        $res['drop_area'] . ' ' . $res['drop_city'] . ' ' . $res['drop_detail'],
        ENT_QUOTES,
        'UTF-8'
      );
    ?>
  </td>
</tr>
<tr>
  <th>対応言語</th>
  <td>
    <?php
      echo htmlspecialchars(
        $res['lang1'] . (!empty($res['lang2']) ? ' / ' . $res['lang2'] : ''),
        ENT_QUOTES,
        'UTF-8'
      );
    ?>
  </td>
</tr>
<tr>
  <th>利用料金</th>
  <td><?php echo number_format($res['car_price']); ?> 円／日</td>
</tr>
</table>

<div class="form-box">
<h3>ご予約に必要な情報をご入力ください。</h3>

<form method="post">
  <div class="form-row">
    <label>お客様名前 *</label>
    <input type="text" name="customer_name" required>
  </div>

  <div class="form-row">
    <label>お客様名前（カタカナ）</label>
    <input type="text" name="customer_name_kana">
  </div>

  <div class="form-row">
    <label>メールアドレス *</label>
    <input type="email" name="customer_email" required>
  </div>

  <div class="form-row">
    <label>電話番号 *</label>
    <input type="text" name="customer_phone" required>
  </div>

  <div class="button-row">
    <button type="button" class="btn-back" onclick="location.href='uw05_02.php'">戻る</button>
    <button type="submit" class="btn-next">予約を確定する</button>
  </div>
</form>
</div>

</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
