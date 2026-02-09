<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// ---------------------------
// パラメータ取得
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
    so.sales_office_name,
    d.driver_email,
    lc1.language_category_name AS lang1,
    lc2.language_category_name AS lang2,
    lc3.language_category_name AS lang3
FROM employee e
JOIN driver d ON e.employee_id = d.employee_id
JOIN sales_office so ON e.sales_office_code = so.sales_office_code
LEFT JOIN language_category lc1 ON d.language_id_1 = lc1.language_category_id
LEFT JOIN language_category lc2 ON d.language_id_2 = lc2.language_category_id
LEFT JOIN language_category lc3 ON d.language_id_3 = lc3.language_category_id
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

// 言語まとめ
$languages = array_filter([$driver['lang1'], $driver['lang2'], $driver['lang3']]);
$language_text = implode(" / ", $languages);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー詳細</title>

<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 40px 60px;
    color: #000;
}
h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 25px;
}
.section-title {
    font-size: 22px;
    font-weight: 700;
    margin-top: 20px;
}
.detail-box {
    margin-top: 15px;
    font-size: 19px;
    line-height: 1.9;
}
.label {
    font-weight: 700;
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
</style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー情報詳細：<?= htmlspecialchars($driver['employee_id']) ?></h1>

<div class="detail-box">
    <span class="label">氏名：</span><?= htmlspecialchars($driver['employee_name']) ?><br>
    <span class="label">氏名（カナ）：</span><?= htmlspecialchars($driver['employee_name_kana']) ?><br>
    <span class="label">所属営業所：</span><?= htmlspecialchars($driver['sales_office_name']) ?><br>
    <span class="label">対応言語：</span><?= $language_text ?><br>
    <span class="label">メールアドレス：</span><?= htmlspecialchars($driver['driver_email']) ?><br>
</div>

<!-- 操作ボタン -->
<a class="btn-blue" href="uw112_03_driver_edit.php?employee_id=<?= $driver['employee_id'] ?>">編集する</a>
<a class="btn" href="uw112_01_driver_list.php">戻る</a>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
