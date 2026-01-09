<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}
include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>配車管理TOP</title>
</head>

<body>
    <style>
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
            text-align: center;
        }

        .welcome-section {
            margin-top: 80px;
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
        <a href="vehicle_status.php" class="btn-dashboard">
            車両ステータス
        </a>
        <a href="driver_list.php" class="btn-dashboard">
            ドライバー管理
        </a>
        <a href="dispatch_schedule.php" class="btn-dashboard">
            配車予定一覧
        </a>

    </div>

</body>

</html>