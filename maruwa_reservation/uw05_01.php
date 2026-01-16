<?php
session_start();

/*
|--------------------------------------------------------------------------
| STEP0: index.php から日程が POST されたとき
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date']) && !isset($_POST['step'])) {
    $_SESSION['reserve'] = [
        'start_date' => $_POST['start_date'],
        'start_time' => $_POST['start_time'] ?? '',
        'end_date'   => $_POST['end_date']   ?? '',
    ];
} else {
    if (empty($_SESSION['reserve']['start_date'])) {
        header('Location: index.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| STEP1: 本画面（条件入力）が送信されたとき
|--------------------------------------------------------------------------
*/
if (isset($_POST['step']) && $_POST['step'] === 'condition') {

    $_SESSION['reserve']['people']        = $_POST['people'];
    $_SESSION['reserve']['wheelchair']    = $_POST['wheelchair'];

    $_SESSION['reserve']['pickup_area']   = $_POST['pickup_area'];
    $_SESSION['reserve']['pickup_city']   = $_POST['pickup_city'];
    $_SESSION['reserve']['pickup_detail'] = $_POST['pickup_detail'];

    $_SESSION['reserve']['drop_area']     = $_POST['drop_area'];
    $_SESSION['reserve']['drop_city']     = $_POST['drop_city'];
    $_SESSION['reserve']['drop_detail']   = $_POST['drop_detail'];

    $_SESSION['reserve']['lang1']          = $_POST['lang1'];
    $_SESSION['reserve']['lang2']          = $_POST['lang2'];

    header('Location: uw05_02.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| 表示用：予約日付文字列
|--------------------------------------------------------------------------
*/
$res = $_SESSION['reserve'];
$dateText = $res['start_date'];
if (!empty($res['start_time'])) {
    $dateText .= ' ' . $res['start_time'];
}
if (!empty($res['end_date'])) {
    $dateText .= ' ～ ' . $res['end_date'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約条件入力 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
  max-width: 900px;
  margin: 40px auto 60px;
  padding: 0 20px;
}
.reserve-date {
  font-size: 20px;
  font-weight: bold;
  margin: 20px 0 30px;
}
.form-table {
  width: 100%;
  border-collapse: collapse;
}
.form-table th,
.form-table td {
  padding: 12px 8px;
  font-size: 16px;
  vertical-align: middle;
}
.form-table th {
  width: 220px;
  text-align: left;
}
.form-table input,
.form-table select {
  width: 100%;
  max-width: 320px;
  padding: 6px 8px;
}
.place-row {
  display: flex;
  gap: 10px;
}
.button-row {
  margin-top: 40px;
  display: flex;
  justify-content: space-between;
  gap: 20px;
}
.btn-cancel,
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

  <div class="reserve-date">
    予約日付　<?php echo htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8'); ?>
  </div>

  <form method="post">
    <input type="hidden" name="step" value="condition">

    <table class="form-table">

      <tr>
        <th>乗客人数</th>
        <td>
          <input type="number" name="people" min="1" max="9" value="1" required>
        </td>
      </tr>

      <tr>
        <th>車椅子／ベビーカー</th>
        <td>
          <select name="wheelchair" required>
            <option value="なし">なし</option>
            <option value="車椅子">車椅子あり</option>
            <option value="ベビーカー">ベビーカーあり</option>
            <option value="両方">両方あり</option>
          </select>
        </td>
      </tr>

      <tr>
        <th>乗車場所</th>
        <td>
          <input type="text" name="pickup_area" placeholder="都道府県・エリア" required>
          <input type="text" name="pickup_city" placeholder="区／市" required>
          <input type="text" name="pickup_detail" placeholder="詳細住所" required>
        </td>
      </tr>

      <tr>
        <th>降車場所</th>
        <td>
          <input type="text" name="drop_area" placeholder="都道府県・エリア" required>
          <input type="text" name="drop_city" placeholder="区／市" required>
          <input type="text" name="drop_detail" placeholder="詳細住所" required>
        </td>
      </tr>

      <tr>
        <th>対応言語</th>
        <td>
          <div class="place-row">
            <select name="lang1" required>
              <option value="">第一希望</option>
              <option value="日本語">日本語</option>
              <option value="英語">英語</option>
              <option value="中国語">中国語</option>
              <option value="韓国語">韓国語</option>
            </select>

            <select name="lang2">
              <option value="">第二希望（任意）</option>
              <option value="日本語">日本語</option>
              <option value="英語">英語</option>
              <option value="なし">なし</option>
            </select>
          </div>
        </td>
      </tr>

    </table>

    <div class="button-row">
      <button type="button" class="btn-cancel" onclick="location.href='index.php'">取消</button>
      <button type="submit" class="btn-next">次へ</button>
    </div>

  </form>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
