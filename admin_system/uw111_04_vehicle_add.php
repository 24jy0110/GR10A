<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

$sql = "SELECT car_model_code, car_model_name, car_model_capacity
        FROM car_model
        ORDER BY car_model_code";
$stmt = $pdo->query($sql);
$car_models = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ------------------------------
   下拉：营业所
------------------------------ */
$sql = "SELECT sales_office_code, sales_office_name
        FROM sales_office
        ORDER BY sales_office_code";
$stmt = $pdo->query($sql);
$sales_offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";

/* ------------------------------
   注册处理（POST）
------------------------------ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $number_plate      = trim($_POST['number_plate'] ?? '');
    $car_model_code    = $_POST['car_model_code'] ?? '';
    $sales_office_code = $_POST['sales_office_code'] ?? '';

    /* ① 必填校验 */
    if ($number_plate === '' || $car_model_code === '' || $sales_office_code === '') {
        $error = "未入力の項目があります。";
    } else {
        /* ② 日本ナンバー形式チェック（无空格版） */

        $pattern = '/^[一-龯]{2,}\d{3}[あ-お]\d{2}-\d{2}$/u';

        if (!preg_match($pattern, $number_plate)) {

            $error = "ナンバープレート形式が正しくありません。
            例：品川500あ90-12
            ※空白なし・半角数字・半角ハイフン使用";
        } else {


            /* ③ 重複チェック */
            $sql = "SELECT COUNT(*) FROM vehicle WHERE number_plate = :num";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":num" => $number_plate]);
            $exists = (int)$stmt->fetchColumn();

            if ($exists > 0) {
                $error = "このナンバープレートの車両は既に登録されています。";
            } else {

                /* ④ 車種から定員取得 */
                $sql = "SELECT car_model_capacity
                        FROM car_model
                        WHERE car_model_code = :cm";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([":cm" => $car_model_code]);
                $capacity = $stmt->fetchColumn();

                if (!$capacity) {
                    $error = "車種情報の取得に失敗しました。";
                } else {

                    /* 格式 & 重複チェック通过后 */

                    $_SESSION["vehicle_add"] = [
                        "number_plate"      => $number_plate,
                        "car_model_code"    => $car_model_code,
                        "sales_office_code" => $sales_office_code
                    ];

                    header("Location: uw111_04_vehicle_confirm.php");
                    exit;
                }
            }
        }
    }
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
        font-size: 26px;
        margin-bottom: 20px;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    .form-block {
        width: 45%;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-size: 15px;
    }

    input[type="text"],
    select {
        width: 100%;
        padding: 8px;
        font-size: 15px;
        border-radius: 4px;
        border: 1px solid #aaa;
    }

    .submit-btn {
        width: 250px;
        padding: 12px;
        background: #000;
        color: #fff;
        border-radius: 6px;
        display: block;
        margin: 30px auto;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
    }

    .back-btn {
        width: 200px;
        padding: 10px;
        background: #fff;
        border: 1px solid #333;
        border-radius: 6px;
        margin: 10px auto;
        text-align: center;
        text-decoration: none;
        color: #000;
    }

    .error {
        color: red;
        margin-bottom: 10px;
    }
</style>

<div class="container">

    <h2>車両登録</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">

        <div class="form-row">
            <!-- 車両番号 -->
            <div class="form-block">
                <label>ナンバープレート（半角英数字と半角）</label>
                <input type="text" name="number_plate" placeholder="※ 例：品川300あ12-34（半角数字・- 使用）">
            </div>

            <!-- 車種 -->
            <div class="form-block">
                <label>車種</label>
                <select name="car_model_code">
                    <option value="">選択してください</option>
                    <?php foreach ($car_models as $cm): ?>
                        <option value="<?= $cm['car_model_code'] ?>">
                            <?= htmlspecialchars($cm['car_model_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <!-- 所属営業所 -->
            <div class="form-block">
                <label>所属営業所</label>
                <select name="sales_office_code">
                    <option value="">選択してください</option>
                    <?php foreach ($sales_offices as $so): ?>
                        <option value="<?= $so['sales_office_code'] ?>">
                            <?= htmlspecialchars($so['sales_office_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button class="submit-btn" type="submit">登録する</button>

    </form>

    <a class="back-btn" href="uw111_01_vehicle_list.php">戻る</a>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>