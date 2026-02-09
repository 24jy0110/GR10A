<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r']) || !isset($_GET['car'])) {
    header("Location: uw113_01_reservation_list.php");
    exit;
}

$resNo = $_GET['r'];
$newCar = $_GET['car'];

/* ---------------------------------------------------
   新しい車両の車種コードを取得
--------------------------------------------------- */
$sql = "
SELECT car_model_code
FROM vehicle
WHERE number_plate = :plate
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":plate" => $newCar]);
$newModel = $stmt->fetchColumn();

if (!$newModel) {
    die("選択した車両が存在しません。");
}

/* ---------------------------------------------------
   更新処理：予約テーブルに反映
--------------------------------------------------- */
$update_sql = "
UPDATE reservation
SET number_plate = :plate,
    car_model_code = :model
WHERE reservation_number = :no
";
$update_stmt = $pdo->prepare($update_sql);
$update_stmt->execute([
    ":plate" => $newCar,
    ":model" => $newModel,
    ":no"    => $resNo
]);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>配車変更完了 | 丸和交通</title>

<style>
body {
    font-family:"Noto Sans JP",sans-serif;
    background:#f5f5f5;
}
.container {
    max-width:700px;
    margin:60px auto;
    background:#fff;
    padding:40px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.title {
    font-size:26px;
    font-weight:bold;
    margin-bottom:20px;
}
.msg {
    font-size:18px;
    margin-bottom:35px;
}
.btn {
    display:inline-block;
    padding:12px 28px;
    margin:8px;
    border-radius:6px;
    font-size:16px;
    text-decoration:none;
    color:#fff;
}
.btn-detail { background:#0A84FF; }
.btn-list   { background:#555; }
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
