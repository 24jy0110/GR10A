<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

if (!isset($_SESSION["vehicle_edit"])) {
    header("Location: uw111_01_vehicle_list.php");
    exit;
}

$data = $_SESSION["vehicle_edit"];

try {

    /* UPDATE 実行 */
    $stmt = $pdo->prepare("
        UPDATE vehicle
        SET number_plate = :new_plate,
            car_model_code = :cm,
            sales_office_code = :so
        WHERE number_plate = :old_plate
    ");

    $stmt->execute([
        ":new_plate" => $data["number_plate"],
        ":cm"        => $data["car_model_code"],
        ":so"        => $data["sales_office_code"],
        ":old_plate" => $data["old_plate"]
    ]);

    /* セッション削除（二重送信防止） */
    unset($_SESSION["vehicle_edit"]);

} catch (PDOException $e) {
    die("更新エラー：" . $e->getMessage());
}

require_once __DIR__ . "/includes/header.php";
?>

<style>
.container {
    max-width:600px;
    margin:80px auto;
    text-align:center;
    font-family:"Yu Gothic", sans-serif;
}

.message {
    font-size:22px;
    margin-bottom:30px;
}

.btn {
    padding:14px 30px;
    background:#000;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    display:inline-block;
}
</style>

<div class="container">

    <div class="message">
        車両情報の修正が完了しました。
    </div>

    <a href="uw111_01_vehicle_list.php" class="btn">
        車両一覧へ戻る
    </a>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
