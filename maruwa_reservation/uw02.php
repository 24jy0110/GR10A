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

<style>
    .container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 0 20px;
}

.container h2 {
    font-size: 2em;
    text-align: center;
    border-bottom: 3px solid #ccc;
    padding-bottom: 10px;
}

.note {
    text-align: center;
    font-size: 0.9em;
    color: #666;
    margin-bottom: 30px;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    margin-bottom: 40px;
    padding: 10px;
    background-color: #f0f0f0;
    border-radius: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin: 5px 15px;
}

.color-block {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 8px;
    border: 1px solid #33333320;
}

.green { background-color: #38c172; }
.red { background-color: #e3342f; }
.magenta { background-color: #f66d9b; }
.teal { background-color: #4dc0b5; }
.cyan { background-color: #6cb2eb; }
.pink { background-color: #cc6592; }
.gray { background-color: #888; }

.map-section {
    margin-bottom: 50px;
    text-align: center;
}

.map-section h3 {
    font-size: 1.5em;
    color: #333;
    border-left: 5px solid #0056b3;
    padding-left: 10px;
    margin: 20px auto 15px 0;
    text-align: left;
}

.map-img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}


.back-row {
      text-align: center;
      padding: 30px 0;
    }

    .btn-back {
      display: inline-block;
      padding: 10px 24px;
      border: 1px solid #000;
      color: #000;
      text-decoration: none;
      border-radius: 4px;
    }

/* 响应式 */
@media (max-width: 768px) {
    .container h2 {
        font-size: 1.5em;
    }
    .map-section h3 {
        font-size: 1.3em;
    }
    .legend {
        justify-content: flex-start;
    }
}

    </style>
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
    <a href="index.php" class="btn-back">ホームページへ</a>
  </div>
</div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
