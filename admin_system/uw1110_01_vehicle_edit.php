<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$error = "";
$vehicle = null;

$number_plate = $_GET["number_plate"] ?? "";

if (!$number_plate) {
    header("Location: uw111_01_vehicle_list.php");
    exit;
}

/* ===============================
   車両取得
=============================== */
$stmt = $pdo->prepare("SELECT * FROM vehicle WHERE number_plate = :num");
$stmt->execute([":num" => $number_plate]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    die("車両が存在しません。");
}

/* ===============================
   状態チェック
=============================== */
$is_running = ($vehicle["vehicle_state"] === "運行中");

/* 未来予約チェック */
$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM reservation
WHERE number_plate = :num
  AND service_end_date >= CURDATE()
  AND state_code IN ('STC01','STC02','STC04')
");
$stmt->execute([":num"=>$number_plate]);
$has_future = $stmt->fetchColumn() > 0;

/* マスタ取得 */
$carModels = $pdo->query("
SELECT car_model_code, car_model_name 
FROM car_model
")->fetchAll(PDO::FETCH_ASSOC);

$offices = $pdo->query("
SELECT sales_office_code, sales_office_name 
FROM sales_office
")->fetchAll(PDO::FETCH_ASSOC);


/* ===============================
   POST処理
=============================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$is_running && !$has_future) {

    $new_plate = trim($_POST["number_plate"]);
    $car_model = $_POST["car_model_code"];
    $office    = $_POST["sales_office_code"];

    if ($new_plate === "" || $car_model === "" || $office === "") {
        $error = "未入力の項目があります。";
    } else {

        /* 日本車牌格式チェック（空白なし） */
        $pattern = '/^[^\s]{2,}\d{3}[あいうえお]\d{2}-\d{2}$/u';

        if (!preg_match($pattern, $new_plate)) {
            $error = "ナンバープレート形式が正しくありません。<br>
            例：品川500あ12-34<br>
            ※ 空白なし・半角数字・半角ハイフン使用";
        } else {

            /* 重複チェック */
            $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM vehicle
            WHERE number_plate = :new_plate
              AND number_plate <> :old_plate
            ");
            $stmt->execute([
                ":new_plate"=>$new_plate,
                ":old_plate"=>$number_plate
            ]);

            if ($stmt->fetchColumn() > 0) {
                $error = "このナンバープレートは既に登録されています。";
            } else {

                $_SESSION["vehicle_edit"] = [
                    "old_plate"=>$number_plate,
                    "number_plate"=>$new_plate,
                    "car_model_code"=>$car_model,
                    "sales_office_code"=>$office
                ];

                header("Location: uw1110_02_vehicle_edit_confirm.php");
                exit;
            }
        }
    }
}

require_once __DIR__ . "/includes/header.php";
?>

<style>
.container { max-width:900px; margin:40px auto; font-family:"Yu Gothic"; }
.card {
    background:#fff;
    padding:35px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
}

.notice {
    background:#fff3cd;
    padding:14px;
    border-radius:8px;
    margin-bottom:25px;
    color:#856404;
}

.error-box {
    background:#f8d7da;
    padding:14px;
    border-radius:8px;
    margin-bottom:20px;
    color:#721c24;
}

input, select {
    width:100%;
    padding:12px;
    margin-bottom:20px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:15px;
}

/* 禁止時の灰色表示 */
input:disabled,
select:disabled {
    background:#e9ecef;
    color:#6c757d;
    cursor:not-allowed;
    opacity:0.8;
}

.btn-left {
    background:#6c757d;
    color:#fff;
    padding:12px 30px;
    border:none;
    border-radius:8px;
    font-size:15px;
}

.btn-right {
    background:#000;
    color:#fff;
    padding:12px 30px;
    border:none;
    border-radius:8px;
    font-size:15px;
    margin-left:12px;
}

.btn-right:disabled {
    background:#999;
    cursor:not-allowed;
}
</style>

<div class="container">
<div class="card">

<h2>車両情報修正</h2>

<?php if ($is_running): ?>
<div class="notice">
※ 現在「運行中」のため、車両情報を変更できません。
</div>
<?php elseif ($has_future): ?>
<div class="notice">
※ 未来の予約が存在します。<br>
対象予約の車両変更を行ってから修正してください。
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="error-box"><?= $error ?></div>
<?php endif; ?>

<form method="post">

<label>ナンバープレート</label>
<input type="text"
       name="number_plate"
       placeholder="例：品川500あ12-34"
       value="<?= htmlspecialchars($vehicle["number_plate"]) ?>"
       <?= ($is_running || $has_future) ? "disabled" : "" ?>>

<label>車種</label>
<select name="car_model_code"
        <?= ($is_running || $has_future) ? "disabled" : "" ?>>
<?php foreach ($carModels as $cm): ?>
<option value="<?= $cm["car_model_code"] ?>"
<?= $vehicle["car_model_code"]==$cm["car_model_code"]?"selected":"" ?>>
<?= htmlspecialchars($cm["car_model_name"]) ?>
</option>
<?php endforeach; ?>
</select>

<label>所属営業所</label>
<select name="sales_office_code"
        <?= ($is_running || $has_future) ? "disabled" : "" ?>>
<?php foreach ($offices as $of): ?>
<option value="<?= $of["sales_office_code"] ?>"
<?= $vehicle["sales_office_code"]==$of["sales_office_code"]?"selected":"" ?>>
<?= htmlspecialchars($of["sales_office_name"]) ?>
</option>
<?php endforeach; ?>
</select>

<br>

<button type="button"
        class="btn-left"
        onclick="history.back();">
戻る
</button>

<?php if (!$is_running && !$has_future): ?>
<button type="submit" class="btn-right">確認</button>
<?php else: ?>
<button type="button" class="btn-right" disabled>確認</button>
<?php endif; ?>

</form>

</div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
