<?php
$error_message = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] === "id") {
        $error_message = "入力された社員IDは登録されていません。";
    } elseif ($_GET['error'] === "password") {
        $error_message = "パスワードが正しくありません。";
    } else {
        $error_message = "入力内容に誤りがあります。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>内部管理システム | ログイン</title>

    <!-- 必要なCSS -->
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/login.css">

</head>
<body>

    <!-- ログイン画面専用ヘッダー -->
    <?php include "header.php"; ?>

    <!-- ログインフォーム -->
    <div class="login-container">

        <h2 class="title">内部管理システム</h2>

        <form action="login_process.php" method="POST">

            <label class="label">社員ID</label>
            <input type="text" name="employee_id" class="input-field" required>

            <label class="label">パスワード</label>
            <input type="password" name="password" class="input-field" required>

            <!-- エラー文のスペースを固定する wrapper -->
            <div class="error-wrapper">
                <?php if ($error_message !== ""): ?>
                    <p class="error"><?= $error_message ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="login-btn">ログイン</button>
        </form>

        <p class="notice">
            社員ID または パスワードを忘れた場合は、システム管理者へお問い合わせください。
        </p>

    </div>

</body>
</html>
