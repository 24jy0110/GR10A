<?php
session_start();
include("../includes/db_connect.php");


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

try {
 
    $officeSql = "SELECT DISTINCT office FROM employees ORDER BY office";
    $officeStmt = $pdo->query($officeSql);
    $offices = $officeStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ドライバー新規登録</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .msg-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .msg-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input[type="text"], .form-group input[type="password"], .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .note { font-size: 12px; color: #888; margin-top: 5px; }
        .btn-area { margin-top: 30px; display: flex; justify-content: space-between; gap: 20px; }
        .btn { padding: 12px 25px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid transparent; }
        .btn-back { background: #ccc; color: #000; border-color: #ccc; width: 30%; }
        .btn-submit { background: #000; color: #fff; width: 70%; }
    </style>
</head>
<body>

<div class="container">
    <h1>ドライバー新規登録</h1>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'error_required'): ?>
            <div class="msg msg-error">エラー：名前、営業所、パスワードは必須項目です。</div>
        <?php elseif ($_GET['msg'] == 'error_db' || $_GET['msg'] == 'error_id_gen'): ?>
            <div class="msg msg-error">データベースエラー、または社員IDの生成に失敗しました。</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <form action="driver_process.php" method="POST">
        <input type="hidden" name="action" value="register">
        
        <div class="form-group">
            <label for="office">所属営業所 *</label>
            <select id="office" name="office" required>
                <option value="">-- 選択してください --</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo htmlspecialchars($office); ?>">
                        <?php echo htmlspecialchars($office); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="note">社員ID (Emp_ID) は選択した営業所に基づいて自動生成されます。</div>
        </div>

        <div class="form-group">
            <label for="name">氏名 (乗務員名前) *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="language_support">言語対応</label>
            <input type="text" id="language_support" name="language_support" placeholder="例: 日本語/英語, 日本語のみ">
            <div class="note">一覧表示で利用されます。</div>
        </div>
        
        <div class="form-group">
            <label for="password">初期パスワード *</label>
            <input type="password" id="password" name="password" required>
            <div class="note">初期パスワードを登録してください。</div>
        </div>

        <div class="btn-area">
            <a href="driver_list.php" class="btn btn-back">一覧に戻る</a>
            <button type="submit" class="btn btn-submit">ドライバーを登録</button>
        </div>
    </form>
</div>

</body>
</html>