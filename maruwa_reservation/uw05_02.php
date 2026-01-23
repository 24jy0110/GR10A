<?php
session_start();

/**
 * DB 接続
 */
require_once __DIR__ . '/includes/db_connect.php';

/* ===============================
   SESSION チェック
================================ */
if (empty($_SESSION['reserve']['people'])) {
    header('Location: uw05_01.php');
    exit;
}

$people = (int)$_SESSION['reserve']['people'];

/* ===============================
   車種選択 → 次へ
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['reserve']['car_model_code']    = $_POST['car_model_code'] ?? '';
    $_SESSION['reserve']['car_model_name']    = $_POST['car_model_name'] ?? '';
    $_SESSION['reserve']['car_model_use_fee'] = $_POST['car_model_use_fee'] ?? 0;

    header('Location: uw05_03.php');
    exit;
}

/* ===============================
   車種マスタ取得（人数条件）
================================ */
$sql = "
    SELECT
        car_model_code,
        car_model_name,
        car_model_capacity,
        car_model_use_fee
    FROM car_model
    WHERE car_model_capacity >= :people
    ORDER BY car_model_capacity ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':people' => $people]);
$carModels = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * 車種コード → 画像ファイル名対応表
 * ※ DB が NULL のため一時的にここで制御
 */
$CAR_IMAGES = [
    'CMD001' => 'crown.jpg',
    'CMD002' => 'alphard.jpg',
    'CMD003' => 'HIACE.png',
];
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
    margin: 4px 0;
    font-size: 14px;
}

.no-car {
    padding: 30px;
    text-align: center;
    border: 1px solid #000;
    font-size: 16px;
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

<h2>ご利用人数に合った車種をお選びください</h2>

<form method="post">

<?php if (empty($carModels)): ?>

    <div class="no-car">
        申し訳ございません。<br>
        ご指定の乗車人数に対応可能な車両が現在ございません。
    </div>

<?php else: ?>

<table class="car-table">

<?php foreach ($carModels as $row): ?>

<?php
$img = $CAR_IMAGES[$row['car_model_code']] ?? 'noimage.jpg';
?>

<tr>
    <td class="car-radio">
        <input type="radio" name="car_model_code"
               value="<?= htmlspecialchars($row['car_model_code'], ENT_QUOTES) ?>"
               required>

        <input type="hidden" name="car_model_name"
               value="<?= htmlspecialchars($row['car_model_name'], ENT_QUOTES) ?>">

        <input type="hidden" name="car_model_use_fee"
               value="<?= htmlspecialchars($row['car_model_use_fee'], ENT_QUOTES) ?>">
    </td>

    <td class="car-img">
        <img src="imgs/<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="">
    </td>

    <td class="car-info">
        <h3><?= htmlspecialchars($row['car_model_name'], ENT_QUOTES) ?></h3>
        <p>推奨乗車人数：<?= htmlspecialchars($row['car_model_capacity'], ENT_QUOTES) ?> 名</p>
        <p>利用料金：<?= number_format($row['car_model_use_fee']) ?> 円／日</p>
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

</body>
</html>
