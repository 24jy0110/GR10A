<?php
require_once __DIR__ . "/../includes/db_connect.php";

// 48時間前（仮予約日）
$limitTime = date("Y-m-d H:i:s", time() - 48 * 3600);


/* 対象取得（仮予約・48h超過・未開始） */
$sql = "
SELECT
    reservation_number,
    customer_email,
    customer_name,
    service_start_time,
    service_end_date,
    ride_location,
    drop_off_location,
    car_model_code
FROM reservation
WHERE state_code = 'STC01'
  AND reservation_date < :limit
  AND service_start_time > NOW()
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":limit" => $limitTime]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 学校 SMTP */
ini_set("SMTP", "10.64.144.9");
ini_set("smtp_port", "25");

/* 処理 */
foreach ($list as $r) {

    /* ---- 状態更新（自動キャンセル） ---- */
    $upd = "
    UPDATE reservation
    SET state_code = 'STC03',
        driver_id = NULL,
        number_plate = NULL
    WHERE reservation_number = :no
    ";
    $stmt2 = $pdo->prepare($upd);
    $stmt2->execute([":no" => $r["reservation_number"]]);

    /* ---- メール送信 ---- */
    if (empty($r['customer_email'])) {
        continue;
    }

    $resNo = $r['reservation_number'];

    /* 日付・日数 */
    $rideDate = date("Y年m月d日 H:i", strtotime($r['service_start_time']));
    $start = new DateTime($r['service_start_time']);
    $end   = new DateTime($r['service_end_date']);
    $days  = $start->diff($end)->days + 1;

    /* 車種名取得 */
    $stmtCm = $pdo->prepare("SELECT car_model_name FROM car_model WHERE car_model_code = :code");
    $stmtCm->execute([":code" => $r['car_model_code']]);
    $carModelName = $stmtCm->fetchColumn() ?: '';

    /* 件名 */
    $subject = "【丸和交通】ご予約失敗お知らせ（予約番号：{$resNo}）";

    /* 本文（UTF-8） */
    $body_utf8 = <<<MAIL
{$r['customer_name']} 様

このたびは、丸和観光タクシーをご利用いただき、
誠にありがとうございます。

ご依頼いただきました下記の仮予約につきまして、
車両およびドライバーの調整を進めてまいりましたが、
誠に恐れ入りますが、ご希望の日時は大変混み合っており、
車両の手配を行うことができませんでした。

せっかくのご依頼にもかかわらず、
ご希望に添いかねる結果となりましたこと、
心よりお詫び申し上げます。

────────────────────────────────
■ ご予約内容
────────────────────────────────
予約番号　：{$resNo}
乗車日時　：{$rideDate}
乗車場所　：{$r['ride_location']}
降車場所　：{$r['drop_off_location']}
利用日数　：{$days}日
車種　　　：{$carModelName}
────────────────────────────────

本予約リクエストにつきましては、
誠に勝手ながらシステム上【予約不成立（キャンセル扱い）】と
させていただきますこと、何卒ご了承くださいますよう
お願い申し上げます。

またの機会がございましたら、
ぜひ改めてご利用をご検討いただけますと幸いです。

────────────────────────────────
■ お問い合わせ
────────────────────────────────
丸和交通株式会社　観光タクシー予約サポートチーム
TEL：03-1234-5678（8:00～22:00）
MAIL：support@maruwa-taxi.jp

――――――――――――――――――――
丸和交通株式会社
旅をつなぐ、笑顔を運ぶ。
MAIL;

    /* 文字コード変換 */
    $body = mb_convert_encoding($body_utf8, "ISO-2022-JP", "UTF-8");

    /* ヘッダ */
    $headers  = "From: " . mb_encode_mimeheader("丸和交通株式会社", "ISO-2022-JP")
              . " <support@maruwa-taxi.jp>\r\n";
    $headers .= "Reply-To: support@maruwa-taxi.jp\r\n";
    $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

    mb_send_mail($r['customer_email'], $subject, $body, $headers);
}

echo "auto cancel completed\n";