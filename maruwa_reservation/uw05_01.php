<?php
session_start();
require_once __DIR__ . '/includes/office_assign.php';

/* =================================================
   1. 接收 index.php 传来的预约日期
================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date']) && !isset($_POST['step'])) {
    $_SESSION['reserve'] = [
        'start_date' => $_POST['start_date'],
        'start_time' => $_POST['start_time'] ?? '',
        'end_date'   => $_POST['end_date']   ?? '',
    ];
}

/* 如果没有预约日期，返回首页 */
if (empty($_SESSION['reserve']['start_date']) && !isset($_POST['step'])) {
    header('Location: index.php');
    exit;
}

/* =================================================
   2. 提交本页（信息输入完成）
================================================= */
if (isset($_POST['step']) && $_POST['step'] === 'info') {

    $pickupCity = $_POST['pickup_city'] ?? '';

    $_SESSION['reserve']['name']          = $_POST['name'] ?? '';
    $_SESSION['reserve']['name_kana']     = $_POST['name_kana'] ?? '';
    $_SESSION['reserve']['people']        = $_POST['people'] ?? '';
    $_SESSION['reserve']['wheelchair']    = $_POST['wheelchair'] ?? '';
    $_SESSION['reserve']['pickup_city']   = $pickupCity;
    $_SESSION['reserve']['pickup_detail'] = $_POST['pickup_detail'] ?? '';
    $_SESSION['reserve']['drop_detail']   = $_POST['drop_detail'] ?? '';
    $_SESSION['reserve']['lang1']          = $_POST['lang1'] ?? '';
    $_SESSION['reserve']['lang2']          = $_POST['lang2'] ?? '';

    /* ★ 自动分配营业所 */
    $_SESSION['reserve']['sales_office_code'] = assignOfficeByCity($pickupCity);

    header('Location: uw05_02.php');
    exit;
}

/* =================================================
   3. 预约日期显示文本
================================================= */
$res = $_SESSION['reserve'];
$rangeText = $res['start_date'];
if (!empty($res['start_time'])) $rangeText .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $rangeText .= ' ～ ' . $res['end_date'];

/* =================================================
   4. エリアマスタ（JS 用）
================================================= */
require_once __DIR__ . '/includes/area_master.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約情報入力 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container{max-width:960px;margin:40px auto;padding:0 20px}
h1{font-size:26px;margin-bottom:10px}
.reserve-date{font-size:18px;font-weight:bold;margin:20px 0}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;font-size:16px;text-align:left}
th{width:220px}
input,select{width:100%;max-width:360px;padding:6px;font-size:16px}
.button-row{margin-top:40px;display:flex;gap:20px}
.btn{flex:1;padding:14px;font-size:18px;cursor:pointer}
.btn-next{background:#000;color:#fff;border:none}
.btn-cancel{background:#fff;border:1px solid #000}
.area-box button,
.city-box button{
  margin:4px;padding:6px 12px;cursor:pointer
}
.area-box, .city-box{margin:8px 0}
.note{font-size:13px;color:#555}
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">
<h1>予約フォームを全て入力してください</h1>

<div class="reserve-date">
  予約日付：<?php echo htmlspecialchars($rangeText, ENT_QUOTES, 'UTF-8'); ?>
</div>

<form method="post">
<input type="hidden" name="step" value="info">
<input type="hidden" name="pickup_city" id="pickup_city">

<table>
<tr>
<th>お客様名前</th>
<td><input type="text" name="name" required></td>
</tr>

<tr>
<th>お客様名前（カタカナ）</th>
<td><input type="text" name="name_kana" required></td>
</tr>

<tr>
<th>乗客人数</th>
<td><input type="number" name="people" min="1" max="9" value="1" required></td>
</tr>

<tr>
<th>車椅子／ベビーカー</th>
<td>
<select name="wheelchair">
  <option value="なし">なし</option>
  <option value="wheelchair">車椅子あり</option>
  <option value="babycar">ベビーカーあり</option>
  <option value="both">両方あり</option>
</select>
</td>
</tr>

<tr>
<th>乗車エリア</th>
<td>
  <div class="area-box" id="prefBox"></div>
  <div class="city-box" id="cityBox"></div>
  <div class="note">※地図上の非灰色エリアのみ選択可能</div>
</td>
</tr>

<tr>
<th>乗車場所（詳細住所）</th>
<td><input type="text" name="pickup_detail" required></td>
</tr>

<tr>
<th>降車場所（詳細住所）</th>
<td><input type="text" name="drop_detail" required></td>
</tr>

<tr>
<th>対応言語</th>
<td>
<select name="lang1" required>
  <option value="">第一希望</option>
  <option value="日本語">日本語</option>
  <option value="英語">英語</option>
  <option value="中国語">中国語</option>
  <option value="韓国語">韓国語</option>
</select>
<select name="lang2">
  <option value="">第二希望（任意）</option>
  <option value="なし">なし</option>
  <option value="日本語">日本語</option>
  <option value="英語">英語</option>
</select>
</td>
</tr>
</table>

<div class="button-row">
<button type="button" class="btn btn-cancel" onclick="location.href='index.php'">取消</button>
<button type="submit" class="btn btn-next">次へ</button>
</div>

</form>
</div>

<?php include("includes/footer.php"); ?>

<script>
const AREA_MASTER = <?php echo json_encode($AREA_MASTER, JSON_UNESCAPED_UNICODE); ?>;
const prefBox = document.getElementById('prefBox');
const cityBox = document.getElementById('cityBox');
const cityInput = document.getElementById('pickup_city');

Object.keys(AREA_MASTER).forEach(pref => {
  const b = document.createElement('button');
  b.type = "button";
  b.textContent = pref;
  b.onclick = () => showCities(pref);
  prefBox.appendChild(b);
});

function showCities(pref){
  cityBox.innerHTML = '';
  Object.values(AREA_MASTER[pref]).flat().forEach(city=>{
    const b=document.createElement('button');
    b.type="button";
    b.textContent=city;
    b.onclick=()=>{
      cityInput.value=city;
      alert("選択：" + city);
    };
    cityBox.appendChild(b);
  });
}
</script>

</body>
</html>
