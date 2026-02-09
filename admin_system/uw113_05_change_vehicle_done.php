<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

mb_language("Japanese");
mb_internal_encoding("UTF-8");

/* ---------------------------------------------------
   パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r']) || !isset($_GET['car'])) {
    header("Location: uw113_01_reservation_list.php");
    exit;
}

$resNo  = $_GET['r'];
$newCar = $_GET['car'];

/* ===================================================
   ① 変更前の予約情報取得（★必ず UPDATE 前）
=================================================== */
$sql_before = "
SELECT 
    r.customer_name,
    r.customer_email,
    r.number_plate AS old_plate,
    cm.car_model_name AS old_model
FROM reservation r
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql_before);
$stmt->execute([":no" => $resNo]);
$before = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$before) {
    die("変更前の予約情報が取得できません。");
}

$customerName = $before["customer_name"];
$to           = $before["customer_email"];
$oldCar       = $before["old_model"] . " " . $before["old_plate"];

/* ===================================================
   ② 新しい車両の車種取得
=================================================== */
$sql_model = "
SELECT 
    v.car_model_code,
    cm.car_model_name
FROM vehicle v
JOIN car_model cm ON cm.car_model_code = v.car_model_code
WHERE v.number_plate = :plate
";
$stmt = $pdo->prepare($sql_model);
$stmt->execute([":plate" => $newCar]);
$new = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$new) {
    die("選択した車両が存在しません。");
}

$newModelCode = $new["car_model_code"];
$newCarText   = $new["car_model_name"] . " " . $newCar;

/* ===================================================
   ③ 予約テーブル更新
=================================================== */
$update_sql = "
UPDATE reservation
SET number_plate = :plate,
    car_model_code = :model
WHERE reservation_number = :no
";
$stmt = $pdo->prepare($update_sql);
$stmt->execute([
    ":plate" => $newCar,
    ":model" => $newModelCode,
    ":no"    => $resNo
]);

/* ===================================================
   ④ 配車変更通知メール送信
=================================================== */

// 学校SMTP
ini_set("SMTP", "10.64.144.9");
ini_set("smtp_port", "25");

/* 件名（★必ずエンコード） */
$subject = "【丸和交通】配車変更のお知らせ（予約番号：{$resNo}）";


/* 本文（UTF-8） */
$body_utf8 = <<<MAIL
{$customerName} 様

いつも大変お世話になっております。
丸和交通株式会社 配車センターでございます。

この度は、予約番号：{$resNo} のご予約につきまして、
誠に勝手ながら配車予定の車両が変更となりましたので
ご連絡申し上げます。

--------------------------------------------------
【変更内容】
--------------------------------------------------
変更前： {$oldCar}
変更後： {$newCarText}

その他の変更はございません。

--------------------------------------------------
■ お問い合わせ先
--------------------------------------------------
丸和交通株式会社 観光ハイヤー予約センター
TEL：03-1234-5678（8:00〜22:00）
MAIL：info@maruwa-taxi.jp
MAIL;

/* ★ ISO-2022-JP に変換 */
$body = mb_convert_encoding($body_utf8, "ISO-2022-JP", "UTF-8");

/* ヘッダ */
$headers  = "From: " . mb_encode_mimeheader("丸和交通株式会社", "ISO-2022-JP")
    . " <info@maruwa-taxi.jp>\r\n";
$headers .= "Reply-To: info@maruwa-taxi.jp\r\n";
$headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

/* 送信 */
mb_send_mail($to, $subject, $body, $headers);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>配車変更完了 | 丸和交通</title>

    <style>
        body {
            font-family: "Noto Sans JP", sans-serif;
            background: #f5f5f5;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .msg {
            font-size: 18px;
            margin-bottom: 35px;
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            margin: 8px;
            border-radius: 6px;
            font-size: 16px;
            text-decoration: none;
            color: #fff;
        }

        .btn-detail {
            background: #0A84FF;
        }

        .btn-list {
            background: #555;
        }
    </style>

</head>

<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container">

        <div class="title">配車変更完了</div>

        <div class="msg">
            配車の変更が正常に完了しました。
        </div>

        <a href="uw113_02_reservation_detail.php?r=<?= urlencode($resNo) ?>"
            class="btn btn-detail">
            予約詳細へ戻る
        </a>

        <a href="uw113_01_reservation_list.php"
            class="btn btn-list">
            配車一覧へ戻る
        </a>

    </div>

</body>

</html>