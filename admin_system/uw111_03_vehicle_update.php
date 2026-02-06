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
        cm.car_model_name,
        cm.car_model_capacity,
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
   状態一覧（画面表示順）
------------------------------------------ */
$states = [
    "空車" => "green",
    "使用停止" => "red",
    "廃車" => "gray"
];

/* -----------------------------------------
   運行中は変更不可
------------------------------------------ */
$is_running = ($vehicle["vehicle_state"] === "運行中");

/* -----------------------------------------
   POST処理（更新）
------------------------------------------ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$is_running) {

    $new_state = $_POST["vehicle_state"] ?? "";

    if ($new_state !== "") {
        $sql = "UPDATE vehicle SET vehicle_state = :st WHERE number_plate = :num";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":st", $new_state);
        $stmt->bindValue(":num", $number_plate);
        $stmt->execute();
    }

    header("Location: uw111_02_vehicle_detail.php?number_plate=" . urlencode($number_plate));
    exit;
}
?>

<style>
.container {
    width: 90%;
    max-width: 900px;
    margin: 20px auto;
    font-family: "Yu Gothic", sans-serif;
}

h2 {
    font-size: 22px;
    margin-bottom: 15px;
}

.state-badge {
    display: inline-block;
    padding: 5px 12px;
    background: green;
    color: #fff;
    border-radius: 5px;
    font-weight: bold;
}

.radio-row {
    margin: 15px 0;
}

.radio-option {
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}

.radio-btn {
    width: 20px;
    height: 20px;
    margin-right: 10px;
}

.label-green {
    background-color: #4caf50;
    padding: 4px 10px;
    border-radius: 4px;
    color: white;
}

.label-red {
    background-color: #d9534f;
    padding: 4px 10px;
    border-radius: 4px;
    color: white;
}

.label-gray {
    background-color: #777;
    padding: 4px 10px;
    border-radius: 4px;
    color: white;
}

.notice {
    margin-top: 25px;
    font-size: 13px;
    color: #555;
    line-height: 1.6em;
}

.btn-area {
    margin-top: 30px;
}

.update-btn {
    padding: 12px 20px;
    background: #000;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
}

.back-btn {
    margin-left: 20px;
    padding: 12px 20px;
    background: #fff;
    color: #333;
    border: 1px solid #333;
    border-radius: 6px;
    text-decoration: none;
}
</style>

<div class="container">

    <h2>本日の車両状態　
        <span class="state-badge" style="background:
            <?= ($vehicle["vehicle_state"] === "空車") ? "green" : (($vehicle["vehicle_state"] === "使用停止") ? "#d9534f" : "#555") ?>">
            <?= htmlspecialchars($vehicle["vehicle_state"]) ?>
        </span>
    </h2>

    <div>実際の状態を選択してください</div>

    <form method="post">

        <div class="radio-row">

            <!-- 運行中の場合は radio すべて disabled -->
            <?php if ($is_running): ?>
                <p style="margin: 15px 0; font-size:15px; color:red;">
                    ※ 現在「運行中」のため、状態を変更することはできません。
                </p>
            <?php endif; ?>

            <?php foreach ($states as $state => $color): ?>
                <div class="radio-option">
                    <input type="radio"
                           class="radio-btn"
                           name="vehicle_state"
                           value="<?= $state ?>"
                           <?= ($vehicle["vehicle_state"] === $state) ? "checked" : "" ?>
                           <?= $is_running ? "disabled" : "" ?>>
                    <span class="label-<?= $color ?>">
                        <?= $state ?>
                    </span>
                </div>
            <?php endforeach; ?>

        </div>

        <div class="notice">
            ※注意事項<br>
            現在「運行中」の場合は、状態を変更することはできません。<br>
            現在「空車」ですが、今後の予約に割り当てた場合は、状態変更時に該当予約の車両変更手続きが必要です。
        </div>

        <div class="btn-area">
            <?php if (!$is_running): ?>
                <button class="update-btn" type="submit">状態を更新する</button>
            <?php else: ?>
                <button class="update-btn" type="button" disabled style="opacity:0.5;">状態を更新する</button>
            <?php endif; ?>

            <a class="back-btn" href="javascript:history.back();">戻る</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
