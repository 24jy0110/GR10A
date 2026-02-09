<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// -----------------------------
// POST 受取
// -----------------------------
$employee_id       = $_POST['employee_id'] ?? '';
$sales_office_code = $_POST['sales_office_code'] ?? '';
$languages         = $_POST['languages'] ?? [];

if (!$employee_id) {
    echo "不正なアクセスです。";
    exit;
}

// 言語は最大 3 まで念のため制限
$languages = array_slice($languages, 0, 3);

// NULL 埋め（language_id_1〜3 に対応）
$language_id_1 = $languages[0] ?? null;
$language_id_2 = $languages[1] ?? null;
$language_id_3 = $languages[2] ?? null;

// -----------------------------
// UPDATE employee (営業所更新)
// -----------------------------
$sql_emp = "
UPDATE employee
SET sales_office_code = :office
WHERE employee_id = :id
";

$stmt1 = $pdo->prepare($sql_emp);
$stmt1->bindValue(':office', $sales_office_code);
$stmt1->bindValue(':id', $employee_id);
$stmt1->execute();

// -----------------------------
// UPDATE driver (言語更新)
// -----------------------------
$sql_drv = "
UPDATE driver
SET language_id_1 = :l1,
    language_id_2 = :l2,
    language_id_3 = :l3
WHERE employee_id = :id
";

$stmt2 = $pdo->prepare($sql_drv);
$stmt2->bindValue(':l1', $language_id_1);
$stmt2->bindValue(':l2', $language_id_2);
$stmt2->bindValue(':l3', $language_id_3);
$stmt2->bindValue(':id', $employee_id);
$stmt2->execute();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー情報更新完了</title>

<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 60px;
    text-align: center;
}

h1 {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 40px;
}

.btn {
    display: inline-block;
    padding: 12px 32px;
    font-size: 18px;
    border: 2px solid #000;
    background: #fff;
    color: #000;
    text-decoration: none;
    border-radius: 6px;
}
.btn:hover {
    background: #000;
    color: #fff;
}
</style>
</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー情報の更新が完了しました</h1>

<a class="btn" href="uw112_02_driver_detail.php?employee_id=<?= htmlspecialchars($employee_id) ?>">
    ドライバー情報へ戻る
</a>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
