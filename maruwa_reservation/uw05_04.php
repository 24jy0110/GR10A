<?php
session_start();

/* ===============================
   SESSION チェック
================================ */
$required = [
  'customer_name',
  'customer_email',
  'customer_phone',
  'car_model_name',
  'car_model_use_fee'
];

foreach ($required as $key) {
    if (empty($_SESSION['reserve'][$key])) {
        header('Location: index.php');
        exit;
    }
}

$res = $_SESSION['reserve'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>最終確認</title>
<link rel="stylesheet" href="./assets/app.css">
</head>

<body>
<?php include("includes/header.php"); ?>

<div class="container">

<h2>最終確認</h2>

<table class="confirm-table">
<tr><th>車種</th><td><?= htmlspecialchars($res['car_model_name']) ?></td></tr>
<tr><th>料金</th><td><?= number_format($res['car_model_use_fee']) ?> 円／日</td></tr>
<tr><th>お名前</th><td><?= htmlspecialchars($res['customer_name']) ?></td></tr>
<tr><th>メール</th><td><?= htmlspecialchars($res['customer_email']) ?></td></tr>
<tr><th>電話</th><td><?= htmlspecialchars($res['customer_phone']) ?></td></tr>
</table>

<div class="button-row">
<form method="post" action="uw05_05.php">
<button class="btn-next">予約を確定する</button>
</form>
</div>

</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
