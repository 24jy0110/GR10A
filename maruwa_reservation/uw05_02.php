<?php
session_start();

/* ===============================
   SESSION チェック
================================ */
if (empty($_SESSION['reserve'])) {
    header('Location: index.php');
    exit;
}

/* ===============================
   次へ（車種選択）
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['reserve']['car_type'] = $_POST['car_type'] ?? '';
    $_SESSION['reserve']['price']    = $_POST['price'] ?? '';

    header('Location: uw05_03.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>車種選択 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
}

h2 {
  margin-bottom: 20px;
}

.car-table {
  width: 100%;
  border-collapse: collapse;
}

.car-table td {
  border: 1px solid #000;
  padding: 16px;
  vertical-align: middle;
}

.car-radio {
  width: 40px;
  text-align: center;
}

.car-img {
  width: 160px;
  text-align: center;
}

.car-img img {
  max-width: 140px;
  height: auto;
}

.car-info h3 {
  margin: 0 0 6px;
}

.car-info p {
  margin: 2px 0;
  font-size: 14px;
}

.note {
  font-size: 12px;
  margin-top: 10px;
}

.button-row {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}

.btn-back,
.btn-next {
  width: 160px;
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
<h2>お気に入りの車種を選択してください</h2>

<form method="post">

<!-- ★ 実際に送信される hidden（1セットのみ） -->
<input type="hidden" name="car_type" id="car_type">
<input type="hidden" name="price" id="price">

<table class="car-table">

<tr>
  <td class="car-radio">
    <input type="radio" name="car"
           value="CROWN"
           data-price="65000"
           required>
  </td>
  <td class="car-img">
    <img src="imgs/crown.jpg" alt="トヨタ クラウン">
    <div>トヨタ クラウン<br>(Toyota Crown)</div>
  </td>
  <td class="car-info">
    <h3>乗客定員：3名</h3>
    <p>スーツケース：最大2個</p>
    <p>特徴：「移動するリビング」に変える上質セダン</p>
    <p>料金：65,000円（73,000円）／日</p>
  </td>
</tr>

<tr>
  <td class="car-radio">
    <input type="radio" name="car"
           value="ALPHARD"
           data-price="70000">
  </td>
  <td class="car-img">
    <img src="imgs/alphard.jpg" alt="トヨタ アルファード">
    <div>トヨタ アルファード<br>(Toyota Alphard)</div>
  </td>
  <td class="car-info">
    <h3>乗客定員：5名</h3>
    <p>スーツケース：最大4個</p>
    <p>特徴：「旅のプライベートスイート」ラグジュアリーMPV</p>
    <p>料金：70,000円（78,000円）／日</p>
  </td>
</tr>

<tr>
  <td class="car-radio">
    <input type="radio" name="car"
           value="HIACE"
           data-price="75000">
  </td>
  <td class="car-img">
    <img src="imgs/HIACE.png" alt="トヨタ ハイエース">
    <div>トヨタ ハイエース<br>(Toyota Hiace)</div>
  </td>
  <td class="car-info">
    <h3>乗客定員：9名</h3>
    <p>スーツケース：最大10個</p>
    <p>特徴：アクティブな仲間旅を支える「ゆとりワゴン」</p>
    <p>料金：75,000円（83,000円）／日</p>
  </td>
</tr>

</table>

<p class="note">
※スーツケースは1個あたり100L以下を目安とします。<br>
※料金は税込・ドライバー付・燃料込です。
</p>

<div class="button-row">
  <button type="button" class="btn-back" onclick="location.href='uw05_01.php'">戻る</button>
  <button type="submit" class="btn-next">次へ</button>
</div>

</form>
</div>

<?php include("includes/footer.php"); ?>

<script>
/* ===============================
   車種選択 → hidden に反映
================================ */
document.querySelectorAll('input[name="car"]').forEach(radio => {
  radio.addEventListener('change', function () {
    document.getElementById('car_type').value = this.value;
    document.getElementById('price').value    = this.dataset.price;
  });
});
</script>

</body>
</html>
