<?php
session_start();

// 予約情報がなければトップへ
if (empty($_SESSION['reserve']['start_date'])) {
    header('Location: index.php');
    exit;
}

// 車種マスタ（uw05_02 と同じ。あとで共通ファイルにしてもOK）
$cars = [
    'crown' => [
        'jp_name'   => 'トヨタ クラウン',
        'en_name'   => 'Toyota Crown',
        'capacity'  => '3名',
        'suitcase'  => '最大2個',
        'price_jp'  => '65,000円',
        'price_fg'  => '73,000円',
    ],
    'alphard' => [
        'jp_name'   => 'トヨタ アルファード',
        'en_name'   => 'Toyota Alphard',
        'capacity'  => '5名',
        'suitcase'  => '最大4個',
        'price_jp'  => '70,000円',
        'price_fg'  => '78,000円',
    ],
    'hiace' => [
        'jp_name'   => 'トヨタ ハイエース',
        'en_name'   => 'Toyota Hiace',
        'capacity'  => '9名',
        'suitcase'  => '最大10個',
        'price_jp'  => '75,000円',
        'price_fg'  => '83,000円',
    ],
];

$res = $_SESSION['reserve'];

// 車種が未選択なら 02 に戻す
if (empty($res['car']) || !isset($cars[$res['car']])) {
    header('Location: uw05_02.php');
    exit;
}

$car = $cars[$res['car']];

// 日付表示用
$range_text = $res['start_date'];
if (!empty($res['start_time'])) $range_text .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $range_text .= ' ～ ' . $res['end_date'];

// 支払いへ進むボタンが押されたとき
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'confirm') {
    // ここではまだDBに入れず、支払い画面へ
    header('Location: uw05_04.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>uw05_03 予約内容確認</title>
    <link rel="stylesheet" href="./assets/app.css">
    <style>
        .container {
            max-width: 980px;
            margin: 40px auto 60px;
            padding: 0 20px;
        }

        h1 {
            font-size: 26px;
            margin-bottom: 4px;
        }

        .sub-copy {
            margin-bottom: 18px;
            color: #555;
        }

        .page-title {
            font-size: 20px;
            margin: 20px 0 10px;
            font-weight: bold;
        }

        .reserve-date {
            font-size: 18px;
            margin-bottom: 24px;
        }

        .confirm-table {
            width: 100%;
            border-collapse: collapse;
        }

        .confirm-table th,
        .confirm-table td {
            padding: 10px 8px;
            vertical-align: top;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
        }

        .confirm-table th {
            width: 220px;
            text-align: left;
            font-weight: bold;
            background-color: #f8f8f8;
        }

        .small-note {
            font-size: 12px;
            margin-top: 8px;
            color: #666;
            line-height: 1.6;
        }

        .button-row {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .btn-back,
        .btn-next {
            flex: 1;
            padding: 10px 0;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #000;
            cursor: pointer;
        }

        .btn-back {
            background-color: #fff;
            color: #000;
        }

        .btn-next {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <div class="container">
        <h1 class="page-title">予約内容確認</h1>
        <div class="reserve-date">
            予約日付　<?php echo htmlspecialchars($range_text, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <form action="uw05_03.php" method="post">
            <input type="hidden" name="step" value="confirm">

            <table class="confirm-table">
                <tr>
                    <th>お客様名前</th>
                    <td>
                        <?php echo htmlspecialchars($res['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?><br>
                        （カタカナ：
                        <?php echo htmlspecialchars($res['name_kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>）
                    </td>
                </tr>
                <tr>
                    <th>乗客人数</th>
                    <td>
                        <?php echo htmlspecialchars($res['people'] ?? '', ENT_QUOTES, 'UTF-8'); ?> 名
                        <?php if (!empty($res['wheelchair']) && $res['wheelchair'] !== 'なし'): ?>
                            ／ 車椅子・ベビーカー：
                            <?php
                            $wheel = [
                                'wheelchair' => '車椅子あり',
                                'babycar'    => 'ベビーカーあり',
                                'both'       => '両方あり',
                                'なし'       => 'なし'
                            ];
                            $label = $wheel[$res['wheelchair']] ?? $res['wheelchair'];
                            echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                            ?>
                        <?php else: ?>
                            ／ 車椅子・ベビーカー：なし
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>乗車場所</th>
                    <td>
                        <?php
                        $pickup_main = trim(($res['pickup_area'] ?? '') . ' ' . ($res['pickup_city'] ?? ''));
                        echo htmlspecialchars($pickup_main !== '' ? $pickup_main : '未選択', ENT_QUOTES, 'UTF-8');
                        ?><br>
                        詳細住所：
                        <?php echo htmlspecialchars($res['pickup_detail'] ?? '未入力', ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
                <tr>
                    <th>降車場所</th>
                    <td>
                        <?php
                        $drop_main = trim(($res['drop_area'] ?? '') . ' ' . ($res['drop_city'] ?? ''));
                        echo htmlspecialchars($drop_main !== '' ? $drop_main : '未選択', ENT_QUOTES, 'UTF-8');
                        ?><br>
                        詳細住所：
                        <?php echo htmlspecialchars($res['drop_detail'] ?? '未入力', ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
                <tr>
                    <th>対応言語</th>
                    <td>
                        第1希望：
                        <?php echo htmlspecialchars($res['lang1'] ?: '指定なし', ENT_QUOTES, 'UTF-8'); ?><br>
                        第2希望：
                        <?php echo htmlspecialchars($res['lang2'] ?: '指定なし', ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
                <tr>
                    <th>選択された車種</th>
                    <td>
                        <?php echo htmlspecialchars($car['jp_name'], ENT_QUOTES, 'UTF-8'); ?>
                        （<?php echo htmlspecialchars($car['en_name'], ENT_QUOTES, 'UTF-8'); ?>）<br>
                        乗客定員：<?php echo htmlspecialchars($car['capacity'], ENT_QUOTES, 'UTF-8'); ?>／
                        スーツケース目安：<?php echo htmlspecialchars($car['suitcase'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
                <tr>
                    <th>料金（参考）</th>
                    <td>
                        日本語ドライバー：<?php echo htmlspecialchars($car['price_jp'], ENT_QUOTES, 'UTF-8'); ?>／日<br>
                        多言語対応ドライバー：<?php echo htmlspecialchars($car['price_fg'], ENT_QUOTES, 'UTF-8'); ?>／日<br>
                        <span class="small-note">
                            ※ 実際のご請求金額は、ご利用日数・走行距離・深夜早朝料金・繁忙期加算などにより変動いたします。<br>
                            ※ こちらの画面では日額の参考料金のみを表示しています。
                        </span>
                    </td>
                </tr>
            </table>

            <p class="small-note">
                上記内容をご確認のうえ、「この内容で支払いへ」ボタンを押してください。<br>
                乗車人数やお荷物量と車種の相性については、お客様ご自身のご判断・ご責任にてお願いいたします。
            </p>

            <div class="button-row">
                <button type="button" class="btn-back" onclick="location.href='uw05_02.php'">戻る</button>
                <button type="submit" class="btn-next">この内容で支払いへ</button>
            </div>
        </form>
    </div>

    <?php include("includes/footer.php"); ?>

</body>

</html>