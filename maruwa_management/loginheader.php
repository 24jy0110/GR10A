<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$employee_name     = $_SESSION['employee_name'] ?? "";
$sales_office_name = $_SESSION['sales_office_name'] ?? "";
$department_name   = $_SESSION['department_name'] ?? "";
$logged_in         = isset($_SESSION['employee_id']);
?>

<link rel="stylesheet" href="assets/css/loginheader.css">

<header class="login-header">

    <!-- 左上ロゴ -->
    <div class="header-left">
        <h1 class="company-name">丸和交通株式会社</h1>
        <p class="company-eng">maruwa transportation co., LTD.</p>
        <p class="company-msg">旅をつなぐ、笑顔を運ぶ。</p>
    </div>

    <!-- 右上ユーザー情報 -->
    <?php if ($logged_in): ?>
    <div class="header-right">

        <div class="user-info">
            <span><?= htmlspecialchars($sales_office_name) ?></span>
            <span><?= htmlspecialchars($department_name) ?></span>
            <span><?= htmlspecialchars($employee_name) ?> 様</span>
        </div>

        <form action="logout.php" method="POST">
            <button class="logout-btn">ログアウト</button>
        </form>

    </div>
    <?php endif; ?>

</header>
