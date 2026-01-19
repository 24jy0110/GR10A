<?php
session_start();

/* ===============================
   SESSION チェック
================================ */
if (empty($_SESSION['reserve'])) {
    header('Location: index.php');
    exit;
}

$res = $_SESSION['reserve'];

/* ===============================
   DB 接続
================================ */
$dsn = 'mysql:host=localhost;dbname=24jy0141;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('DB接続エラー：' . $e->getMessage());
}

/* ===============================
   予約番号生成（yymmdd + 連番）
================================ */
$today = date('ymd');

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM reservation WHERE reservation_number LIKE :prefix"
);
$stmt->execute([':prefix' => $today . '%']);
$count = $stmt->fetchColumn() + 1;

$reservationNumber = $today . str_pad($count, 3, '0', STR_PAD_LEFT);

/* ===============================
   データ整形
================================ */
$rideLocation =
    $res['pickup_pref'] . ' ' .
    $res['pickup_city'] . ' ' .
    $res['pickup_detail'];

$dropLocation =
    $res['drop_pref'] . ' ' .
    $res['drop_city'] . ' ' .
    $res['drop_detail'];

$serviceStart = $res['start_date'] . ' ' . ($res['start_time'] ?: '07:00');

/* ===============================
   INSERT
================================ */
$sql = "
INSERT INTO reservation (
  reservation_number,
  reservation_date,
  ride_count,
  ride_location,
  drop_off_location,
  service_start_time,
  usage_fee,
  customer_name,
  customer_email,
  customer_phone,
  customer_name_kana,
  lang_pref_1,
  lang_pref_2,
  state_code,
  number_plate,
  driver_id
) VALUES (
  :reservation_number,
  :reservation_date,
  :ride_count,
  :ride_location,
  :drop_off_location,
  :service_start_time,
  :usage_fee,
  :customer_name,
  :customer_email,
  :customer_phone,
  :customer_name_kana,
  :lang_pref_1,
  :lang_pref_2,
  :state_code,
  :number_plate,
  :driver_id
)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':reservation_number' => $reservationNumber,
    ':reservation_date'   => $res['start_date'],
    ':ride_count'         => $res['people'],
    ':ride_location'      => $rideLocation,
    ':drop_off_location'  => $dropLocation,
    ':service_start_time' => $serviceStart,
    ':usage_fee'          => $res['car_price'],
    ':customer_name'      => $res['customer_name'],
    ':customer_email'     => $res['customer_email'],
    ':customer_phone'     => $res['customer_phone'],
    ':customer_name_kana' => $res['customer_name_kana'],
    ':lang_pref_1'        => $res['lang1'],
    ':lang_pref_2'        => $res['lang2'],
    ':state_code'         => 'TMP',          // 仮予約
    ':number_plate'       => 'UNASSIGNED',
    ':driver_id'          => 'UNASSIGNED'
]);

/* ===============================
   仮予約メール送信
================================ */
$to = $res['customer_email'];
$subject = '【丸和交通】仮予約受付のお知らせ';

$message = <<<MAIL
{$res['customer_name']} 様

このたびは、丸和交通株式会社の観光ハイヤーサービスに
お申し込みいただき、誠にありがとうございます。

下記内容にて【仮予約】を受け付けいたしました。

■ 仮予約番号
{$reservationNumber}

■ ご利用日
{$serviceStart}

■ 乗車場所
{$rideLocation}

■ 降車場所
{$dropLocation}

現在はまだ【仮予約】の状態です。
車両およびドライバーの手配が完了次第、
【本予約確定】のご連絡を差し上げます。

ご不明な点がございましたら、下記までお問い合わせください。

――――――――――――
丸和交通株式会社 観光ハイヤー予約センター
TEL：03-1234-5678（8:00〜22:00）
MAIL：support@maruwa-taxi.jp
――――――――――――

MAIL;

$headers = "From: support@maruwa-taxi.jp";

mail($to, $subject, $message, $headers);

/* ===============================
   SESSION クリア（任意）
================================ */
// session_destroy();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>仮予約完了 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">
<style>
.container {
  max-width: 800px;
  margin: 60px auto;
  text-align: center;
}
.res-number {
  font-size: 22px;
  font-weight: bold;
  margin: 20px 0;
}
.note {
  font-size: 14px;
  line-height: 1.8;
}
.btn-home {
  margin-top: 40px;
  padding: 12px 40px;
  border: 1px solid #000;
  background: #fff;
  cursor: pointer;
}
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">
  <h2>仮予約を受け付けました</h2>

  <p class="note">
    現時点ではまだ本予約（配車確定）ではありません。<br>
    手配完了後、改めてご連絡いたします。
  </p>

  <div class="res-number">
    【予約番号：<?= htmlspecialchars($reservationNumber) ?>】
  </div>

  <p class="note">
    予約番号は必ずお控えください。
  </p>

  <button class="btn-home" onclick="location.href='index.php'">
    ホームページへ
  </button>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
