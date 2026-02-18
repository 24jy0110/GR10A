<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/header.php";

if (!isset($_SESSION["vehicle_state_edit"])) {
    header("Location: uw111_01_vehicle_list.php");
    exit;
}

$data = $_SESSION["vehicle_state_edit"];
?>

<style>
    .container {
        max-width: 800px;
        margin: 40px auto;
        font-family: "Yu Gothic";
    }

    .card {
        padding: 30px;
        background: #fff;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border-radius: 10px;
    }

    .btn-left {
        background: #666;
        color: #fff;
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
    }

    .btn-right {
        background: #000;
        color: #fff;
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        margin-left: 15px;
    }
</style>

<div class="container">
    <div class="card">

        <h2>車両状態変更確認</h2>

        <p><strong>ナンバープレート：</strong>
            <?= htmlspecialchars($data["number_plate"]) ?></p>

        <p><strong>変更前：</strong>
            <?= htmlspecialchars($data["old_state"]) ?></p>

        <p><strong>変更後：</strong>
            <?= htmlspecialchars($data["new_state"]) ?></p>

        <form method="post" action="uw113_03_vehicle_state_done.php">

            <div style="margin-top:30px;">
                <button type="button"
                    class="btn-left"
                    onclick="history.back();">
                    修正する
                </button>

                <button type="submit"
                    class="btn-right">
                    確定
                </button>
            </div>

        </form>

    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>