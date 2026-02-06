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



<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 没登录踢回根目录 index.php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit;
}

$employee_name     = $_SESSION['employee_name'] ?? "";
$sales_office_name = $_SESSION['sales_office_name'] ?? "";
$department_name   = $_SESSION['department_name'] ?? "";
$logged_in         = isset($_SESSION['employee_id']);
?>
<style>
/* ---------------------------------------------------- */
/* Logo/Header 样式 */
/* ---------------------------------------------------- */
body {
    font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
    margin: 0;
    background-color: #f8f8f8;
}

.header {
    background-color: #222;
    color: white;
    padding: 16px 20px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    height: 80px; 
}

.header a.home-link {
    text-decoration: none;
    color: inherit;
}
.header a.home-link:hover { opacity: 0.85; }

.header h1 {
    margin: 0 0 4px 0;
    font-size: 28px;
    line-height: 1.2;
}

.user-info-area {
    text-align: right;
    padding-right: 20px;
    font-size: 14px;
}

.logout-link {
    font-size: 13px;
    color: #aaa;
    text-decoration: none;
    display: block;
    margin-top: 4px;
}

.logout-link:hover {
    color: #fff;
    text-decoration: underline;
}
</style>

<header class="header">
    <a href="../program/index.php" class="home-link">
        <div>
            <h1>丸和交通株式会社</h1>
            <p style="margin:0; font-size:12px; color:#ccc;">旅をつなぐ、笑顔を運ぶ。</p>
        </div>
    </a>
    <div class="user-info-area">
    <p style="margin:0;"><span><?= htmlspecialchars($sales_office_name) ?></span></p>
    <p style="margin:0;"><span><?= htmlspecialchars($department_name) ?></span></p>
    <p style="margin:0;"><span><?= htmlspecialchars($employee_name) ?> 様</span></p>

        <a href="../logout.php" class="logout-link">ログアウト</a>
    </div>
</header>