<?php
session_start();
require_once 'dbconnect.php';

// 予約情報がなければトップへ
if (empty($_SESSION['reserve']['start_date'])) {
    header('Location: index.php');
    exit;
}

// 車種マスタ（0502, 0503 と同じ）
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
$car = $cars[$res['car']] ?? null;
if (!$car) {
    header('Location: uw05_02.php');
    exit;
}

// 予約日付表示用
$range_text = $res['start_date'];
if (!empty($res['start_time'])) $range_text .= ' ' . $res['start_time'];
if (!empty($res['end_date']))   $range_text .= ' ～ ' . $res['end_date'];

$error = '';
$done  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'pay') {
    $payment_method = $_POST['payment_method'] ?? '';
    $card_name      = $_POST['card_name']      ?? '';
    $card_number    = $_POST['card_number']    ?? '';
    $card_exp       = $_POST['card_exp']       ?? '';
    $card_cvv       = $_POST['card_cvv']       ?? '';
    $emoney_type    = $_POST['emoney_type']    ?? ''; // 電子マネーの種類
    $emoney_id      = $_POST['emoney_id']      ?? ''; // 任意：取引IDなど（デモ）

    // --- 簡単なバリデーション ---
    if ($payment_method === '') {
        $error = 'お支払い方法を選択してください。';
    } elseif ($payment_method === 'card') {
        if ($card_name === '' || $card_number === '' || $card_exp === '' || $card_cvv === '') {
            $error = 'クレジットカード情報を入力してください。';
        }
    } elseif ($payment_method === 'emoney') {
        if ($emoney_type === '') {
            $error = 'ご利用の電子マネー種別を選択してください。';
        }
        // emoney_id は任意（書かなくてもOK）
    } elseif ($payment_method === 'bank') {
        // 銀行振込は特に必須項目なし（後日メールでご案内、という想定）
    }

    if ($error === '') {
        // ★ 支払いメモを作る（ここが前のコードでは抜けていた）
        $payment_note = '';
        if ($payment_method === 'card') {
            $last4 = substr(preg_replace('/\D/', '', $card_number), -4);
            $payment_note = 'クレジットカード決済（下4桁：' . $last4 . '）';
        } elseif ($payment_method === 'emoney') {
            if ($emoney_type !== '') {
                if ($emoney_id !== '') {
                    $payment_note = '電子マネー（' . $emoney_type . '、取引ID：' . $emoney_id . '）';
                } else {
                    $payment_note = '電子マネー（' . $emoney_type . '）';
                }
            }
        } elseif ($payment_method === 'bank') {
            $payment_note = '銀行振込';
        }

        try {
            $sql = "INSERT INTO reservations (
                        created_at,
                        start_date, start_time, end_date,
                        name, name_kana, people, wheelchair,
                        pickup_area, pickup_city, pickup_detail,
                        drop_area, drop_city, drop_detail,
                        lang1, lang2,
                        car_code, payment_method, payment_note
                    ) VALUES (
                        NOW(),
                        :start_date, :start_time, :end_date,
                        :name, :name_kana, :people, :wheelchair,
                        :pickup_area, :pickup_city, :pickup_detail,
                        :drop_area, :drop_city, :drop_detail,
                        :lang1, :lang2,
                        :car_code, :payment_method, :payment_note
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':start_date'     => $res['start_date'],
                ':start_time'     => $res['start_time'] ?? null,
                ':end_date'       => $res['end_date']   ?? null,
                ':name'           => $res['name'] ?? '',
                ':name_kana'      => $res['name_kana'] ?? '',
                ':people'         => $res['people'] ?? '',
                ':wheelchair'     => $res['wheelchair'] ?? '',
                ':pickup_area'    => $res['pickup_area'] ?? '',
                ':pickup_city'    => $res['pickup_city'] ?? '',
                ':pickup_detail'  => $res['pickup_detail'] ?? '',
                ':drop_area'      => $res['drop_area'] ?? '',
                ':drop_city'      => $res['drop_city'] ?? '',
                ':drop_detail'    => $res['drop_detail'] ?? '',
                ':lang1'          => $res['lang1'] ?? '',
                ':lang2'          => $res['lang2'] ?? '',
                ':car_code'       => $res['car'],
                ':payment_method' => $payment_method,
                ':payment_note'   => $payment_note,
            ]);

            unset($_SESSION['reserve']);
            $done = true;
        } catch (PDOException $e) {
            $error = 'システムエラーが発生しました。時間をおいて再度お試しください。';
            // デバッグ用に一時的に詳細を見たいなら↓を開ける
            // $error .= '（詳細：' . $e->getMessage() . '）';
        }
    }
} // ★ 外側の if ($_SERVER['REQUEST_METHOD']...) をここで閉じる
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>uw05_04 お支払い</title>
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
            margin-bottom: 16px;
        }

        .error-msg {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .pay-table th,
        .pay-table td {
            padding: 10px 8px;
            vertical-align: top;
            font-size: 16px;
        }

        .pay-table th {
            width: 220px;
            text-align: left;
            font-weight: bold;
        }

        .pay-table input[type="text"],
        .pay-table input[type="password"],
        .pay-table input[type="tel"] {
            width: 100%;
            max-width: 320px;
            padding: 6px 8px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .payment-options label {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
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

        /* 完了画面用 */
        .complete-box {
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 6px;
        }

        .complete-title {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include("includes/header.php"); ?>

    <div class="container">
        <h1>丸和交通株式会社</h1>
        <p class="sub-copy">
            maruwa transportation co.,LTD.<br>
            旅をつなぐ、笑顔を運ぶ。
        </p>

        <?php if ($done): ?>
            <!-- 予約完了画面 -->
            <div class="page-title">予約完了</div>
            <div class="complete-box">
                <div class="complete-title">ご予約ありがとうございました。</div>
                <p>
                    ご入力いただいた内容とお支払い情報をもとに、<br>
                    送迎予約を受け付けいたしました。
                </p>
                <p>
                    後ほど担当ドライバーまたは配車センターより、<br>
                    最終確認のご連絡を差し上げます。
                </p>
                <p class="small-note">
                    ※ 本画面はデモサイトのため、実際の決済処理は行われておりません。
                </p>
            </div>

            <div class="button-row" style="margin-top:24px;">
                <button type="button" class="btn-next" onclick="location.href='index.php'">トップページへ戻る</button>
            </div>

        <?php else: ?>
            <!-- 支払い入力画面 -->
            <div class="page-title">お支払い</div>
            <div class="reserve-date">
                予約日付　<?php echo htmlspecialchars($range_text, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <p>
                ご選択の車種：<?php echo htmlspecialchars($car['jp_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                参考日額：<?php echo htmlspecialchars($car['price_jp'], ENT_QUOTES, 'UTF-8'); ?>
                （多言語ドライバー：<?php echo htmlspecialchars($car['price_fg'], ENT_QUOTES, 'UTF-8'); ?>）
            </p>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form action="uw05_04.php" method="post">
                <input type="hidden" name="step" value="pay">

                <table class="pay-table">
                    <tr>
                        <th>お支払い方法</th>
                        <td class="payment-options">
                            <label>
                                <input type="radio" name="payment_method" value="card">
                                クレジットカード（デモ）
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="emoney">
                                電子マネー（PayPay／楽天ペイ／Alipay／WeChat Pay など）
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="bank">
                                銀行振込（デモ）
                            </label>
                            <div class="small-note">
                                ※ 本サイトはデモ用のため、実際の決済処理は行われません。<br>
                                ※ 現金でのお支払いはお受けしておりません。
                            </div>
                        </td>
                    </tr>

                    <!-- クレジットカード用 -->
                    <tr id="cardRow1" style="display:none;">
                        <th>カード名義（ローマ字）</th>
                        <td>
                            <input type="text" name="card_name" placeholder="TARO YAMADA">
                        </td>
                    </tr>
                    <tr id="cardRow2" style="display:none;">
                        <th>カード番号</th>
                        <td>
                            <input type="tel" name="card_number" placeholder="1234 5678 9012 3456">
                        </td>
                    </tr>
                    <tr id="cardRow3" style="display:none;">
                        <th>有効期限／セキュリティコード</th>
                        <td>
                            <input type="text" name="card_exp" placeholder="MM/YY" style="max-width:120px; display:inline-block;">
                            &nbsp;
                            <input type="password" name="card_cvv" placeholder="CVV" style="max-width:100px; display:inline-block;">
                        </td>
                    </tr>

                    <!-- 電子マネー用 -->
                    <tr id="emoneyRow1" style="display:none;">
                        <th>電子マネー種別</th>
                        <td>
                            <select name="emoney_type">
                                <option value="">選択してください</option>
                                <option value="PayPay">PayPay</option>
                                <option value="楽天ペイ">楽天ペイ</option>
                                <option value="LINE Pay">LINE Pay</option>
                                <option value="Alipay">Alipay（支付宝）</option>
                                <option value="WeChat Pay">WeChat Pay（微信支付）</option>
                                <option value="その他">その他</option>
                            </select>
                            <div class="small-note">
                                ※ 実際の決済は各サービスのアプリ／画面にて行っていただく想定です（本画面ではデモのみ）。
                            </div>
                        </td>
                    </tr>
                    <tr id="emoneyRow2" style="display:none;">
                        <th>取引ID／お支払い名義（任意）</th>
                        <td>
                            <input type="text" name="emoney_id" placeholder="例）取引IDやお支払い名義など（任意）">
                        </td>
                    </tr>

                    <!-- 銀行振込には入力不要。別途メールなどで振込先をご案内する想定 -->
                </table>

                <div class="small-note">
                    ※ 予約内容とお支払い情報を送信することで、利用規約およびプライバシーポリシーに同意したものとみなされます。<br>
                    ※ 乗車人数やお荷物量に対する車種選択は、お客様の自己責任にてお願いいたします。
                </div>

                <div class="button-row">
                    <button type="button" class="btn-back" onclick="location.href='uw05_03.php'">戻る</button>
                    <button type="submit" class="btn-next">この内容で予約を確定する</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include("includes/footer.php"); ?>

    <script>
        // 支払い方法によって入力欄の表示/非表示を切り替え
        const methodRadios = document.querySelectorAll('input[name="payment_method"]');
        const cardRows = [
            document.getElementById('cardRow1'),
            document.getElementById('cardRow2'),
            document.getElementById('cardRow3'),
        ];
        const emoneyRows = [
            document.getElementById('emoneyRow1'),
            document.getElementById('emoneyRow2'),
        ];

        function updatePayRows() {
            let method = '';
            methodRadios.forEach(r => {
                if (r.checked) method = r.value;
            });

            const showCard = (method === 'card');
            const showEmoney = (method === 'emoney');

            cardRows.forEach(row => {
                row.style.display = showCard ? '' : 'none';
            });
            emoneyRows.forEach(row => {
                row.style.display = showEmoney ? '' : 'none';
            });
        }

        methodRadios.forEach(radio => {
            radio.addEventListener('change', updatePayRows);
        });

        // ページ読み込み時に一度状態を反映（戻ってきた場合など）
        updatePayRows();
    </script>