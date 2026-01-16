<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>料金案内（日貸し） | 丸和交通株式会社</title>

    <!-- 共通样式 -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/common.css">

    <!-- UW04 专用样式 -->
    <style>
        .price-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
            text-align: center;
        }

        .price-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .price-note {
            font-size: 12px;
            margin-bottom: 30px;
        }

        .price-note .red {
            color: red;
            font-weight: bold;
        }

        .price-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 20px;
            align-items: center;
        }

        .price-header {
            font-weight: bold;
            font-size: 14px;
            padding-bottom: 10px;
        }

        .car-box {
            text-align: center;
        }

        .car-box img {
            width: 200px;
            height: auto;
            display: block;
            margin: 0 auto 8px;
        }

        .car-name {
            font-weight: bold;
            font-size: 14px;
        }

        .price-cell {
            font-size: 16px;
        }

        .back-row {
            margin: 40px 0 20px;
            text-align: center;
        }

        .btn-back {
            padding: 10px 24px;
            border: 1px solid #333;
            text-decoration: none;
            color: #333;
            border-radius: 6px;
        }

        .btn-back:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="price-wrapper">
    <div class="price-title">日貸し（1日単位）の参考料金設定案</div>
    <div class="price-note">
        ※いずれも《車両＋専属ドライバー＋燃料＋保険込み》／
        高速代・駐車料・宿泊費は<span class="red">別途</span>
    </div>

    <!-- 表头 -->
    <div class="price-grid">
        <div class="price-header">車種</div>
        <div class="price-header">1日基本料金（税込）<br>日本語対応</div>
        <div class="price-header">1日基本料金（税込）<br>外国語対応</div>

        <!-- Crown -->
        <div class="car-box">
            <img src="imgs/crown.jpg" alt="トヨタ Crown">
            <div class="car-name">トヨタ Crown</div>
        </div>
        <div class="price-cell">65,000円</div>
        <div class="price-cell">73,000円</div>

        <!-- Alphard -->
        <div class="car-box">
            <img src="imgs/alphard.jpg" alt="トヨタ Alphard">
            <div class="car-name">トヨタ Alphard</div>
        </div>
        <div class="price-cell">70,000円</div>
        <div class="price-cell">78,000円</div>

        <!-- Hiace -->
        <div class="car-box">
            <img src="imgs/HIACE.png" alt="トヨタ Hiace">
            <div class="car-name">トヨタ Hiace</div>
        </div>
        <div class="price-cell">75,000円</div>
        <div class="price-cell">83,000円</div>
    </div>

    <div class="back-row">
        <a href="index.php" class="btn-back">ホームページへ</a>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
