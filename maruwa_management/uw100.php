<?php
session_start();

/* ---------- ログインチェック ---------- */
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit;
}

/* ---------- セッション情報取得 ---------- */
$employee_name     = $_SESSION['employee_name'];
$sales_office_name = $_SESSION['sales_office_name'];
$department_name   = $_SESSION['department_name'];
$job_code          = $_SESSION['job_code'];

/* ---------- 職種チェック（受付：01） ---------- */
if ($job_code !== "01") {
    header("Location: index.php?error=access");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>受付トップ | 内部管理システム</title>

    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/loginheader.css">

    <style>
        /* UW100専用レイアウト */
        .uw100-wrapper {
            margin-top: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .uw100-inner {
            text-align: center;
        }

        .uw100-welcome {
            font-size: 24px;
            font-weight: 500;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .uw100-btn {
            width: 220px;
            height: 55px;
            font-size: 20px;
            border-radius: 4px;
            border: none;
            background: #000;
            color: white;
            cursor: pointer;
        }
        .uw100-btn:hover {
            opacity: 0.85;
        }
    </style>

</head>

<body>

<!-- ログイン後ヘッダー -->
<?php include "loginheader.php"; ?>

<div class="uw100-wrapper">
    <div class="uw100-inner">

        <h2 class="uw100-welcome">
            ようこそ、
            <?= htmlspecialchars($sales_office_name) ?>
            <?= htmlspecialchars($department_name) ?>
            <?= htmlspecialchars($employee_name) ?> 様
        </h2>

        <button class="uw100-btn" onclick="location.href='uw101_01.php'">
            予約一覧
        </button>

    </div>
</div>

</body>
</html>
