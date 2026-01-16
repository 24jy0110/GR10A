<?php
session_start();
require_once 'includes/db_connect.php';

/* ===============================
   SESSION チェック
================================ */
if (empty($_SESSION['reserve'])) {
    header('Location: index.php');
    exit;
}

$res = $_SESSION['reserve'];

/* ===============================
   営業所コード対応表
================================ */
$officeMap = [
  '亀沢本社'     => 'OFCE001',
  '豊島支社'     => 'OFCE002',
  '桃井支社'     => 'OFCE003',
  '富士見町支社' => 'OFCE004',
  '本羽田支社'   => 'OFCE005',
  '中区支社'     => 'OFCE006',
];

// 前段で決定済みの営業所名
$office_name = $res['office_name'] ?? '亀沢本社';
$office_code = $officeMap[$office_name] ?? 'OFCE001';

/* ===============================
   予約番号生成
   例：20251101001
================================ */
$today = date('Ymd');

// 同日件数を取得
$stmt = $pdo->prepare(
  "SELECT COUNT(*) FROM reservations WHERE reserve_date = :d"
);
$stmt->execute([':d' => $today]);
$count = $stmt->fetchColumn() + 1;

$reserve_no = $today . str_pad($count, 3, '0', STR_PAD_LEFT);

/* ===============================
   DB 登録（仮予約）
================================ */
$sql = "
INSERT INTO reservations (
  reserve_no,
  reserve_date,
  start_date,
  start_time,
  end_date,
  people,
  car_type,
  pickup_area,
  pickup_city,
  pickup_detail,
  drop_area,
  drop_city,
  drop_detail,
  lang1,
  lang2,
  price,
  customer_name,
  customer_name_kana,
  email,
  phone,
  office_code,
  status,
  created_at
) VALUES (
  :reserve_no,
  :reserve_date,
  :start_date,
  :start_time,
  :end_date,
  :people,
  :car_type,
  :pickup_area,
  :pickup_city,
  :pickup_detail,
  :drop_area,
  :drop_city,
  :drop_detail,
  :lang1,
  :lang2,
  :price,
  :customer_name,
  :customer_name_kana,
  :email,
  :phone,
  :office_code,
  '仮予約',
  NOW()
)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':reserve_no'         => $reserve_no,
  ':reserve_date'       => $today,
  ':start_date'         => $res['start_date'],
  ':start_time'         => $res['start_time'],
  ':end_date'           => $res['end_date'],
  ':people'             => $res['people'],
  ':car_type'           => $res['car_type'],
  ':pickup_area'        => $res['pickup_area'],
  ':pickup_city'        => $res['pickup_city'],
  ':pickup_detail'      => $res['pickup_detail'],
  ':drop_area'          => $res['drop_area'],
  ':drop_city'          => $res['drop_city'],
  ':drop_detail'        => $res['drop_detail'],
  ':lang1'              => $res['lang1'],
  ':lang2'              => $res['lang2'],
  ':price'              => $res['car_price'],
  ':customer_name'      => $res['customer_name'],
  ':customer_name_kana' => $res['customer_name_kana'],
  ':email'              => $res['customer_email'],
  ':phone'              => $res['customer_phone'],
  ':office_code'        => $office_code,
]);

/* ===============================
   SESSION クリア
================================ */
unset($_SESSION['reserve']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>仮予約完了 | 丸和交通株式会社</title>
<link rel="stylesheet" href="./assets/app.css">

<style>
.container {
  max-width: 900px;
  margin: 50px auto;
  padding: 0 20px;
  text-align: center;
}

h2 {
  font-size: 26px;
  margin-bottom: 20px;
}

.reserve-no {
  font-size: 20px;
  margin: 20px 0;
}

.reserve-no span {
  color: red;
  font-weight: bold;
}

.flow {
  text-align: left;
  max-width: 700px;
  margin: 30px auto;
  font-size: 14px;
  line-height: 1.8;
}

.contact {
  text-align: left;
  max-width: 700px;
  margin: 30px auto;
  font-size: 14px;
}

.btn-home {
  margin-top: 40px;
  padding: 12px 40px;
  font-size: 16px;
  border: 1px solid #000;
  background: #fff;
  cursor: pointer;
}

.back-row {
      text-align: center;
      padding: 30px 0;
    }
</style>
</head>

<body>

<?php include("includes/header.php"); ?>

<div class="container">
<h2>仮予約を受け付けました</h2>

<p>
お客様のご入力内容で、<strong>仮予約</strong>を受け付けました。<br>
現在点ではまだ<strong style="color:red;">本予約（配車確定）ではありません</strong>。
</p>

<div class="reserve-no">
【予約番号：<span><?php echo $reserve_no; ?></span>】<br>
※必ずお控えください。
</div>

<div class="flow">
<h3>今後の流れ</h3>
<ol>
<li>弊社にて、車両およびドライバーの空き状況を確認します。</li>
<li>確認完了後、本予約可否・確定料金をメールにてご連絡します。</li>
<li>内容に問題がなければ、本予約として配車を行います。</li>
<li>出発日が近い場合やお急ぎの場合は、お電話にてお問い合わせください。</li>
</ol>
</div>

<div class="contact">
<h3>お問い合わせ</h3>
<p>
電話：03-1234-5678（8:00〜22:00）<br>
メール：support@maruwa-taxi.jp<br>
LINE公式アカウント：@maruwa-taxi
</p>
</div>

<button class="btn-home" onclick="location.href='index.php'">
ホームページへ
</button>

</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
