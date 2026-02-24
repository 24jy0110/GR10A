<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// ---------------------------
// POST 受取
// ---------------------------
$employee_id = $_POST['employee_id'] ?? '';
$sales_office_code = $_POST['sales_office_code'] ?? '';
$languages = $_POST['languages'] ?? [];  // 外国語のみ（LCAT00なし）
$driver_status = $_POST['driver_status'] ?? '在職';

if (!$employee_id) {
    echo "不正なアクセスです。";
    exit;
}

// 言語は最大3つまで（念のため再チェック）
$languages = array_slice($languages, 0, 3);

// ---------------------------
// ドライバー基本情報 再取得
// ---------------------------
$sql = "
SELECT e.employee_id, e.employee_name, e.employee_name_kana,
       so.sales_office_name, d.driver_email
FROM employee e
JOIN driver d ON e.employee_id = d.employee_id
JOIN sales_office so ON e.sales_office_code = so.sales_office_code
WHERE e.employee_id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $employee_id);
$stmt->execute();
$driver = $stmt->fetch();

if (!$driver) {
    echo "ドライバー情報が取得できません。";
    exit;
}

// ---------------------------
// 営業所名の取得（新しい値）
// ---------------------------
$stmt2 = $pdo->prepare("SELECT sales_office_name FROM sales_office WHERE sales_office_code = :code");
$stmt2->bindValue(':code', $sales_office_code);
$stmt2->execute();
$new_office_name = $stmt2->fetchColumn();

// ---------------------------
// 言語名の取得
// ---------------------------
$lang_names = [];
if (!empty($languages)) {
    $in = str_repeat('?,', count($languages) - 1) . '?';
    $sql_lang = "SELECT language_category_name FROM language_category WHERE language_category_id IN ($in)";
    $stmt3 = $pdo->prepare($sql_lang);
    $stmt3->execute($languages);
    $lang_names = $stmt3->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ドライバー情報更新確認</title>

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

        .detail-box {
            line-height: 2.0;
            font-size: 18px;
        }

        .label {
            font-weight: 700;
            display: inline-block;
            width: 200px;
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

    <h1>ドライバー情報更新確認</h1>

    <div class="detail-box">

        <div>
            <span class="label">社員ID：</span>
            <?= htmlspecialchars($driver['employee_id']) ?>
        </div>

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
            <?= htmlspecialchars($new_office_name) ?>
        </div>
        <div>
            <span class="label">在籍状況：</span>
            <?= htmlspecialchars($driver_status) ?>
        </div>

        <div>
            <span class="label">対応言語：</span>
            <?= $lang_names ? implode(" / ", $lang_names) : "なし" ?>
        </div>

    </div>

    <!-- ★ Hidden Fields for Final Submit ★ -->
    <form action="uw116_03_driver_edit_done.php" method="post">

        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>">
        <input type="hidden" name="sales_office_code" value="<?= htmlspecialchars($sales_office_code) ?>">
        <input type="hidden" name="driver_status" value="<?= htmlspecialchars($driver_status) ?>">
        <?php foreach ($languages as $l): ?>
            <input type="hidden" name="languages[]" value="<?= htmlspecialchars($l) ?>">
        <?php endforeach; ?>

        <button type="submit" class="btn-blue">更新する</button>
        <a class="btn" href="uw116_01_driver_edit.php?employee_id=<?= $employee_id ?>">戻る</a>
    </form>



    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>

</html>