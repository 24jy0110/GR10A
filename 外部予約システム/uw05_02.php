<?php
session_start();

// 前のステップの情報がなければトップへ
if (empty($_SESSION['reserve']['start_date'])) {
    header('Location: index.php');
    exit;
}

// 車種マスタ
$cars = [
    'crown' => [
        'jp_name'   => 'トヨタ クラウン',
        'en_name'   => 'Toyota Crown',
        'capacity'  => '3名',
        'suitcase'  => '最大2個',
        'baby'      => 'ベビーカー・車椅子×2：対応可',
        'feature'   => '「移動するリビング」に変える上質セダン',
        'price_jp'  => '65,000円',
        'price_fg'  => '73,000円',
        'img'       => 'imgs/crown.jpg',
    ],
    'alphard' => [
        'jp_name'   => 'トヨタ アルファード',
        'en_name'   => 'Toyota Alphard',
        'capacity'  => '5名',
        'suitcase'  => '最大4個',
        'baby'      => 'ベビーカー・車椅子×2：対応可',
        'feature'   => '「旅のプライベートスイート」を叶えるラグジュアリーMPV',
        'price_jp'  => '70,000円',
        'price_fg'  => '78,000円',
        'img'       => 'imgs/alphard.jpg',
    ],
    'hiace' => [
        'jp_name'   => 'トヨタ ハイエース',
        'en_name'   => 'Toyota Hiace',
        'capacity'  => '9名',
        'suitcase'  => '最大10個',
        'baby'      => 'ベビーカー・車椅子×2：対応可',
        'feature'   => 'アクティブな仲間旅を支える “ゆとりワゴン”',
        'price_jp'  => '75,000円',
        'price_fg'  => '83,000円',
        'img'       => 'imgs/hiace.png',
    ],
];

$error = '';
$selected = $_SESSION['reserve']['car'] ?? 'crown'; // デフォルトでクラウン

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['car']) || !isset($cars[$_POST['car']])) {
        $error = '車種を選択してください。';
    } else {
        $_SESSION['reserve']['car'] = $_POST['car'];
        header('Location: uw05_03.php'); // 確認画面へ
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>uw05_02 予約入力 車種選択画面</title>
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

        .guide-title {
            font-size: 18px;
            margin-bottom: 16px;
        }

        .error-msg {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .car-list {
            margin-top: 8px;
        }

        .car-card {
            display: grid;
            grid-template-columns: 40px 200px 1fr;
            border: 1px solid #333;
            padding: 12px 10px;
            margin-bottom: 12px;
            gap: 10px;
            background-color: #fff;
            cursor: pointer;
        }

        label:has(input[type="radio"]:checked) .car-card {
            border: 2px solid #007bff;
            background-color: #f0f8ff;
            padding: 11px 9px;
        }

        .car-radio {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .car-image {
            text-align: center;
        }

        .car-image img {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto 6px;
        }

        .car-name {
            font-weight: bold;
            margin-top: 4px;
        }

        .car-name small {
            display: block;
            font-size: 12px;
            color: #555;
        }

        .car-info {
            font-size: 14px;
            line-height: 1.6;
        }

        .car-info .price {
            margin-top: 6px;
            font-weight: bold;
        }

        .footnote {
            font-size: 12px;
            margin-top: 12px;
            line-height: 1.6;
        }

        .disclaimer {
            font-size: 12px;
            margin-top: 8px;
            line-height: 1.6;
            color: #555;
        }

        .button-row {
            margin-top: 24px;
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

    // ... (PHP 代码保持不变)

    ?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        // ... (CSS/head 部分保持不变)
    </head>

    <body>

        <?php include("includes/header.php"); ?>
        <div class="container">
            <h1 class="guide-title">お気に入りの車種を選択してください</h1>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form action="uw05_02.php" method="post">
                <div class="car-list">
                    <?php foreach ($cars as $code => $car): ?>
                        <label for="car_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="car-card">
                                <div class="car-radio">
                                    <input type="radio"
                                        id="car_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                        name="car"
                                        value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php if ($selected === $code) echo 'checked'; ?>>
                                </div>

                                <div class="car-image">
                                    <img src="<?php echo htmlspecialchars($car['img'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($car['jp_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="car-name">
                                        <?php echo htmlspecialchars($car['jp_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                                        <small><?php echo htmlspecialchars($car['en_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                </div>

                                <div class="car-info">
                                    乗客定員：<?php echo htmlspecialchars($car['capacity'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    スーツケース※1：<?php echo htmlspecialchars($car['suitcase'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    <?php echo htmlspecialchars($car['baby'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    特徴：<?php echo htmlspecialchars($car['feature'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    <span class="price"> 料金※3：<?php echo htmlspecialchars($car['price_jp'], ENT_QUOTES, 'UTF-8'); ?>
                                        （<?php echo htmlspecialchars($car['price_fg'], ENT_QUOTES, 'UTF-8'); ?>）／日
                                    </span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="footnote">
                    ※1 スーツケースは1個あたり容量100リットル以下のものに限らせていただきます。<br>
                    ※2 ベビーカーまたは車椅子を積載いただくと、車内スペースの都合により積載できるスーツケースの数が減少します。<br>
                    ※3 料金表に記載の（　）内金額は、多言語対応ドライバーをご希望の場合の追加料金を含んだ参考料金となっています。
                </div>

                <div class="disclaimer">
                    ※ ご乗車人数・お荷物量に対して不適切な車種を選択された場合、当日の積載状況によっては、
                    お荷物の一部をお断りする場合がございます。<br>
                    ※ 車種選択はお客様の自己責任にてお願いいたします。
                </div>

                <div class="button-row">
                    <button type="button" class="btn-back" onclick="location.href='uw05_01.php'">戻る</button>
                    <button type="submit" class="btn-next">次へ</button>
                </div>
            </form>
        </div>

        <?php include("includes/footer.php"); ?>

    </body>

    </html>