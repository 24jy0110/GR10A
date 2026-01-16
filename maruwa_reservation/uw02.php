<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 标题支持多语言 -->
    <title data-key="page_title_uw02">対応エリア | 丸和交通株式会社</title>

    <!-- 共通样式 -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/common.css">

    <!-- UW02 专用样式 -->
    <link rel="stylesheet" href="assets/css/uw02.css">
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">
    <!-- 页面主标题 -->
    <h2 data-key="area_title">サービス提供エリア</h2>

    <!-- 说明文字 -->
    <p class="note">
        <span data-key="area_note_1">
            ※配車はお客様の乗車地点に応じて自動で決定されます。詳しくは各社の対応エリアマップをご参照ください。
        </span><br>
        <span data-key="area_note_2">
            ※乗車地と降車地がこのエリアに含まれる場合ご利用いただけます。
        </span>
    </p>

    <!-- 图例 -->
    <div class="legend">
        <h3 data-key="legend_title" style="margin:0 12px 0 0;">凡例</h3>

        <div class="legend-item">
            <span class="color-block green"></span>
            <span data-key="legend_head_office">亀沢本社</span>
        </div>

        <div class="legend-item">
            <span class="color-block red"></span>
            <span data-key="legend_toyoshima">豊島支社</span>
        </div>

        <div class="legend-item">
            <span class="color-block magenta"></span>
            <span data-key="legend_momoi">桃井支社</span>
        </div>

        <div class="legend-item">
            <span class="color-block teal"></span>
            <span data-key="legend_haneda">本羽田支社</span>
        </div>

        <div class="legend-item">
            <span class="color-block cyan"></span>
            <span data-key="legend_fujimicho">富士見町支社</span>
        </div>

        <div class="legend-item">
            <span class="color-block pink"></span>
            <span data-key="legend_naka">中区支社</span>
        </div>

        <div class="legend-item">
            <span class="color-block gray"></span>
            <span data-key="legend_out">サービスエリア外</span>
        </div>
    </div>

    <!-- 東京都 -->
    <section class="map-section">
        <h3 data-key="area_tokyo">東京都市区部</h3>
        <img src="imgs/tokyo.png"
             alt="東京都市区部のサービスエリアマップ"
             class="map-img"
             width="900"
             height="800">
    </section>

    <!-- 神奈川県 -->
    <section class="map-section">
        <h3 data-key="area_kanagawa">神奈川県</h3>
        <img src="imgs/kanagawa.png"
             alt="神奈川県のサービスエリアマップ"
             class="map-img"
             width="900"
             height="800">
    </section>

    <!-- 埼玉県 -->
    <section class="map-section">
        <h3 data-key="area_saitama">埼玉県</h3>
        <img src="imgs/saitama.png"
             alt="埼玉県のサービスエリアマップ"
             class="map-img"
             width="900"
             height="800">
    </section>

    <!-- 千葉県 -->
    <section class="map-section">
        <h3 data-key="area_chiba">千葉県</h3>
        <img src="imgs/chiba.png"
             alt="千葉県のサービスエリアマップ"
             class="map-img"
             width="900"
             height="800">
    </section>

    <!-- 返回首页 -->
    <div class="back-row">
        <a href="index.php"
           class="btn-back"
           data-key="btn_homepage"
           aria-label="トップページへ戻る">
           ホームページへ
        </a>
    </div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
