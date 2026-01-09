<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>丸和交通 - ログイン</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #222;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>社員ログイン</h2>
        <?php
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'auth_failed') echo "<p class='error'>IDまたはパスワードが違います。</p>";
            if ($_GET['error'] == 'empty') echo "<p class='error'>全て入力してください。</p>";
            if ($_GET['error'] == 'role_error') echo "<p class='error'>職種権限エラーです。管理者に連絡してください。</p>";
            if ($_GET['error'] == 'db_error') echo "<p class='error'>システムエラーが発生しました。</p>";
        }
        ?>
        <form action="login_process.php" method="POST">
            <input type="text" name="employee_id" placeholder="社員ID (EMPL...)" required>
            <input type="password" name="password" placeholder="パスワード" required>
            <button type="submit">ログイン</button>
        </form>
    </div>
</body>

</html>