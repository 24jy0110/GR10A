<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>料金案内（日貸し） | 丸和交通株式会社</title>
    <link rel="stylesheet" href="./assets/app.css">

    <style>
        /* 可以之后挪到 app.css 里 */

        .price-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0 8px;
        }

        .price-note {
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .price-note .red {
            color: red;
            font-weight: bold;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background-color: #fff;
        }

        .price-table th,
        .price-table td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        .price-table th {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
            font-size: 14px;
        }

        .price-table td {
            font-size: 14px;
        }

        .car-col {
            width: 260px;
        }

        .car-info {
            text-align: center;
        }

        .car-info img {
            max-width: 260px;
            height: auto;
            display: block;
            margin: 4px auto;
        }

        .car-name {
            font-weight: bold;
            margin-top: 4px;
        }

        .price-col,
        .dist-col,
        .extra-col {
            text-align: center;
            white-space: nowrap;
        }

        /* 下部规则块 */

        .rule-block {
            text-align: center;
            font-size: 13px;
            line-height: 1.6;
            margin: 20px 0;
        }

        .rule-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .rule-table {
            margin: 0 auto;
            font-size: 13px;
            border-collapse: collapse;
        }

        .rule-table th,
        .rule-table td {
            padding: 2px 8px;
        }

        .rule-table th {
            text-align: center;
            border-bottom: 1px solid #000;
        }

        .rule-table td {
            text-align: left;
        }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <div class="container">
        <h2 class="price-title">日貸し（1日単位）の参考料金設定案</h2>
        <p class="price-note">
            ※いずれも《車両＋専属ドライバー＋燃料＋保険込み》／
            高速代・駐車料・宿泊実費は<span class="red">別途</span>
        </p>

        <!-- 料金テーブル -->
        <table class="price-table">
            <thead>
                <tr>
                    <th class="car-col">車種</th>
                    <th class="price-col">
                        1日基本料金（税込）<br>
                        日本語対応／外国語対応
                    </th>
                    <th class="dist-col">含まれる走行距離</th>
                    <th class="extra-col">追加距離料金</th>
                </tr>
            </thead>
            <tbody>
                <!-- トヨタ CROWN-->
                <tr>
                    <td class="car-col">
                        <div class="car-info">
                            <img src="imgs/crown.jpg" alt="トヨタ Crown">
                            <div class="car-name">トヨタ Crown</div>
                        </div>
                    </td>
                    <td class="price-col">65,000円／73,000円</td>
                    <td class="dist-col">240 km まで</td>
                    <td class="extra-col">300円／km</td>
                </tr>

                <!-- トヨタ Alphard -->
                <tr>
                    <td class="car-col">
                        <div class="car-info">
                            <img src="imgs/alphard.jpg" alt="トヨタ Alphard">
                            <div class="car-name">トヨタ Alphard</div>
                        </div>
                    </td>
                    <td class="price-col">70,000円／78,000円</td>
                    <td class="dist-col">240 km まで</td>
                    <td class="extra-col">350円／km</td>
                </tr>

                <!-- トヨタ Hiace -->
                <tr>
                    <td class="car-col">
                        <div class="car-info">
                            <img src="imgs/HIACE.png" alt="トヨタ Hiace">
                            <div class="car-name">トヨタ Hiace</div>
                        </div>
                    </td>
                    <td class="price-col">75,000円／83,000円</td>
                    <td class="dist-col">240 km まで</td>
                    <td class="extra-col">350円／km</td>
                </tr>
            </tbody>
        </table>

        <!-- 追加・割増ルール -->
        <div class="rule-block">
            <div class="rule-title">追加・割増ルール（時間課金なし）</div>
            <table class="rule-table">
                <tr>
                    <th>項目</th>
                    <th>料金・条件</th>
                </tr>
                <tr>
                    <td>深夜早朝運行（22:00〜翌5:00）</td>
                    <td>日額の20％加算</td>
                </tr>
                <tr>
                    <td>運転手宿泊費</td>
                    <td>1泊あたり9,000円（泊数・走行距離・宿泊同行時）</td>
                </tr>
                <tr>
                    <td>繁忙期（4月・8月・10月）</td>
                    <td>基本料金10％加算</td>
                </tr>
                <tr>
                    <td>連日利用割引</td>
                    <td>2〜3日連続：5％OFF ／ 4〜6日：10％OFF ／ 7日以上：15％OFF</td>
                </tr>
            </table>
        </div>

        <div class="back-row">
            <a href="index.php" class="btn-back"> ホームページへ</a>
        </div>

    </div>

    <?php include("includes/footer.php"); ?>

</body>

</html>