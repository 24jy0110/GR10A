<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * 仮予約完了メール送信
 *
 * @param string $toMail 宛先メール
 * @param string $toName 宛先姓名
 * @param array  $data   予約信息
 * @return bool
 */
function sendTempReservationMail(string $toMail, string $toName, array $data): bool
{
    $mail = new PHPMailer(true);

    try {
        /* ===============================
           基本設定
        =============================== */
        $mail->CharSet = 'UTF-8';

        /* ===============================
           Fake SMTP Server（学校用）
        =============================== */
        $mail->isSMTP();
        $mail->Host       = '127.0.0.1';
        $mail->Port       = 25;
        $mail->SMTPAuth   = false;

        /* ===============================
           宛先・差出人
        =============================== */
        $mail->setFrom('test@example.com', '車両予約管理システム');
        $mail->addAddress($toMail, $toName);

        /* ===============================
           件名
        =============================== */
        $mail->Subject = '【仮予約完了】車両予約受付のお知らせ';

        /* ===============================
           本文
        =============================== */
        $mail->Body = <<<EOT
{$toName} 様

この度は、車両予約システムをご利用いただき、
誠にありがとうございます。

以下の内容にて、車両の【仮予約】を受け付けました。

――――――――――――――
予約番号：{$data['reservation_id']}
利用日　：{$data['date']}
利用時間：{$data['time']}
車両名　：{$data['car']}
――――――――――――――

※ 本予約は現在「仮予約」の状態です。
内容確認後、担当者より正式なご連絡を差し上げます。

車両予約管理システム
（模擬環境）
EOT;

        /* ===============================
           送信
        =============================== */
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}
