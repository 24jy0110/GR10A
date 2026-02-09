<?php
session_start();
require_once __DIR__ . '/includes/check_login.php';

// ------------------------------------
// POST データ受け取り
// ------------------------------------
$employee_id        = $_POST['employee_id'] ?? '';
$driver_name        = $_POST['driver_name'] ?? '';
$driver_name_kana   = $_POST['driver_name_kana'] ?? '';
$sales_office_code  = $_POST['sales_office_code'] ?? '';
$languages          = $_POST['languages'] ?? [];
$driver_email       = $_POST['driver_email'] ?? '';
$initial_password   = $_POST['initial_password'] ?? '';

// 営業所名取得
require_once __DIR__ . '/includes/db_connect.php';
$stmt = $pdo->prepare("SELECT sales_office_name FROM sales_office WHERE sales_office_code = :code");
$stmt->bindValue(':code', $sales_office_code, PDO::PARAM_STR);
$stmt->execute();
$office = $stmt->fetch();
$sales_office_name = $office ? $office['sales_office_name'] : '';

// 言語名取得
$language_names = [];
if (!empty($languages)) {
    $in = implode(",", array_fill(0, count($languages), "?"));
    $sql = "SELECT language_category_name FROM language_category WHERE language_category_id IN ($in)";
    $stmt2 = $pdo->prepare($sql);
    foreach ($languages as $i => $lid) {
        $stmt2->bindValue($i + 1, $lid, PDO::PARAM_STR);
    }
    $stmt2->execute();
    $rows = $stmt2->fetchAll();
    foreach ($rows as $r) {
        $language_names[] = $r['language_category_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー登録確認</title>
<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 40px 60px;
}

h1 {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 30px;
}

.data-box {
    margin-top: 20px;
    font-size: 18px;
    line-height: 2.2;
}

/* ---- ボタン配置 ---- */
.button-area {
    margin-top: 50px;
    display: flex;
    justify-content: center;
    gap: 40px;
}

.btn-blue {
    padding: 12px 40px;
    background: #1e90ff;
    border: none;
    color: #fff;
    font-size: 18px;
    border-radius: 5px;
}
.btn-blue:hover {
    background: #0a70d0;
}

.btn-white {
    padding: 12px 40px;
    background: #fff;
    border: 2px solid #000;
    font-size: 18px;
    border-radius: 5px;
    text-decoration: none;
    color: #000;
}
.btn-white:hover {
    background: #000;
    color: #fff;
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー登録確認画面</h1>

<div class="data-box">
    <div>社員ID：<?= htmlspecialchars($employee_id) ?></div>
    <div>氏名：<?= htmlspecialchars($driver_name) ?></div>
    <div>氏名（フリガナ）：<?= htmlspecialchars($driver_name_kana) ?></div>
    <div>所属営業所：<?= htmlspecialchars($sales_office_name) ?></div>
    <div>対応言語：
        <?= empty($language_names) ? 'なし' : implode(" / ", $language_names) ?>
    </div>
    <div>メールアドレス：<?= htmlspecialchars($driver_email) ?></div>
</div>

<form action="uw112_08_driver_register_done.php" method="post">

    <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
    <input type="hidden" name="driver_name" value="<?= htmlspecialchars($driver_name) ?>">
    <input type="hidden" name="driver_name_kana" value="<?= htmlspecialchars($driver_name_kana) ?>">
    <input type="hidden" name="sales_office_code" value="<?= $sales_office_code ?>">
    <input type="hidden" name="driver_email" value="<?= htmlspecialchars($driver_email) ?>">
    <input type="hidden" name="initial_password" value="<?= htmlspecialchars($initial_password) ?>">

    <?php foreach ($languages as $l): ?>
        <input type="hidden" name="languages[]" value="<?= htmlspecialchars($l) ?>">
    <?php endforeach; ?>

    <div class="button-area">
        <button type="submit" class="btn-blue">登録する</button>
        <a href="uw112_06_driver_register.php" class="btn-white">戻る</a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
