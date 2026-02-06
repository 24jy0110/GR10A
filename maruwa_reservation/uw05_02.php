<?php
session_start();

/* =========================================================
   0501 から正常に遷移してきたかチェック
========================================================= */
$required = ['start_date', 'end_date', 'ride_count'];
foreach ($required as $k) {
    if (empty($_SESSION['reserve'][$k])) {
        header("Location: uw05_01.php");
        exit;
    }
}

$rideCount = (int)$_SESSION['reserve']['ride_count'];

/* =========================================================
   利用日数（end_date – start_date）を計算 → SESSION 保存
========================================================= */
$start = new DateTime($_SESSION['reserve']['start_date']);
$end   = new DateTime($_SESSION['reserve']['end_date']);
$diffDays = $start->diff($end)->days;

// 最低 1 日保証
$_SESSION['reserve']['day_count'] = max(1, $diffDays);

/* =========================================================
   DB 接続（学校サーバー）
========================================================= */
$dsn = 'mysql:host=10.64.144.5;dbname=24jy0141;charset=utf8';
$username = '24jy0141';
$password = '24jy0141';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("DB接続失敗: " . $e->getMessage());
}

/* =========================================================
   人数に対応する車種を取得
========================================================= */
$sql = "
    SELECT
        car_model_code,
        car_model_name,
        car_model_capacity,
        car_model_use_fee,
        photo_file
    FROM car_model
    WHERE car_model_capacity >= :ride_count
    ORDER BY car_model_capacity ASC
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':ride_count', $rideCount, PDO::PARAM_INT);
$stmt->execute();
$carList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   POST：車種選択 → DB から名称・料金を再取得 → SESSION → 0503
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = $_POST['car_model_code'] ?? '';

    if ($code === '') {
        header("Location: uw05_02.php");
        exit;
    }

    // hidden を信用しない → DBから再取得
    $stmt = $pdo->prepare("
        SELECT car_model_name, car_model_use_fee 
        FROM car_model 
        WHERE car_model_code = :code
    ");
    $stmt->execute([':code' => $code]);
    $model = $stmt->fetch(PDO::FETCH_ASSOC);

    $_SESSION['reserve']['car_model_code']    = $code;
    $_SESSION['reserve']['car_model_name']    = $model['car_model_name'];
    $_SESSION['reserve']['car_model_use_fee'] = $model['car_model_use_fee'];

    header("Location: uw05_03.php");
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
.container { max-width:900px; margin:40px auto; padding:0 20px; }
.car-table { width:100%; border-collapse:collapse; }
.car-table td { border:1px solid #000; padding:16px; vertical-align:top; }
.car-img img { max-width:150px; height:auto; }
.no-car { padding:30px; font-size:18px; text-align:center; border:1px solid #000; }
.button-row { display:flex; justify-content:space-between; margin-top:30px; }
.btn-back, .btn-next { width:180px; padding:12px; font-size:18px; cursor:pointer; }
.btn-next { background:#000; color:#fff; border:none; }
.btn-back { background:#fff; border:1px solid #000; }

.car-row:hover { background:#fafafa; cursor:pointer; }
.car-row.selected { background:#f0f0f0; }
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">

<h2>ご利用人数に対応した車種をお選びください</h2>

<!-- 利用日数・単価情報の確認（デバッグ用なら表示して OK）
<p>利用日数：<?= $_SESSION['reserve']['day_count'] ?> 日</p>
-->

<form method="post">

<?php if (empty($carList)): ?>

<div class="no-car">
    ご指定の人数に対応できる車両が現在ございません。
</div>

<?php else: ?>

<table class="car-table">

<?php foreach ($carList as $car): ?>
<?php $photo = $car['photo_file'] ?: 'noimage.jpg'; ?>

<tr class="car-row">
    <td width="60">
        <input type="radio" name="car_model_code"
               value="<?= htmlspecialchars($car['car_model_code']) ?>" required>
    </td>

    <td class="car-img">
        <img src="imgs/<?= htmlspecialchars($photo) ?>" alt="車両画像">
    </td>

    <td>
        <h3><?= htmlspecialchars($car['car_model_name']) ?></h3>
        <p>推奨乗車人数：<?= htmlspecialchars($car['car_model_capacity']) ?> 名</p>
        <p>利用料金：<?= number_format($car['car_model_use_fee']) ?> 円／日</p>
    </td>
</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

<div class="button-row">
    <button type="button" class="btn-back" onclick="location.href='uw05_01.php'">戻る</button>
    <button type="submit" class="btn-next">次へ</button>
</div>

</form>

</div>

<?php include("includes/footer.php"); ?>

<script>
// 行クリックでラジオ選択＋ハイライト
document.querySelectorAll(".car-row").forEach(row => {
    row.addEventListener("click", () => {

        const radio = row.querySelector("input[type='radio']");
        radio.checked = true;

        document.querySelectorAll(".car-row")
            .forEach(r => r.classList.remove("selected"));

        row.classList.add("selected");
    });
});
</script>

</body>
</html>
