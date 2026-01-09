<?php
session_start();
include("../includes/db_connect.php");

// 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: driver_list.php");
    exit;
}

try {
    // 1. 获取司机详情
    $driverSql = "
        SELECT 
            id, emp_id, name, office, language_support
        FROM employees 
        WHERE id = ? AND role = 'driver'
    ";
    $driverStmt = $pdo->prepare($driverSql);
    $driverStmt->execute([$id]);
    $driver = $driverStmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) { exit("指定されたドライバーが見つかりませんでした。"); }
    
    // 2. 获取所有可能的营业所列表 (用于编辑时的下拉选择)
    $officeSql = "SELECT DISTINCT office FROM employees ORDER BY office";
    $officeStmt = $pdo->query($officeSql);
    $offices = $officeStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// 定义默认密码常量
const DEFAULT_PASSWORD = '123456';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ドライバー情報詳細・編集</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .msg-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .msg-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .section { margin-top: 30px; border: 1px solid #eee; padding: 20px; border-radius: 6px; }
        .section h2 { border-bottom: 1px dashed #ccc; padding-bottom: 5px; margin-top: 0; font-size: 18px; color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input[type="text"], .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .password-reset { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px; text-align: center; }
        .password-reset p { margin-bottom: 15px; font-weight: bold; }
        .btn-reset { 
            padding: 10px 20px; 
            background: #d32f2f; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold;
            display: inline-block;
        }
        .btn-area { margin-top: 30px; display: flex; justify-content: space-between; gap: 20px; }
        .btn { padding: 12px 25px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid transparent; }
        .btn-back { background: #ccc; color: #000; border-color: #ccc; width: 30%; }
        .btn-save { background: #007bff; color: #fff; width: 70%; }
    </style>
</head>
<body>

<div class="container">
    <h1>ドライバー情報詳細・編集</h1>
    <p style="font-size: 1.1em;">社員ID: **<?php echo htmlspecialchars($driver['emp_id']); ?>**</p>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'success_update'): ?>
            <div class="msg msg-success">情報が正常に更新されました。</div>
        <?php elseif ($_GET['msg'] == 'success_reset'): ?>
            <div class="msg msg-success">パスワードがデフォルト値 (<?php echo DEFAULT_PASSWORD; ?>) にリセットされました。</div>
        <?php elseif ($_GET['msg'] == 'error_db'): ?>
            <div class="msg msg-error">データベースエラーが発生しました。</div>
        <?php elseif ($_GET['msg'] == 'error_required'): ?>
            <div class="msg msg-error">エラー：必須項目が不足しています。</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="section">
        <h2>基本情報編集</h2>
        <form action="driver_process.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($driver['id']); ?>">
            
            <div class="form-group">
                <label for="name">氏名 (乗務員名前) *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($driver['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="office">所属営業所 *</label>
                <select id="office" name="office" required>
                    <option value="">-- 選択してください --</option>
                    <?php foreach ($offices as $office): ?>
                        <option value="<?php echo htmlspecialchars($office); ?>"
                            <?php echo ($driver['office'] === $office) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($office); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="language_support">言語対応</label>
                <input type="text" id="language_support" name="language_support" 
                       value="<?php echo htmlspecialchars($driver['language_support'] ?? ''); ?>" placeholder="例: 日本語/英語">
            </div>

            <div class="btn-area">
                <a href="driver_list.php" class="btn btn-back">一覧に戻る</a>
                <button type="submit" class="btn btn-save">情報を更新する</button>
            </div>
        </form>
    </div>

    <div class="section">
        <h2>パスワードリセット</h2>
        <div class="password-reset">
            <p>現在のパスワードをデフォルト値 **<?php echo DEFAULT_PASSWORD; ?>** にリセットします。</p>
            <form action="driver_process.php" method="POST" onsubmit="return confirm('本当にパスワードをデフォルト値にリセットしてもよろしいですか？');">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($driver['id']); ?>">
                <button type="submit" class="btn-reset">パスワードをリセットする</button>
            </form>
        </div>
    </div>

</div>

</body>
</html>