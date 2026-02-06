<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

function sendTempReservationMail($toMail, $toName, $data)
{
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourmail@gmail.com';
        $mail->Password   = '应用专用密码';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('yourmail@gmail.com', '車両予約管理システム');
        $mail->addAddress($toMail, $toName);

        $mail->Subject = '【仮予約完了】車両予約受付のお知らせ';

        $mail->Body = <<<EOT
{$toName} 様

この度は、車両予約システムをご利用いただき、
誠にありがとうございます。

以下の内容で、車両の【仮予約】を受け付けました。

――――――――――――――――
予約番号　：{$data['reservation_id']}
利用日　　：{$data['date']}
利用時間　：{$data['time']}
車両名　　：{$data['car']}
――――――――――――――――

※ 本予約は仮予約の状態です。

車両予約管理システム
（授業課題用）
EOT;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}
