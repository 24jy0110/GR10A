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
.text { font-size: 18px; line-height: 1.9; margin-bottom: 35px; }
.info-box { font-size: 20px; line-height: 1.8; margin-bottom: 40px; }
.btn { padding: 12px 32px; font-size: 18px; border: 2px solid #000; background: #fff; }
.btn:hover { background: #000; color: #fff; }
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー用アカウントの登録が完了しました。</h1>

<div class="text">
ご登録いただいたメールアドレス宛に、ログインIDとパスワードを送信しました。<br>
メールが届かない場合は、迷惑メールフォルダもご確認ください。必要に応じてシステム管理者までお問い合わせください。
</div>

<div class="info-box">
■ 今後のログインについて<br>
・ドライバーは、メールに記載されたログインIDとパスワードでログインします。<br>
・パスワードを忘れた場合は、システム管理者までご連絡ください。<br><br>

<strong>社員ID：</strong><?= htmlspecialchars($employee_id) ?><br>
<strong>初期パスワード：</strong><?= htmlspecialchars($initial_password) ?>
</div>

<a href="uw112_01_driver_list.php" class="btn">ドライバー一覧へ</a>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
