<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 没登录踢回根目录 index.php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); 
    exit;
}

// 对应数据库物理名：employee_name, sales_office_code
$userName   = $_SESSION['employee_name'] ?? 'ゲスト'; 
$officeCode = $_SESSION['sales_office_code'] ?? '不明';
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
        <p style="margin:0;">支社コード：<?php echo htmlspecialchars($officeCode, ENT_QUOTES, 'UTF-8'); ?></p>
        <p style="margin:0;"><strong><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?> 様</strong></p>
        <a href="../logout.php" class="logout-link">ログアウト</a>
    </div>
</header>