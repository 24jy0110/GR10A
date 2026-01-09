<?php
session_start();
include("../includes/db_connect.php");
include("../includes/header.php");
// 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

try {
    // 获取所有可能的营业所列表 (用于下拉选择)
    $officeSql = "SELECT DISTINCT office FROM vehicles ORDER BY office";
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
    <title>車両登録</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        .form-group input[type="text"], .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .btn-area { margin-top: 30px; display: flex; justify-content: space-between; gap: 20px; }
        .btn { padding: 12px 25px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid transparent; }
        
        .btn-back { background: #ccc; color: #000; border-color: #ccc; }
        .btn-back:hover { background: #bbb; }
        
        .btn-submit { background: #000; color: #fff; }
        .btn-submit:hover { opacity: 0.8; }
        
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .msg-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .msg-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    </style>
</head>
<body>

<div class="container">
    <h1>新規車両登録</h1>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'success'): ?>
            <div class="msg msg-success">車両「<?php echo htmlspecialchars($_GET['plate'] ?? ''); ?>」を正常に登録しました。</div>
        <?php elseif ($_GET['msg'] == 'error'): ?>
            <div class="msg msg-error">登録に失敗しました。必須項目を確認してください。</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <form action="vehicle_process.php" method="POST">
        <input type="hidden" name="action" value="register">
        
        <div class="form-group">
            <label for="plate_number">ナンバープレート (必須)</label>
            <input type="text" id="plate_number" name="plate_number" placeholder="例: 墨田300あ0001" required>
        </div>

        <div class="form-group">
            <label for="car_code">車種 (必須)</label>
            <select id="car_code" name="car_code" required>
                <option value="">選択してください</option>
                <option value="crown">トヨタ クラウン</option>
                <option value="alphard">トヨタ アルファード</option>
                <option value="hiace">トヨタ ハイエース</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="office">所属営業所 (必须)</label>
            <select id="office" name="office" required>
                <option value="">選択してください</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo htmlspecialchars($office); ?>">
                        <?php echo htmlspecialchars($office); ?>
                    </option>
                <?php endforeach; ?>
                <option value="その他/新規">その他/新規</option>
            </select>
            <small style="color:#999;">もし新しい営業所の場合は「その他/新規」を選択し、次回登録時に自動でリストに追加されます。</small>
        </div>

        <div class="btn-area">
            <a href="vehicle_status.php" class="btn btn-back">キャンセル・戻る</a>
            <button type="submit" class="btn btn-submit">登録を実行</button>
        </div>
    </form>
</div>

</body>
</html>