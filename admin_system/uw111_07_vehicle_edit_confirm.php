<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

if (!isset($_SESSION["vehicle_edit"])) {
    header("Location: uw111_01_vehicle_list.php");
    exit;
}

$data = $_SESSION["vehicle_edit"];

/* 车种名称取得 */
$stmt = $pdo->prepare("
SELECT car_model_name 
FROM car_model 
WHERE car_model_code = :cm
");
$stmt->execute([":cm"=>$data["car_model_code"]]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

/* 营业所名称取得 */
$stmt = $pdo->prepare("
SELECT sales_office_name 
FROM sales_office 
WHERE sales_office_code = :so
");
$stmt->execute([":so"=>$data["sales_office_code"]]);
$office = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
.container { max-width:800px; margin:40px auto; font-family:"Yu Gothic"; }
.card {
    background:#fff;
    padding:35px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
}
.row { margin-bottom:15px; }
.label { font-weight:bold; }
.btn-left {
    background:#6c757d;
    color:#fff;
    padding:12px 30px;
    border:none;
    border-radius:8px;
}
.btn-right {
    background:#000;
    color:#fff;
    padding:12px 30px;
    border:none;
    border-radius:8px;
    margin-left:12px;
}
</style>

<div class="container">
<div class="card">

<h2>車両情報修正確認</h2>

<div class="row">
<span class="label">ナンバープレート：</span>
<?= htmlspecialchars($data["number_plate"]) ?>
</div>

<div class="row">
<span class="label">車種：</span>
<?= htmlspecialchars($car["car_model_name"]) ?>
</div>

<div class="row">
<span class="label">所属営業所：</span>
<?= htmlspecialchars($office["sales_office_name"]) ?>
</div>

<form method="post" action="uw111_08_vehicle_edit_done.php">

<br>

<button type="button"
        class="btn-left"
        onclick="history.back();">
修正する
</button>

<button type="submit"
        class="btn-right">
登録確定
</button>

</form>

</div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
