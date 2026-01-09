<?php
session_start();
// 检查是否登录，没登录踢回首页
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../index.php");
    exit;
}
include("../includes/header.php"); 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ドライバーTOP</title>
    </head>
<body>
<style>
    body {
        font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
        text-align: center;
    }

    /* 欢迎语区域 */
    .welcome-section {
        margin-top: 80px; /* 距离Header的距离 */
        margin-bottom: 60px;
        font-size: 32px;
        line-height: 1.6;
        font-weight: normal;
    }

    .user-name {
        font-size: 36px;
        font-weight: 500;
    }

    .menu-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 30px;
    }


    .btn-dashboard {
        background-color: black;
        color: white;
        width: 400px;       
        padding: 25px 0;    
        font-size: 24px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 5px;
        transition: opacity 0.3s;
        display: flex;        
        justify-content: center;
        align-items: center;
    }

    .btn-dashboard:hover {
        opacity: 0.8;
    }
</style>
    <div class="welcome-section">
        ようこそ、<?php echo $_SESSION['office']; ?>　<?php echo $_SESSION['department']; ?><br>
        <span class="user-name"><?php echo $_SESSION['user_name']; ?>様</span>
    </div>

    <div class="menu-container">
        <a href="schedule.php" class="btn-dashboard">
            出勤スケジュール
        </a>
        <a href="rides.php" class="btn-dashboard">
            予定乗務
        </a>
    </div>

</body>
</html>