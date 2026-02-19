<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

$number_plate = $_GET["number_plate"] ?? "";

if ($number_plate === "") {
    echo "<p>ナンバープレートが指定されていません。</p>";
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

/* -----------------------------------------
   車両情報を取得
------------------------------------------ */
$sql = "
    SELECT 
        v.number_plate,
        v.vehicle_state,
        v.vehicle_capacity,
        cm.car_model_name,
        so.sales_office_name
    FROM vehicle v
    JOIN sales_office so ON v.sales_office_code = so.sales_office_code
    JOIN car_model cm ON v.car_model_code = cm.car_model_code
    WHERE v.number_plate = :num
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":num", $number_plate);
$stmt->execute();
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo "<p>該当する車両が存在しません。</p>";
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

/* -----------------------------------------
   以降の予約（未来予約一覧）
   ※ 包天ロジック：reservation_date > 今日 で取得
------------------------------------------ */
$sql = "
SELECT 
    reservation_number,
    reservation_date,
    service_start_time,
    service_end_date,
    customer_name
FROM reservation
WHERE number_plate = :num
  AND service_start_time > NOW()
  AND state_code IN ('STC02', 'STC04') 
ORDER BY service_start_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":num", $number_plate);
$stmt->execute();
$future_reservations = $stmt->fetchAll();

$future_count = count($future_reservations);
?>

<style>
    .container {
        width: 92%;
        max-width: 900px;
        margin: 25px auto;
        font-family: "Yu Gothic", sans-serif;
    }

    .title {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .section {
        padding: 18px;
        border: 1px solid #ccc;
        margin-bottom: 25px;
        border-radius: 6px;
    }

    .section-title {
        font-size: 18px;
        margin-bottom: 12px;
        font-weight: bold;
    }

    .info-row {
        margin-bottom: 10px;
    }

    .state-badge {
        padding: 4px 10px;
        border-radius: 5px;
        color: #fff;
    }

    .badge-green {
        background: #4caf50;
    }

    .badge-red {
        background: #d9534f;
    }

    .badge-gray {
        background: #777;
    }

    .badge-blue {
        background: #0275d8;
    }

    .detail-btn {
        padding: 6px 12px;
        background: #0275d8;
        color: #fff;
        border-radius: 5px;
        text-decoration: none;
        font-size: 13px;
    }

    .back-btn {
        padding: 12px 20px;
        background: #fff;
        color: #333;
        border: 1px solid #333;
        border-radius: 6px;
        text-decoration: none;
    }

    .state-update-btn {
        padding: 12px 20px;
        background: #000;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 15px;
    }

    .edit-btn {
        padding: 12px 20px;
        background: #0A84FF;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 15px;
    }
</style>

<div class="container">

    <div class="title">車両詳細：<?= htmlspecialchars($vehicle["number_plate"]) ?></div>

    <!-- 基本情報 -->
    <div class="section">
        <div class="section-title">基本情報</div>

        <div class="info-row">営業所：<?= htmlspecialchars($vehicle["sales_office_name"]) ?></div>
        <div class="info-row">車種：<?= htmlspecialchars($vehicle["car_model_name"]) ?></div>
        <div class="info-row">定員：<?= htmlspecialchars($vehicle["vehicle_capacity"]) ?> 名</div>

        <div class="info-row">
            状態：
            <?php
            $state = $vehicle["vehicle_state"];
            $color = "gray";
            if ($state === "空車") $color = "green";
            if ($state === "使用停止") $color = "red";
            if ($state === "運行中") $color = "blue";
            ?>
            <span class="state-badge badge-<?= $color ?>">
                <?= htmlspecialchars($state) ?>
            </span>
        </div>
    </div>

    <!-- 以降の予約 -->
    <div class="section">
        <div class="section-title">以降の予約（<?= $future_count ?>件）</div>

        <?php if ($future_count === 0): ?>
            <div>現在、今後の予約はありません。</div>

        <?php else: ?>

            <?php foreach ($future_reservations as $res): ?>

                <div class="info-row" style="margin-bottom:12px;">

                    <?= htmlspecialchars($res["service_start_time"]) ?>


                    &nbsp;&nbsp;

                    <a class="detail-btn"
                        href="uw112_01_reservation_detail.php?reservation_number=<?= urlencode($res['reservation_number']) ?>">
                        詳細
                    </a>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>
    </div>

  
    <div>
        <a class="state-update-btn"
            href="uw113_01_vehicle_update.php?number_plate=<?= urlencode($number_plate) ?>">
            車両状態を更新する
        </a>
        <a class="edit-btn"
            href="uw1110_01_vehicle_edit.php?number_plate=<?= urlencode($number_plate) ?>">
            車両情報を修正する
        </a>

        <a class="back-btn" href="uw111_01_vehicle_list.php">戻る</a>
    </div>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>