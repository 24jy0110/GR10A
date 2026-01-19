<?php
// 当前页面文件名（例如 uw03.php）
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- 共通 Header -->
<link rel="stylesheet" href="assets/css/header.css">
<link rel="stylesheet" href="assets/css/common.css">
<script src="assets/js/i18n.js" defer></script>

<div class="header">
    <a href="index.php" class="home-link">
        <h1>丸和交通株式会社</h1>
        <p data-key="company_name_en">maruwa transportation co.,LTD.</p>
        <p data-key="slogan">旅をつなぐ、笑顔を運ぶ。</p>
    </a>

    <div class="language-selector">
        <select id="language-switcher">
            <option value="ja">日本語</option>
            <option value="en">English</option>
            <option value="ko">한국어</option>
        </select>
    </div>
</div>

<div class="headergroup">
    <table>
        <tr>
            <th>
                <a href="uw01.php"
                   class="<?= $currentPage === 'uw01.php' ? 'active' : '' ?>"
                   data-key="nav_usage">利用方法</a>
            </th>
            <th>
                <a href="uw02.php"
                   class="<?= $currentPage === 'uw02.php' ? 'active' : '' ?>"
                   data-key="nav_area">対応エリア</a>
            </th>
            <th>
                <a href="uw03.php"
                   class="<?= $currentPage === 'uw03.php' ? 'active' : '' ?>"
                   data-key="nav_car">車種紹介</a>
            </th>
            <th>
                <a href="uw04.php"
                   class="<?= $currentPage === 'uw04.php' ? 'active' : '' ?>"
                   data-key="nav_price">料金検索</a>
            </th>
        </tr>
    </table>
</div>
