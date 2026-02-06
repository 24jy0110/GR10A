<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';

$error = '';

/* ------------------------------
   POST処理（ログイン試行）
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id = $_POST['employee_id'] ?? '';
    $password    = $_POST['password'] ?? '';

    if ($employee_id === '' || $password === '') {
        $error = "社員ID または パスワードが未入力です。";
    } else {

        // DBから employee を取得
        $sql = "SELECT employee_id, employee_name, sales_office_code, password
                FROM employee 
                WHERE employee_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $employee_id, PDO::PARAM_STR);
        $stmt->execute();
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$emp || $emp['password'] !== $password) {
            $error = "社員ID または パスワードが違います。";
        } else {

            /* ------------------------------
               🔥 古い形式のセッション削除
            ------------------------------ */
            unset($_SESSION['employee_id']);
            unset($_SESSION['employee_name']);
            unset($_SESSION['job_code']);

            /* ------------------------------
               新形式でログイン情報保存
            ------------------------------ */
            $job_code = substr($employee_id, 8, 2);

            $_SESSION['employee'] = [
                'employee_id'       => $emp['employee_id'],
                'employee_name'     => $emp['employee_name'],
                'sales_office_code' => $emp['sales_office_code'],
                'job_code'          => $job_code
            ];

            /* ------------------------------
               職種別TOPへ遷移
            ------------------------------ */
            switch ($job_code) {
                case '01': header("Location: uw100.php"); break; // 受付
                case '02': header("Location: uw110.php"); break; // 配車
                case '03': header("Location: uw120.php"); break; // ドライバー
                default:
                    $error = "不正な職種コードです。";
                    break;
            }

            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン | 丸和交通</title>

<style>
body {
    font-family:'Noto Sans JP', sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:0;
}
.login-container {
    width:380px;
    margin:120px auto;
    padding:40px 30px;
    background:#fff;
    border:1px solid #ddd;
    border-radius:10px;
    text-align:center;
}

.login-container h2 {
    font-size:24px;
    font-weight:700;
    margin-bottom:30px;
}

.login-container input {
    width:90%;
    padding:12px;
    border:1px solid #bbb;
    border-radius:5px;
    margin-bottom:15px;
    font-size:15px;
}

.login-container button {
    width:95%;
    padding:14px;
    background:#000;
    color:#fff;
    border:none;
    border-radius:6px;
    font-size:17px;
    cursor:pointer;
    font-weight:600;
}

.error {
    color:#d60000;
    font-size:14px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="login-container">
    <h2>社員ログイン</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="employee_id" placeholder="社員ID" required>
        <input type="password" name="password" placeholder="パスワード" required>
        <button type="submit">ログイン</button>
    </form>
</div>

</body>
</html>
