<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

if (!isset($_SESSION["vehicle_state_edit"])) {
    header("Location: uw111_01_vehicle_list.php");
    exit;
}

$data = $_SESSION["vehicle_state_edit"];

/* 数据库更新 */
$sql = "UPDATE vehicle
        SET vehicle_state = :st
        WHERE number_plate = :num";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":st"  => $data["new_state"],
    ":num" => $data["number_plate"]
]);

unset($_SESSION["vehicle_state_edit"]);
?>

<style>
    .container {
        max-width: 600px;
        margin: 40px auto;
        text-align: center;
        font-family: "Yu Gothic";
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
    <div class="msg">車両状態の変更が完了しました。</div>

    <a class="back-btn"
        href="uw111_02_vehicle_detail.php?number_plate=<?= urlencode($data["number_plate"]) ?>">
        詳細へ戻る
    </a>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>