<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// ---------------------------
// GET パラメータ
// ---------------------------
$employee_id = $_GET['employee_id'] ?? '';

if (!$employee_id) {
    echo "社員IDが指定されていません。";
    exit;
}

// ---------------------------
// ドライバー情報取得
// ---------------------------
$sql = "
SELECT 
    e.employee_id,
    e.employee_name,
    e.employee_name_kana,
    e.sales_office_code,
    so.sales_office_name,
    d.driver_email,
    d.language_id_1,
    d.language_id_2,
    d.language_id_3
FROM employee e
JOIN driver d ON e.employee_id = d.employee_id
JOIN sales_office so ON e.sales_office_code = so.sales_office_code
WHERE e.employee_id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $employee_id, PDO::PARAM_STR);
$stmt->execute();
$driver = $stmt->fetch();

if (!$driver) {
    echo "ドライバー情報が見つかりません。";
    exit;
}

// ---------------------------
// 営業所一覧取得
// ---------------------------
$office_list = $pdo->query("SELECT sales_office_code, sales_office_name FROM sales_office ORDER BY sales_office_code")
                   ->fetchAll();

// ---------------------------
// 言語一覧取得（日本語 LCAT00 は非表示）
// ---------------------------
$lang_list = $pdo->query("SELECT * FROM language_category ORDER BY language_category_id")->fetchAll();

$current_langs = [
    $driver['language_id_1'],
    $driver['language_id_2'],
    $driver['language_id_3']
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー情報編集</title>

<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 40px 60px;
}
h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 25px;
}
.form-box {
    font-size: 18px;
    line-height: 1.9;
}
.label {
    font-weight: 700;
    display: inline-block;
    width: 180px;
}
input[type=text], select {
    padding: 6px 10px;
    font-size: 16px;
    width: 260px;
}
.lang-box {
    margin-left: 180px;
}
.btn {
    display: inline-block;
    padding: 10px 28px;
    margin-top: 30px;
    font-size: 18px;
    border: 2px solid #000;
    background: #fff;
    color: #000;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover {
    background: #000;
    color: #fff;
}
.btn-blue {
    padding: 10px 28px;
    background: #1e90ff;
    border: none;
    color: #fff;
    margin-right: 20px;
    border-radius: 6px;
}
.btn-blue:hover {
    background: #0a70d0;
}
#error-msg {
    color: red;
    font-weight: 700;
    margin-left: 180px;
    margin-top: 5px;
}
</style>

<script>
// ---------------------------
// 外国語は最大3つまで
// ---------------------------
function checkLimit() {
    const checkboxes = document.querySelectorAll(".lang-check");
    let count = 0;
    checkboxes.forEach(cb => { if (cb.checked) count++; });

    const errorMsg = document.getElementById("error-msg");

    if (count > 3) {
        errorMsg.innerText = "※対応言語は最大3つまで選択できます。（日本語は常に対応）";
        event.preventDefault();
        return false;
    }

    errorMsg.innerText = "";
    return true;
}
</script>

</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー情報編集：<?= htmlspecialchars($driver['employee_id']) ?></h1>

<form action="uw112_04_driver_edit_confirm.php" method="post" onsubmit="return checkLimit();">

<div class="form-box">

    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($driver['employee_id']) ?>">

    <div>
        <span class="label">氏名：</span>
        <?= htmlspecialchars($driver['employee_name']) ?>
    </div>

    <div>
        <span class="label">氏名（カナ）：</span>
        <?= htmlspecialchars($driver['employee_name_kana']) ?>
    </div>

    <div>
        <span class="label">メールアドレス：</span>
        <?= htmlspecialchars($driver['driver_email']) ?>
    </div>

    <div>
        <span class="label">所属営業所：</span>
        <select name="sales_office_code">
            <?php foreach ($office_list as $o): ?>
                <option value="<?= $o['sales_office_code'] ?>"
                    <?= ($driver['sales_office_code'] === $o['sales_office_code']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o['sales_office_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <span class="label">対応言語：</span>
    </div>

    <div class="lang-box">
        <?php foreach ($lang_list as $l): ?>
            <?php
            // ★ 日本語 (LCAT00) は表示しない
            if ($l['language_category_id'] === 'LCAT00') continue;

            $checked = in_array($l['language_category_id'], $current_langs) ? "checked" : "";
            ?>
            <label>
                <input type="checkbox" 
                    name="languages[]" 
                    value="<?= $l['language_category_id'] ?>" 
                    class="lang-check"
                    <?= $checked ?>
                >
                <?= htmlspecialchars($l['language_category_name']) ?>
            </label><br>
        <?php endforeach; ?>
    </div>

    <div id="error-msg"></div>

</div>

<br>
<button type="submit" class="btn-blue">確認する</button>
<a class="btn" href="uw112_02_driver_detail.php?employee_id=<?= $driver['employee_id'] ?>">戻る</a>

</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
