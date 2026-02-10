<?php
session_start();
/* ============================================================
   SESSION 必要データチェック
============================================================ */
$required = [
    'start_date',
    'end_date',
    'start_time',
    'ride_count',
    'pickup_pref',
    'pickup_city',
    'pickup_detail',
    'drop_pref',
    'drop_city',
    'drop_detail',
    'car_model_code',        
    'car_model_name',
    'car_model_use_fee',
    'customer_name',
    'customer_email',
    'customer_phone',
    'sales_office_code'
];

foreach ($required as $key) {
    if (!isset($_SESSION['reserve'][$key])) {
        header("Location: uw05_01.php");
        exit;
    }
}

$res = $_SESSION['reserve'];

/* ============================================================
   DB 接続
============================================================ */
require_once __DIR__ . '/includes/db_connect.php';

/* ============================================================
   予約番号（yymmdd + 3桁連番）
============================================================ */
$today = date("ymd");

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM reservation 
    WHERE reservation_number LIKE :prefix
");
$stmt->execute([':prefix' => $today . '%']);
$count = $stmt->fetchColumn() + 1;

$reservationNumber = $today . str_pad($count, 3, '0', STR_PAD_LEFT);

/* ============================================================
   利用日数 × 単価 → 料金計算
============================================================ */
$start = new DateTime($res['start_date']);
$end   = new DateTime($res['end_date']);

$days = (int)$start->diff($end)->days + 1;
$days = max(1, $days);

$usageFee = $days * (int)$res['car_model_use_fee'];

/* ============================================================
   表示用項目
============================================================ */
$rideLocation = trim("{$res['pickup_pref']} {$res['pickup_city']} {$res['pickup_detail']}");
$dropLocation = trim("{$res['drop_pref']} {$res['drop_city']} {$res['drop_detail']}");

$serviceStart = $res['start_date'] . " " . ($res['start_time'] ?: "07:00");
$serviceEnd   = $res['end_date'];   // date only

/* ============================================================
   今は配車未確定 → 車両・ドライバーは NULL
============================================================ */
$numberPlate = null;
$driverId    = null;

/* ============================================================
   INSERT 実行（★ car_model_code 追加済み）
============================================================ */
$sql = "
INSERT INTO reservation (
    reservation_number,
    reservation_date,
    ride_count,
    car_model_code,   
    ride_location,
    drop_off_location,
    service_start_time,
    service_end_date,
    usage_fee,
    customer_name,
    customer_email,
    customer_phone,
    customer_name_kana,
    lang_pref_1,
    lang_pref_2,
    state_code,
    sales_office_code,
    number_plate,
    driver_id
) VALUES (
    :reservation_number,
    :reservation_date,
    :ride_count,
    :car_model_code,      -- ★ 新規追加
    :ride_location,
    :drop_off_location,
    :service_start_time,
    :service_end_date,
    :usage_fee,
    :customer_name,
    :customer_email,
    :customer_phone,
    :customer_name_kana,
    :lang_pref_1,
    :lang_pref_2,
    'STC01',
    :sales_office_code,
    :number_plate,
    :driver_id
)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':reservation_number' => $reservationNumber,
    ':reservation_date'   => $reservationDate = date('Y-m-d'), 
    ':ride_count'         => $res['ride_count'],
    ':car_model_code'     => $res['car_model_code'], // ★ 重要
    ':ride_location'      => $rideLocation,
    ':drop_off_location'  => $dropLocation,
    ':service_start_time' => $serviceStart,
    ':service_end_date'   => $serviceEnd,
    ':usage_fee'          => $usageFee,
    ':customer_name'      => $res['customer_name'],
    ':customer_email'     => $res['customer_email'],
    ':customer_phone'     => $res['customer_phone'],
    ':customer_name_kana' => $res['customer_name_kana'] ?: null,
    ':lang_pref_1'        => $res['lang_pref_1'],
    ':lang_pref_2'        => $res['lang_pref_2'],
    ':sales_office_code'  => $res['sales_office_code'],
    ':number_plate'       => $numberPlate,   // NULL
    ':driver_id'          => $driverId       // NULL
]);

mb_language("Japanese");
mb_internal_encoding("UTF-8");


date_default_timezone_set('Asia/Tokyo');


ini_set("SMTP", "10.64.144.9");
ini_set("smtp_port", "25");


$applyDateTime = date('Y年m月d日 H:i');

$to = $res['customer_email']; 
$subject = "【丸和交通】仮予約完了のお知らせ";

$body = <<<EOT
{$res['customer_name']} 様

このたびは、丸和交通株式会社の観光ハイヤーサービスに
お申し込みいただき、誠にありがとうございます。

下記の内容にて、【仮予約】を受け付けいたしました。
現時点ではまだ本予約（配車確定）ではございませんので、ご注意ください。

────────────────────
■ 仮予約情報
────────────────────
仮予約番号：{$reservationNumber}
お申込日時：{$applyDateTime}

────────────────────
■ 今後の流れ
────────────────────
1. 弊社にて、車両およびドライバーの空き状況、行程内容を確認いたします。
2. 確認が完了次第、本予約可否と確定料金をメールにてご連絡いたします。
3. ご案内内容に問題がなければ、そのまま本予約として配車手配を行います。

※ ご出発日が近い場合やお急ぎの場合は、
　お手数ですが下記までお電話にてお問い合わせください。

────────────────────
■ お問い合わせ先
────────────────────
丸和交通株式会社　観光ハイヤー予約センター
TEL：03-1234-5678（8:00〜22:00）
MAIL：support@maruwa-taxi.jp

今後とも、丸和交通をご愛顧賜りますようよろしくお願い申し上げます。
EOT;


$fromName  = mb_encode_mimeheader("丸和交通株式会社", "UTF-8");
$fromEmail = "support@maruwa-taxi.jp";
$headers   = "From: {$fromName} <{$fromEmail}>";


mb_send_mail($to, $subject, $body, $headers);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>仮予約を受け付けました | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.body-area {
    max-width: 850px;
    margin: 40px auto 80px;
    color: #111;
    font-size: 17px;
    line-height: 1.8;
}
h1 {
    text-align: center;
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 15px;
}
.red-note {
    color: #d60000;
    font-weight: bold;
}
.reserve-number {
    font-size: 24px;
    font-weight: bold;
    color: #b30000;
    margin: 10px 0;
}
.section-title {
    font-size: 20px;
    font-weight: bold;
    margin-top: 35px;
    margin-bottom: 10px;
}
.home-btn-area {
    text-align: center;
    margin-top: 40px;
}
.home-btn {
    padding: 12px 40px;
    background: #fff;
    border: 1px solid #000;
    font-size: 17px;
    cursor: pointer;
}
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="body-area">

<h1>仮予約を受け付けました</h1>

<p style="text-align:center;">
    お客様のご入力内容で、<span class="red-note">仮予約</span>を受け付けました。<br>
    現時点ではまだ<span class="red-note">本予約（配車確定）ではありません</span>ので、ご注意ください。
</p>

<p class="reserve-number" style="text-align:center;">
【予約番号：<?= htmlspecialchars($reservationNumber) ?>】
</p>

<p style="color:#d60000; text-align:center; margin-bottom:30px;">
予約番号を必ずお控えください。
</p>

<div>
    <div class="section-title">今後の流れ</div>

    <ol style="padding-left:22px;">
        <li>弊社にて、車両およびドライバーの空き状況、行程内容を確認いたします。</li>
        <li>確認が完了次第、<span class="red-note">本予約可否と確定料金</span>をメールにてご連絡いたします。</li>
        <li>ご案内メールをご確認後、問題なければそのまま本予約として手配いたします。</li>
    </ol>
</div>

<div>
    <div class="section-title">仮予約内容の変更・取消について</div>
    内容を変更／取消される場合は、お手数ですが下記までお問い合わせください。
</div>

<div style="margin-top:30px;">
    <div class="section-title">お問い合わせ</div>
    電話：03-1234-5678（8:00–22:00）<br>
    メール：<a href="mailto:support@maruwa-taxi.jp">support@maruwa-taxi.jp</a>
</div>

<div class="home-btn-area">
    <button class="home-btn" onclick="location.href='index.php'">ホームページへ</button>
</div>

</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
