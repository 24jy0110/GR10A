<?php
session_start();
require_once __DIR__ . '/includes/area_master.php';

/*
|--------------------------------------------------------------------------
| STEP0: 从 index.php 接收日期
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
| STEP1: 本页面提交
|--------------------------------------------------------------------------
*/
if (isset($_POST['step']) && $_POST['step'] === 'condition') {

    $_SESSION['reserve']['people']        = $_POST['people'];
    $_SESSION['reserve']['wheelchair']    = $_POST['wheelchair'];

    $_SESSION['reserve']['pickup_pref']   = $_POST['pickup_pref'];
    $_SESSION['reserve']['pickup_city']   = $_POST['pickup_city'];
    $_SESSION['reserve']['pickup_detail'] = $_POST['pickup_detail'];

    $_SESSION['reserve']['drop_pref']     = $_POST['drop_pref'];
    $_SESSION['reserve']['drop_city']     = $_POST['drop_city'];
    $_SESSION['reserve']['drop_detail']   = $_POST['drop_detail'];

    $_SESSION['reserve']['lang1']          = $_POST['lang1'];
    $_SESSION['reserve']['lang2']          = $_POST['lang2'];

    // === 営業所自動判定（乗車場所 기준）===
    $officeCode = '';
    foreach ($AREA_MASTER[$_POST['pickup_pref']] as $ofc => $cities) {
        if (in_array($_POST['pickup_city'], $cities, true)) {
            $officeCode = $ofc;
            break;
        }
    }
    $_SESSION['reserve']['sales_office_code'] = $officeCode;

    header('Location: uw05_02.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| 表示用日期
|--------------------------------------------------------------------------
*/
$res = $_SESSION['reserve'];
$dateText = $res['start_date'];
if (!empty($res['start_time'])) $dateText .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $dateText .= ' ～ ' . $res['end_date'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約条件入力 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">
<style>
.container{max-width:960px;margin:40px auto;padding:0 20px}
.reserve-date{font-size:20px;font-weight:bold;margin-bottom:30px}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;font-size:16px;vertical-align:top}
th{text-align:left;width:220px}
select,input{width:100%;max-width:360px;padding:6px}
.button-row{margin-top:40px;display:flex;gap:20px}
.btn-next{background:#000;color:#fff;padding:14px;font-size:18px;flex:1}
.btn-cancel{padding:14px;font-size:18px;flex:1}
</style>
</head>

<body>
<?php include 'includes/header.php'; ?>

<div class="container">
  <div class="reserve-date">
    予約日付　<?= htmlspecialchars($dateText, ENT_QUOTES) ?>
  </div>

  <form method="post">
    <input type="hidden" name="step" value="condition">

    <table>
      <tr>
        <th>乗客人数</th>
        <td><input type="number" name="people" min="1" max="9" required></td>
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

      <!-- 乗車 -->
      <tr>
        <th>乗車場所</th>
        <td>
          <select name="pickup_pref" id="pickup_pref" required>
            <option value="">都道府県</option>
            <?php foreach ($AREA_MASTER as $pref => $_): ?>
              <option value="<?= $pref ?>"><?= $pref ?></option>
            <?php endforeach; ?>
          </select>

          <select name="pickup_city" id="pickup_city" required>
            <option value="">市区町村</option>
          </select>

          <input type="text" name="pickup_detail" placeholder="詳細住所" required>
        </td>
      </tr>

      <!-- 降車 -->
      <tr>
        <th>降車場所</th>
        <td>
          <select name="drop_pref" id="drop_pref" required>
            <option value="">都道府県</option>
            <?php foreach ($AREA_MASTER as $pref => $_): ?>
              <option value="<?= $pref ?>"><?= $pref ?></option>
            <?php endforeach; ?>
          </select>

          <select name="drop_city" id="drop_city" required>
            <option value="">市区町村</option>
          </select>

          <input type="text" name="drop_detail" placeholder="詳細住所" required>
        </td>
      </tr>

      <tr>
        <th>対応言語</th>
        <td>
          <select name="lang1" required>
            <option value="">第一希望</option>
            <option>日本語</option>
            <option>英語</option>
            <option>中国語</option>
            <option>韓国語</option>
          </select>
          <select name="lang2">
            <option value="">第二希望（任意）</option>
            <option>日本語</option>
            <option>英語</option>
            <option value="なし">なし</option>
          </select>
        </td>
      </tr>
    </table>

    <div class="button-row">
      <button type="button" class="btn-cancel" onclick="location.href='index.php'">取消</button>
      <button type="submit" class="btn-next">次へ</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>

<script>
const AREA_MASTER = <?= json_encode($AREA_MASTER, JSON_UNESCAPED_UNICODE) ?>;

function bind(prefSel, citySel){
  prefSel.onchange = () => {
    citySel.innerHTML = '<option value="">市区町村</option>';
    if (!AREA_MASTER[prefSel.value]) return;
    Object.values(AREA_MASTER[prefSel.value]).flat().forEach(c=>{
      const o=document.createElement('option');
      o.value=o.textContent=c;
      citySel.appendChild(o);
    });
  };
}
bind(pickup_pref, pickup_city);
bind(drop_pref, drop_city);
</script>

</body>
</html>
