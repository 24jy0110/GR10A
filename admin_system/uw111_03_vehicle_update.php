<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

/* -----------------------------
   パラメータ取得
----------------------------- */
$number_plate = $_GET["number_plate"] ?? "";

if ($number_plate === "") {
    echo "<p>ナンバープレートが指定されていません。</p>";
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

/* -----------------------------
   車両情報取得
----------------------------- */
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
$stmt->execute([":num" => $number_plate]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    echo "<p>該当する車両が存在しません。</p>";
    require_once __DIR__ . "/includes/footer.php";
    exit;
}

/* -----------------------------
   未来・進行中予約チェック
----------------------------- */
$sql = "
SELECT COUNT(*)
FROM reservation
WHERE number_plate = :num
  AND state_code IN ('STC02', 'STC04')
  AND service_start_time >= NOW()
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":num" => $number_plate]);
$future_reserve_count = (int)$stmt->fetchColumn();

/* -----------------------------
   状態判定
----------------------------- */
$is_in_use_now = ($vehicle["vehicle_state"] === "運行中");
$has_future_reservation = ($future_reserve_count > 0);
$can_change_state = !$is_in_use_now && !$has_future_reservation;

/* -----------------------------
   状態一覧
----------------------------- */
$states = [
    "空車"     => "green",
    "使用停止" => "red",
    "廃車"     => "gray"
];

/* -----------------------------
   POST処理
----------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!$can_change_state) {
        header("Location: uw111_02_vehicle_detail.php?number_plate=" . urlencode($number_plate));
        exit;
    }

    $new_state = $_POST["vehicle_state"] ?? "";

    if ($new_state !== "" && array_key_exists($new_state, $states)) {

        $_SESSION["vehicle_state_edit"] = [
            "number_plate" => $number_plate,
            "old_state"    => $vehicle["vehicle_state"],
            "new_state"    => $new_state
        ];

        header("Location: uw111_03_vehicle_state_confirm.php");
        exit;
    }
}

?>

<style>
    .container {
        width: 90%;
        max-width: 900px;
        margin: 30px auto;
        font-family: "Yu Gothic", sans-serif;
    }

    h2 {
        font-size: 22px;
        margin-bottom: 15px;
    }

    .state-badge {
        display: inline-block;
        padding: 6px 14px;
        color: #fff;
        border-radius: 6px;
        font-weight: bold;
    }

    .radio-option {
        margin: 12px 0;
    }

    .radio-option label {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 6px;
        transition: 0.2s;
    }

    .radio-option label:hover {
        background: #f5f5f5;
    }

    .radio-btn {
        margin-right: 10px;
    }

    .label-green {
        background: #4caf50;
        padding: 4px 10px;
        border-radius: 4px;
        color: #fff;
    }

    .label-red {
        background: #d9534f;
        padding: 4px 10px;
        border-radius: 4px;
        color: #fff;
    }

    .label-gray {
        background: #777;
        padding: 4px 10px;
        border-radius: 4px;
        color: #fff;
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
        border: none;
    }

    .back-btn {
        margin-left: 20px;
        padding: 12px 20px;
        border: 1px solid #333;
        background: #fff;
        text-decoration: none;
    }
</style>

<div class="container">

    <h2>
        車両状態：
        <span class="state-badge" style="background:
<?= ($vehicle["vehicle_state"] === "空車") ? "#4caf50" : (($vehicle["vehicle_state"] === "使用停止") ? "#d9534f" : (($vehicle["vehicle_state"] === "廃車") ? "#777" : "#555")) ?>">
            <?= htmlspecialchars($vehicle["vehicle_state"]) ?>
        </span>
    </h2>

    <?php if ($is_in_use_now): ?>
        <p style="color:red;">
            ※ 現在この車両は運行中のため、状態を変更できません。
        </p>
    <?php elseif ($has_future_reservation): ?>
        <p style="color:red;">
            ※ 今後の予約に割り当てられています。<br>
            先に予約の車両変更を行ってください。
        </p>
    <?php endif; ?>

    <form method="post">

        <?php foreach ($states as $state => $color): ?>
            <div class="radio-option">
                <label>
                    <input type="radio"
                        class="radio-btn"
                        name="vehicle_state"
                        value="<?= $state ?>"
                        <?= ($vehicle["vehicle_state"] === $state) ? "checked" : "" ?>
                        <?= $can_change_state ? "" : "disabled" ?>>
                    <span class="label-<?= $color ?>"><?= $state ?></span>
                </label>
            </div>
        <?php endforeach; ?>

        <div class="notice">
            ※ 「未使用」かつ「今後の予約なし」の場合のみ変更可能です。
        </div>

        <div class="btn-area">
            <?php if ($can_change_state): ?>
                <button class="update-btn" type="submit">状態を更新する</button>
            <?php else: ?>
                <button class="update-btn" type="button" disabled style="opacity:0.5;">
                    状態を更新する
                </button>
            <?php endif; ?>

            <a class="back-btn" href="javascript:history.back();">戻る</a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>