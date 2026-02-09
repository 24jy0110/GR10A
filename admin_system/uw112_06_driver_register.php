<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// ---------------------------
// 年度（yyyy）
// ---------------------------
$year = date("Y");

// ---------------------------
// 次の社員番号（連番）
// ---------------------------
$stmt = $pdo->query("SELECT COUNT(*) FROM driver");
$count = $stmt->fetchColumn();
$next = $count + 1;
$next3 = sprintf("%03d", $next);

// employee_id 自動生成
$employee_id = "EMPL" . $year . "03" . $next3;

// email & 初期パスワード
$driver_email = $employee_id . "@maruwa.com";
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    return $password;
}

$initial_password = generatePassword(10);

// 営業所リスト
$office_list = $pdo->query("SELECT * FROM sales_office ORDER BY sales_office_code")->fetchAll();

// 外国語（日本語を除く）
$lang_list = $pdo->query("SELECT * FROM language_category WHERE language_category_id <> 'LCAT00' ORDER BY language_category_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>新規ドライバー登録</title>

<style>
body { font-family: "Noto Sans JP", sans-serif; margin: 40px 60px; }
h1 { font-size: 28px; font-weight: 700; margin-bottom: 25px; }
.label { width: 200px; display: inline-block; font-weight: 700; }
input[type=text], select { padding: 6px 10px; font-size: 16px; width: 250px; }
.lang-box { margin-left: 200px; }
.btn { padding: 10px 28px; font-size: 18px; border: 2px solid #000; background: #fff; }
.btn:hover { background: #000; color: #fff; }
.btn-blue { background: #1e90ff; color: #fff; border: none; padding: 10px 28px; }
.btn-blue:hover { background: #0a70d0; }
#error-msg { color: red; margin-left: 200px; font-weight: 700; }
</style>

<script>
function checkLimit() {
    const list = document.querySelectorAll(".lang-check");
    let cnt = 0;
    list.forEach(cb => { if (cb.checked) cnt++; });

    if (cnt > 3) {
        document.getElementById("error-msg").innerText = "※対応言語は最大3つまで選択できます。";
        event.preventDefault();
        return false;
    }
    return true;
}
</script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>新規ドライバー登録</h1>

<form action="uw112_07_driver_register_confirm.php" method="post" onsubmit="return checkLimit();">

    <div class="label">社員ID*：</div>
    <?= htmlspecialchars($employee_id) ?>
    <input type="hidden" name="employee_id" value="<?= $employee_id ?>"><br><br>

    <div class="label">氏名*：</div>
    <input type="text" name="driver_name" required><br><br>

    <div class="label">氏名（カナ）*：</div>
    <input type="text" name="driver_name_kana" required><br><br>

    <div class="label">所属営業所：</div>
    <select name="sales_office_code" required>
        <?php foreach ($office_list as $o): ?>
            <option value="<?= $o['sales_office_code'] ?>">
                <?= htmlspecialchars($o['sales_office_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <div class="label">対応言語：</div><br>
    <div class="lang-box">
        <?php foreach ($lang_list as $l): ?>
            <label>
                <input type="checkbox" class="lang-check"
                    name="languages[]" value="<?= $l['language_category_id'] ?>">
                <?= htmlspecialchars($l['language_category_name']) ?>
            </label><br>
        <?php endforeach; ?>
    </div>

    <div id="error-msg"></div><br>

    <div class="label">メールアドレス：</div>
    <?= htmlspecialchars($driver_email) ?>
    <input type="hidden" name="driver_email" value="<?= $driver_email ?>"><br><br>

    <div class="label">初期パスワード：</div>
    <?= htmlspecialchars($initial_password) ?>
    <input type="hidden" name="initial_password" value="<?= $initial_password ?>"><br><br>

    <button type="submit" class="btn-blue">確認する</button>
    <a href="uw112_01_driver_list.php" class="btn">戻る</a>

</form>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
