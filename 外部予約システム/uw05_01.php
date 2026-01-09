<?php
session_start();

// 1回目：index から日程が POST されたとき
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date']) && !isset($_POST['step'])) {
    $_SESSION['reserve'] = [
        'start_date' => $_POST['start_date'],
        'start_time' => $_POST['start_time'] ?? '',
        'end_date'   => $_POST['end_date']   ?? '',
    ];
} else {
    // 日程情報がなければトップに戻す
    if (empty($_SESSION['reserve']['start_date']) && (!isset($_POST['step']) || $_POST['step'] !== 'info')) {
        header('Location: index.php');
        exit;
    }
}

// 情報入力フォームが送信されたとき
if (isset($_POST['step']) && $_POST['step'] === 'info') {
  $_SESSION['reserve']['name']           = $_POST['name'] ?? '';
  $_SESSION['reserve']['name_kana']      = $_POST['name_kana'] ?? '';
  $_SESSION['reserve']['people']         = $_POST['people'] ?? '';
  $_SESSION['reserve']['wheelchair']     = $_POST['wheelchair'] ?? '';
  $_SESSION['reserve']['pickup_area']    = $_POST['pickup_area'] ?? '';
  $_SESSION['reserve']['pickup_city']    = $_POST['pickup_city'] ?? '';
  $_SESSION['reserve']['drop_area']      = $_POST['drop_area'] ?? '';
  $_SESSION['reserve']['drop_city']      = $_POST['drop_city'] ?? '';

  // ★ 新增：详细地址
  $_SESSION['reserve']['pickup_detail']  = $_POST['pickup_detail'] ?? '';
  $_SESSION['reserve']['drop_detail']    = $_POST['drop_detail'] ?? '';

  $_SESSION['reserve']['lang1']          = $_POST['lang1'] ?? '';
  $_SESSION['reserve']['lang2']          = $_POST['lang2'] ?? '';

  header('Location: uw05_02.php');
  exit;
}

$res = $_SESSION['reserve'];
$range_text = $res['start_date'];
if (!empty($res['start_time'])) $range_text .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $range_text .= ' ～ ' . $res['end_date'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>uw05_01 予約入力画面</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
  .container {
    max-width: 960px;
    margin: 40px auto 60px;
    padding: 0 20px;
  }

  h1 {
    font-size: 26px;
    margin-bottom: 4px;
  }

  .sub-copy {
    margin-bottom: 24px;
    color: #555;
  }

  .reserve-date {
    font-size: 20px; /* だいたい h3〜h4 くらい */
    margin: 20px 0 30px;
    font-weight: bold;
  }

  .form-table {
    width: 100%;
    border-collapse: collapse;
  }

  .form-table th,
  .form-table td {
    padding: 12px 8px;
    vertical-align: middle;
    font-size: 16px;
  }

  .form-table th {
    width: 220px;
    text-align: left;
    font-weight: bold;
  }

  .form-table input[type="text"],
  .form-table input[type="number"],
  .form-table select {
    width: 100%;
    max-width: 320px;
    padding: 6px 8px;
    font-size: 16px;
    box-sizing: border-box;
  }

  .form-table input[readonly] {
    background-color: #f8f8f8;
    cursor: default;
  }

  .place-display {
    display: flex;
    gap: 8px;
    align-items: center;
    max-width: 480px;
  }

  .btn-select-place {
    padding: 6px 12px;
    font-size: 14px;
    cursor: pointer;
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
    padding: 12px 0;
    font-size: 18px;
    border-radius: 4px;
    border: 1px solid #000;
    cursor: pointer;
  }

  .btn-cancel {
    background-color: #fff;
    color: #000;
  }

  .btn-next {
    background-color: #000;
    color: #fff;
  }

  /* 乗車／降車場所選択モーダル */
  .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
  }
  .modal-content {
      background-color: #fff;
      margin: 12% auto;
      padding: 20px 24px;
      border-radius: 8px;
      width: 90%;
      max-width: 480px;
  }
  .modal-content h3 {
      margin-top: 0;
      margin-bottom: 16px;
  }
  .modal-content label {
      display: block;
      margin-top: 10px;
      margin-bottom: 4px;
      font-size: 14px;
  }
  .modal-content select {
      width: 100%;
      padding: 6px 8px;
      font-size: 14px;
      box-sizing: border-box;
  }
  .modal-buttons {
      margin-top: 18px;
      text-align: right;
  }
  .modal-buttons button {
      padding: 6px 14px;
      margin-left: 8px;
  }
</style>
</head>
<body>

<?php include("includes/header.php"); ?>

<div class="container">
  <h1>予約フォームを全て入力してください</h1>
  <div class="reserve-date">
    予約日付　<?php echo htmlspecialchars($range_text, ENT_QUOTES, 'UTF-8'); ?>
  </div>

  <form action="uw05_01.php" method="post">
    <input type="hidden" name="step" value="info">

    <table class="form-table">
      <tr>
        <th>お客様名前</th>
        <td>
          <input type="text" name="name" placeholder="山田 太郎" required>
        </td>
      </tr>
      <tr>
        <th>お客様名前（カタカナ）</th>
        <td>
          <input type="text" name="name_kana" placeholder="ヤマダ タロウ" required>
        </td>
      </tr>
      <tr>
        <th>乗客人数</th>
        <td>
          <!-- 上下矢印付きの number -->
          <input type="number" name="people" min="1" max="9" value="1" required>
        </td>
      </tr>
      <tr>
        <th>車椅子／ベビーカー</th>
        <td>
          <select name="wheelchair" required>
            <option value="なし">なし</option>
            <option value="wheelchair">車椅子あり</option>
            <option value="babycar">ベビーカーあり</option>
            <option value="both">両方あり</option>
          </select>
        </td>
      </tr>
      <tr>
        <th>乗車場所</th>
        <td>
          <div class="place-display">
            <input type="text" id="pickupDisplay" readonly placeholder="クリックして選択">
            <button type="button" class="btn-select-place" data-target="pickup">選択</button>
          </div>
          <input type="hidden" name="pickup_area" id="pickupArea">
          <input type="hidden" name="pickup_city" id="pickupCity">
        </td>
      </tr>
      <tr>
  <th>乗車場所（詳細住所）</th>
  <td>
    <input type="text"
           name="pickup_detail"
           placeholder="例）西池袋1-1-1 ○○ホテル前／111室など"  required>
  </td>
</tr>
      <tr>
        <th>降車場所</th>
        <td>
          <div class="place-display">
            <input type="text" id="dropDisplay" readonly placeholder="クリックして選択">
            <button type="button" class="btn-select-place" data-target="drop">選択</button>
          </div>
          <input type="hidden" name="drop_area" id="dropArea">
          <input type="hidden" name="drop_city" id="dropCity">
        </td>
      </tr>
      <tr>
  <th>降車場所（詳細住所）</th>
  <td>
    <input type="text"
           name="drop_detail"
           placeholder="例）成田空港 第2ターミナル 北口付近 など" required>
  </td>
</tr>
      <tr>
        <th>対応言語</th>
        <td>
          <div style="display:flex; gap:10px; max-width:480px;">
            <select name="lang1" required>
              <option value="">第一希望言語</option>
              <option value="日本語">日本語</option>
              <option value="英語">英語</option>
              <option value="中国語">中国語</option>
              <option value="韓国語">韓国語</option>
              <option value="フランス語">フランス語</option>
              <!-- 必要に応じて追加 -->
            </select>
            <select name="lang2">
              <option value="">第二希望言語（任意）</option>
              <option value="なし">特になし</option>
              <option value="日本語">日本語</option>
              <option value="英語">英語</option>
            </select>
            
          </div>
          <P>※第二希望言語は英語や日本語しか選択できません</P>
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

<!-- 乗車／降車場所選択モーダル（共通で使う） -->
<div id="placeModal" class="modal">
  <div class="modal-content">
    <h3>エリアを選択してください</h3>

    <label for="placeArea">エリア</label>
    <select id="placeArea">
      <option value="">選択してください</option>
      <option value="東京都市区部">東京都市区部</option>
      <option value="神奈川県">神奈川県</option>
      <option value="埼玉県">埼玉県</option>
      <option value="千葉県">千葉県</option>
    </select>

    <label for="placeCity">区／市</label>
    <select id="placeCity">
      <option value="">先にエリアを選択してください</option>
    </select>

    <div class="modal-buttons">
      <button type="button" id="placeCancel">キャンセル</button>
      <button type="button" id="placeConfirm">決定</button>
    </div>
  </div>
</div>

<script>
// ===== 乗車／降車場所用 データ =====
const citiesData = {
  "東京都市区部": ["豊島区", "新宿区", "渋谷区", "千代田区", "中央区"],
  "神奈川県":    ["横浜市", "川崎市", "相模原市"],
  "埼玉県":      ["さいたま市", "川口市", "所沢市"],
  "千葉県":      ["千葉市", "船橋市", "市川市"]
  // ここは必要に応じて本番用の一覧に差し替えてOK
};

const modal      = document.getElementById('placeModal');
const areaSelect = document.getElementById('placeArea');
const citySelect = document.getElementById('placeCity');
const btnCancel  = document.getElementById('placeCancel');
const btnConfirm = document.getElementById('placeConfirm');

let currentTarget = null; // "pickup" or "drop"

// 「選択」ボタンを押したとき
document.querySelectorAll('.btn-select-place').forEach(btn => {
  btn.addEventListener('click', () => {
    currentTarget = btn.dataset.target; // pickup / drop
    areaSelect.value = "";
    citySelect.innerHTML = '<option value="">先にエリアを選択してください</option>';
    modal.style.display = 'block';
  });
});

// モーダル外クリックで閉じる
window.addEventListener('click', e => {
  if (e.target === modal) modal.style.display = 'none';
});

// キャンセル
btnCancel.addEventListener('click', () => {
  modal.style.display = 'none';
});

// エリア選択で市区の選択肢を切り替え
areaSelect.addEventListener('change', () => {
  const area = areaSelect.value;
  citySelect.innerHTML = '<option value="">区／市を選択してください</option>';
  if (!area || !citiesData[area]) return;

  citiesData[area].forEach(city => {
    const opt = document.createElement('option');
    opt.value = city;
    opt.textContent = city;
    citySelect.appendChild(opt);
  });
});

// 決定ボタン
btnConfirm.addEventListener('click', () => {
  const area = areaSelect.value;
  const city = citySelect.value;

  if (!area || !city) {
    alert("エリアと区／市を選択してください。");
    return;
  }

  if (currentTarget === 'pickup') {
    document.getElementById('pickupArea').value   = area;
    document.getElementById('pickupCity').value   = city;
    document.getElementById('pickupDisplay').value = `${area} ${city}`;
  } else if (currentTarget === 'drop') {
    document.getElementById('dropArea').value   = area;
    document.getElementById('dropCity').value   = city;
    document.getElementById('dropDisplay').value = `${area} ${city}`;
  }

  modal.style.display = 'none';
});
</script>

</body>
</html>
