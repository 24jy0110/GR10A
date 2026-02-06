<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

/* ------------------------------
   下拉：车型
------------------------------ */
$sql = "SELECT car_model_code, car_model_name, car_model_capacity 
        FROM car_model ORDER BY car_model_code";
$stmt = $pdo->query($sql);
$car_models = $stmt->fetchAll();

/* ------------------------------
   下拉：营业所
------------------------------ */
$sql = "SELECT sales_office_code, sales_office_name 
        FROM sales_office ORDER BY sales_office_code";
$stmt = $pdo->query($sql);
$sales_offices = $stmt->fetchAll();

/* ------------------------------
   注册处理（POST）
------------------------------ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $number_plate = $_POST['number_plate'] ?? '';
    $car_model_code = $_POST['car_model_code'] ?? '';
    $sales_office_code = $_POST['sales_office_code'] ?? '';

    // 基本验证（必填）
    if ($number_plate === '' || $car_model_code === '' || $sales_office_code === '') {
        $error = "未入力の項目があります。";
    } else {

        /* 从车型表自动取定员 */
        $sql = "SELECT car_model_capacity 
                FROM car_model 
                WHERE car_model_code = :cm";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":cm", $car_model_code);
        $stmt->execute();
        $capacity = $stmt->fetchColumn();

        if (!$capacity) {
            $error = "車種情報の取得に失敗しました。";
        } else {

            /* 插入 vehicle 表 */
            $sql = "INSERT INTO vehicle 
                    (number_plate, vehicle_capacity, vehicle_state, sales_office_code, car_model_code)
                    VALUES 
                    (:num, :cap, '空車', :so, :cm)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":num", $number_plate);
            $stmt->bindValue(":cap", $capacity);
            $stmt->bindValue(":so", $sales_office_code);
            $stmt->bindValue(":cm", $car_model_code);

            $stmt->execute();

            // 跳到完了页面
            header("Location: uw111_05_vehicle_add_done.php");
            exit;
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

input[type="text"], select {
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
                <label>ナンバープレート</label>
                <input type="text" name="number_plate" placeholder="例）品川 300 あ 12-34">
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
