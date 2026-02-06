<?php
require_once __DIR__ . "/includes/check_login.php";
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
