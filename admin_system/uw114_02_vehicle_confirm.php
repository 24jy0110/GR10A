<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

if (!isset($_SESSION["vehicle_add"])) {
    header("Location: uw114_01_vehicle_add.php");
    exit;
}

$data = $_SESSION["vehicle_add"];

/* 车种名称取得 */
$sql = "SELECT car_model_name, car_model_capacity
        FROM car_model
        WHERE car_model_code = :cm";
$stmt = $pdo->prepare($sql);
$stmt->execute([":cm" => $data["car_model_code"]]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

/* 营业所名称取得 */
$sql = "SELECT sales_office_name
        FROM sales_office
        WHERE sales_office_code = :so";
$stmt = $pdo->prepare($sql);
$stmt->execute([":so" => $data["sales_office_code"]]);
$office = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div style="max-width:800px;margin:40px auto;">

    <h2>車両登録確認</h2>

    <div style="border:1px solid #ccc;padding:20px;margin-top:20px;">

        <p><strong>ナンバープレート：</strong>
            <?= htmlspecialchars($data["number_plate"]) ?></p>

        <p><strong>車種：</strong>
            <?= htmlspecialchars($car["car_model_name"]) ?></p>

        <p><strong>定員：</strong>
            <?= htmlspecialchars($car["car_model_capacity"]) ?>名</p>

        <p><strong>所属営業所：</strong>
            <?= htmlspecialchars($office["sales_office_name"]) ?></p>

    </div>

    <form method="post" action="uw114_03_vehicle_add_done.php">

        <div style="margin-top:30px;display:flex;gap:20px;">

            <button type="button"
                onclick="location.href='uw114_01_vehicle_add.php'"
                style="padding:12px 30px;border:1px solid #000;background:#fff;">
                修正する
            </button>

            <button type="submit"
                style="padding:12px 30px;background:#000;color:#fff;">
                登録確定
            </button>

        </div>

    </form>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>