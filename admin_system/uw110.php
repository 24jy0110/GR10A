<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

// 登录用户信息
$employee_name = $_SESSION['employee_name'] ?? '';
$sales_office_name = $_SESSION['sales_office_name'] ?? '';
$department_name = "配車管理課";  // 课名固定
?>

<style>
    .main-container {
        width: 100%;
        max-width: 800px;
        margin: 40px auto;
        text-align: center;
        font-family: "Yu Gothic", sans-serif;
    }

    .main-title {
        font-size: 22px;
        margin-bottom: 30px;
        line-height: 1.6em;
    }

    .menu-btn {
        width: 260px;
        padding: 18px 0;
        margin: 20px auto;
        background-color: #000;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 18px;
        cursor: pointer;
        transition: 0.2s;
        display: block;
        text-decoration: none;
    }

    .menu-btn:hover {
        opacity: .8;
    }
</style>

<div class="main-container">
    <div class="main-title">
        ようこそ、<?= htmlspecialchars($sales_office_name) ?>　<?= $department_name ?><br>
        <?= htmlspecialchars($employee_name) ?> 様
    </div>

    <!-- 車両ステータス -->
    <a class="menu-btn" href="uw111_01_vehicle_list.php">車両ステータス</a>

    <!-- ドライバー管理（之后再开发） -->
    <a class="menu-btn" href="uw112_driver_list.php">ドライバー管理</a>

    <!-- 配車予定一覧（之后再开发） -->
    <a class="menu-btn" href="uw113_dispatch_list.php">配車予定一覧</a>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
