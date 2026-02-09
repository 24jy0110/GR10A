<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET: reservation number
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw113_01_reservation_list.php");
    exit;
}
$resNo = $_GET['r'];

/* ---------------------------------------------------
   予約情報取得（车种固定）
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    cm.car_model_name,
    cm.car_model_capacity,
    so.sales_office_name
FROM reservation r
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
LEFT JOIN sales_office so ON so.sales_office_code = r.sales_office_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("予約データが見つかりません。");
}

$office_code = $res["sales_office_code"];
$car_model_code = $res["car_model_code"]; // ★固定车种！
$start_date  = $res["service_start_time"];
$end_date    = $res["service_end_date"];

/* ---------------------------------------------------
   同じ車種の空車のみ取得
--------------------------------------------------- */
$sql_vehicle = "
SELECT 
    v.number_plate,
    v.car_model_code,

    (
        SELECT COUNT(*)
        FROM reservation r2
        WHERE r2.number_plate = v.number_plate
          AND r2.reservation_number != :res_no
          AND r2.service_start_time <= :end_date
          AND r2.service_end_date >= :start_date
    ) AS used_count

FROM vehicle v
WHERE v.sales_office_code = :office
  AND v.car_model_code = :model
  AND ( :current_plate IS NULL OR v.number_plate <> :current_plate )
ORDER BY v.number_plate

";

$stmt2 = $pdo->prepare($sql_vehicle);
$stmt2->execute([
    ":office"        => $office_code,
    ":model"         => $car_model_code,
    ":res_no"        => $resNo,
    ":start_date"    => $start_date,
    ":end_date"      => $end_date,
    ":current_plate" => $res["number_plate"]
]);


$vehicles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>配車変更（空車一覧） | 丸和交通</title>

    <style>
        body {
            font-family: "Noto Sans JP", sans-serif;
            background: #f5f5f5;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .table-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table-list th,
        .table-list td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        .table-list th {
            background: #f0f0f0;
        }

        .btn-select {
            padding: 8px 18px;
            background: #0A84FF;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-back {
            margin-top: 25px;
            display: inline-block;
            padding: 10px 24px;
            background: #555;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        .unavailable {
            color: #d32f2f;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container">

        <h1 class="section-title">配車変更（空車一覧）</h1>

        <p><b>予約番号：</b><?= htmlspecialchars($resNo) ?></p>
        <p><b>現在の車種：</b><?= htmlspecialchars($res["car_model_name"]) ?></p>
        <p>
            <b>現在の配車：</b>
            <?php if (!empty($res["number_plate"])): ?>
                <?= htmlspecialchars($res["number_plate"]) ?>
            <?php else: ?>
                未配車
            <?php endif; ?>
        </p>
        <p><b>営業所：</b><?= htmlspecialchars($res["sales_office_name"]) ?></p>

        <table class="table-list">
            <tr>
                <th>ナンバープレート</th>
                <th>状態</th>
                <th>操作</th>
            </tr>

            <?php foreach ($vehicles as $v): ?>
                <?php
                if ($v["used_count"] > 0) {
                    continue; // ★ 使用中の車両は表示しない
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($v["number_plate"]) ?></td>
                    <td>
                        <span style="color:green;font-weight:bold;">空車</span>
                    </td>
                    <td>
                        <a class="btn-select"
                            href="uw113_04_change_vehicle_confirm.php?r=<?= $resNo ?>&car=<?= urlencode($v["number_plate"]) ?>">
                            選択
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>


        </table>

        <a href="uw113_02_reservation_detail.php?r=<?= $resNo ?>" class="btn-back">戻る</a>

    </div>

</body>

</html>