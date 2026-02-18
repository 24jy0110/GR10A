<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

/* SESSION確認 */
if (!isset($_SESSION["vehicle_add"])) {
    header("Location: uw111_04_vehicle_add.php");
    exit;
}

$data = $_SESSION["vehicle_add"];

/* ===============================
   車種定員取得
=============================== */
$stmt = $pdo->prepare("
    SELECT car_model_capacity
    FROM car_model
    WHERE car_model_code = :cm
");
$stmt->execute([":cm" => $data["car_model_code"]]);
$capacity = $stmt->fetchColumn();

if (!$capacity) {
    die("車種情報取得エラー");
}

/* ===============================
   INSERT 実行
=============================== */
try {

    $stmt = $pdo->prepare("
        INSERT INTO vehicle
        (number_plate, vehicle_capacity, vehicle_state, sales_office_code, car_model_code)
        VALUES
        (:num, :cap, '空車', :so, :cm)
    ");

    $stmt->execute([
        ":num" => $data["number_plate"],
        ":cap" => $capacity,
        ":so"  => $data["sales_office_code"],
        ":cm"  => $data["car_model_code"]
    ]);

    /* 登録成功 → SESSION削除 */
    unset($_SESSION["vehicle_add"]);

} catch (PDOException $e) {
    die("登録エラー：" . $e->getMessage());
}

require_once __DIR__ . "/includes/header.php";
?>

<style>
.container {
    width: 90%;
    max-width: 600px;
    margin: 40px auto;
    text-align: center;
    font-family: "Yu Gothic", sans-serif;
}

.msg {
    font-size: 22px;
    margin-bottom: 25px;
}

.back-btn {
    padding: 12px 20px;
    background: #000;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
}
</style>

<div class="container">
    <div class="msg">車両の登録が完了しました。</div>

    <a class="back-btn" href="uw111_01_vehicle_list.php">車両一覧へ戻る</a>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
