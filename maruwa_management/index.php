<?php
/* ---------------------------------------------------------
   エラーメッセージ制御
   error=id        → 社員IDが存在しない
   error=password  → パスワードが一致しない
   --------------------------------------------------------- */
$error_message = "";

if (isset($_GET['error'])) {

    if ($_GET['error'] === "id") {
        $error_message = "入力された社員IDは登録されていません。";
    }

    elseif ($_GET['error'] === "password") {
        $error_message = "パスワードが正しくありません。";
    }

    else {
        $error_message = "入力内容に誤りがあります。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>内部管理システム | ログイン</title>

    <!-- ログイン画面専用CSS -->
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <!-- 共通ヘッダー（ログイン画面用） -->
    <?php include "header.php"; ?>

    <div class="login-container">

        <!-- 画面タイトル -->
        <h2 class="title">内部管理システム</h2>

        <!-- ログインフォーム -->
        <form action="login_process.php" method="POST">

            <!-- 社員ID入力欄 -->
            <label class="label">社員ID</label>
            <input
                type="text"
                name="employee_id"
                class="input-field"
                placeholder="例：EMPL202401001"
                required
            >

            <!-- パスワード入力欄 -->
            <label class="label">パスワード</label>
            <input
                type="password"
                name="password"
                class="input-field"
                placeholder="パスワードを入力"
                required
            >

            <!-- ログインエラー（失敗時のみ表示） -->
            <?php if ($error_message !== ""): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <!-- ログインボタン -->
            <button type="submit" class="login-btn">ログイン</button>
        </form>

        <!-- 案内文 -->
        <p class="notice">
            社員ID または パスワードを忘れた場合は、システム管理者へお問い合わせください。
        </p>
    </div>

</body>
</html>
