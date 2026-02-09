<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

mb_language("Japanese");
mb_internal_encoding("UTF-8");

/* ---------------------------------------------------
   受付（01）以外アクセス禁止
--------------------------------------------------- */
if (!isset($_SESSION['employee']) || $_SESSION['employee']['job_code'] !== "01") {
    header("Location: login.php");
    exit;
}

/* ---------------------------------------------------
   GET パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw101.php");
    exit;
}

$resNo = $_GET['r'];

/* ---------------------------------------------------
   ★ キャンセル済み予約情報取得（メール用）
--------------------------------------------------- */
$sql = "
SELECT
    r.customer_name,
    r.customer_email,
    r.service_start_time,
    r.service_end_date,
    r.ride_location,
    r.drop_off_location,
    cm.car_model_name
FROM reservation r
LEFT JOIN car_model cm
  ON cm.car_model_code = r.car_model_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$mailData = $stmt->fetch(PDO::FETCH_ASSOC);

/* メール送信可能かチェック */
if ($mailData && !empty($mailData['customer_email'])) {

    /* 日付・日数計算 */
    $rideDate = date("Y年m月d日 H:i", strtotime($mailData['service_start_time']));

    $start = new DateTime($mailData['service_start_time']);
    $end   = new DateTime($mailData['service_end_date']);
    $days  = $start->diff($end)->days + 1;

    /* 学校 SMTP */
    ini_set("SMTP", "10.64.144.9");
    ini_set("smtp_port", "25");

    $to = $mailData['customer_email'];

    $subject = "【丸和交通】ご予約キャンセル完了のお知らせ（予約番号：{$resNo}）";

    $body_utf8 = <<<MAIL
{$mailData['customer_name']} 様

このたびは、丸和交通株式会社の観光ハイヤーサービスを
ご利用いただき、誠にありがとうございます。

ご依頼いただきました下記のご予約につきまして、
キャンセル手続きが正常に完了いたしましたので
ご連絡申し上げます。

────────────────────────────────
■ キャンセル内容
────────────────────────────────
予約番号　：{$resNo}
乗車日時　：{$rideDate}
乗車場所　：{$mailData['ride_location']}
降車場所　：{$mailData['drop_off_location']}
利用日数　：{$days}日
車種　　　：{$mailData['car_model_name']}
────────────────────────────────

本件につきまして、キャンセル料は発生いたしません。
またのご利用を、スタッフ一同心よりお待ちしております。

────────────────────────────────
■ お問い合わせ先
────────────────────────────────
丸和交通株式会社　観光タクシー予約サポートチーム
TEL：03-1234-5678（8:00～22:00）
MAIL：support@maruwa-taxi.jp

――――――――――――――――――――
丸和交通株式会社
旅をつなぐ、笑顔を運ぶ。
MAIL;

    /* UTF-8 → ISO-2022-JP */
    $body = mb_convert_encoding($body_utf8, "ISO-2022-JP", "UTF-8");

    $headers  = "From: " . mb_encode_mimeheader("丸和交通株式会社", "ISO-2022-JP")
        . " <support@maruwa-taxi.jp>\r\n";
    $headers .= "Reply-To: support@maruwa-taxi.jp\r\n";
    $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

    mb_send_mail($to, $subject, $body, $headers);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約キャンセル完了 | 丸和交通</title>
    <link rel="stylesheet" href="assets/app.css">

    <style>
        .body-area {
            max-width: 850px;
            margin: 60px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            font-size: 17px;
            line-height: 1.8;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
        }

        .cancel-number {
            font-size: 24px;
            font-weight: bold;
            color: #b30000;
            text-align: center;
            margin: 15px 0 30px;
        }

        .message-area {
            text-align: center;
            margin-bottom: 30px;
            color: #444;
        }

        .btn-area {
            text-align: center;
            margin-top: 40px;
        }

        .back-btn {
            padding: 12px 36px;
            font-size: 17px;
            border: 1px solid #000;
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            color: #000;
            border-radius: 6px;
        }

        .back-btn:hover {
            background: #f5f5f5;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . "/includes/header.php"; ?>

    <div class="body-area">

        <h1>予約キャンセルが完了しました</h1>

        <p class="message-area">
            以下の仮予約を <span style="color:#d00000; font-weight:bold;">キャンセル</span> いたしました。<br>
            内容をご確認のうえ、必要に応じてお客様へご案内ください。
        </p>

        <div class="cancel-number">
            【予約番号：<?= htmlspecialchars($resNo) ?>】
        </div>

        <div class="btn-area">
            <a href="uw101.php" class="back-btn">予約一覧へ戻る</a>
        </div>

    </div>

    <?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>