<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

$employee_id      = $_POST['employee_id'];
$driver_name      = $_POST['driver_name'];
$driver_name_kana = $_POST['driver_name_kana'];
$sales_office_code= $_POST['sales_office_code'];
$driver_email     = $_POST['driver_email'];
$initial_password = $_POST['initial_password'];
$languages        = $_POST['languages'] ?? [];

$languages = array_slice($languages, 0, 3);
$l1 = $languages[0] ?? null;
$l2 = $languages[1] ?? null;
$l3 = $languages[2] ?? null;

try {
    $pdo->beginTransaction();

    // employee 登録
    $stmt1 = $pdo->prepare("
        INSERT INTO employee (
            employee_id, employee_name, employee_name_kana, sales_office_code, password
        ) VALUES (
            :id, :name, :kana, :office, :pass
        )
    ");
    $stmt1->execute([
        ':id' => $employee_id,
        ':name' => $driver_name,
        ':kana' => $driver_name_kana,
        ':office' => $sales_office_code,
        ':pass' => $initial_password
    ]);

    // driver 登録
    $stmt2 = $pdo->prepare("
        INSERT INTO driver (
            employee_id, driver_email, language_id_1, language_id_2, language_id_3
        ) VALUES (
            :id, :email, :l1, :l2, :l3
        )
    ");
    $stmt2->execute([
        ':id' => $employee_id,
        ':email' => $driver_email,
        ':l1' => $l1,
        ':l2' => $l2,
        ':l3' => $l3
    ]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー登録完了</title>

<style>

body { font-family: "Noto Sans JP", sans-serif; margin: 60px 80px; }

h1 { font-size: 26px; font-weight: 700; margin-bottom: 20px; }

.text {
    font-size: 18px;
    line-height: 1.9;
    margin-bottom: 25px;
}


.alert {
    color: #c40000;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 30px;
}


.info-box {
    font-size: 20px;
    line-height: 2;
    padding: 20px 25px;
    border: 2px solid #000;
    background: #fafafa;
    margin-bottom: 40px;
}

.info-box span.label {
    display: inline-block;
    width: 160px;
    font-weight: bold;
}

.btn {
    padding: 12px 32px;
    font-size: 18px;
    border: 2px solid #000;
    background: #fff;
    text-decoration: none;
    color: #000;
}
.btn:hover { background: #000; color: #fff; }

</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー用アカウントの登録が完了しました。</h1>

<div class="text">
ログインIDとパスワードを発行しました。
</div>

<div class="alert">
※ この画面は一度閉じると再表示できません。<br>
※ 以下の情報を必ず控え、速やかにドライバーへお伝えください。
</div>

<div class="info-box">
    <div>
        <span class="label">社員ID</span>
        <?= htmlspecialchars($employee_id) ?>
    </div>
    <div>
        <span class="label">初期パスワード</span>
        <?= htmlspecialchars($initial_password) ?>
    </div>
</div>


<a href="uw112_01_driver_list.php" class="btn">ドライバー一覧へ</a>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
