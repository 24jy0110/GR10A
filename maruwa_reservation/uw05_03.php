<?php
session_start();

/* ===============================
   POST：客户信息保存 → 0504
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reserve']['customer_name']       = $_POST['customer_name'] ?? '';
    $_SESSION['reserve']['customer_name_kana']  = $_POST['customer_name_kana'] ?? '';
    $_SESSION['reserve']['customer_email']      = $_POST['customer_email'] ?? '';
    $_SESSION['reserve']['customer_phone']      = $_POST['customer_phone'] ?? '';

    header('Location: uw05_04.php');
    exit;
}

/* ===============================
   SESSION チェック
================================ */
if (
    empty($_SESSION['reserve']['car_model_name']) ||
    empty($_SESSION['reserve']['car_model_use_fee'])
) {
    header('Location: index.php');
    exit;
}

$res = $_SESSION['reserve'];

/* ===============================
   表示用整形
================================ */
$dateText = $res['start_date'];
if (!empty($res['start_time'])) $dateText .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $dateText .= ' ～ ' . $res['end_date'];

$pickupText = "{$res['pickup_area']} {$res['pickup_city']} {$res['pickup_detail']}";
$dropText   = "{$res['drop_area']} {$res['drop_city']} {$res['drop_detail']}";

$langText = $res['lang1'];
if (!empty($res['lang2'])) $langText .= ' / ' . $res['lang2'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約内容確認・個人情報入力</title>
<link rel="stylesheet" href="./assets/app.css">
</head>

<body>
<?php include("includes/header.php"); ?>

<div class="container">

<h2>予約内容確認</h2>

<table class="confirm-table">
<tr><th>予約日付</th><td><?= htmlspecialchars($dateText) ?></td></tr>
<tr><th>乗車人数</th><td><?= $res['people'] ?> 名</td></tr>
<tr><th>車種</th><td><?= htmlspecialchars($res['car_model_name']) ?></td></tr>
<tr><th>乗車場所</th><td><?= htmlspecialchars($pickupText) ?></td></tr>
<tr><th>降車場所</th><td><?= htmlspecialchars($dropText) ?></td></tr>
<tr><th>対応言語</th><td><?= htmlspecialchars($langText) ?></td></tr>
<tr><th>利用料金</th><td><?= number_format($res['car_model_use_fee']) ?> 円／日</td></tr>
</table>

<h3>お客様情報入力</h3>

<form method="post">
<table class="form-table">
<tr><th>お名前 *</th><td><input name="customer_name" required></td></tr>
<tr><th>カタカナ</th><td><input name="customer_name_kana"></td></tr>
<tr><th>メール *</th><td><input type="email" name="customer_email" required></td></tr>
<tr><th>電話番号 *</th><td><input name="customer_phone" required></td></tr>
</table>

<div class="button-row">
  <button type="submit" class="btn-next">次へ</button>
</div>
</form>

</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
