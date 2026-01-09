<?php
session_start();
include("../includes/db_connect.php");

// 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

try {
    // 1. 获取所有独特的营业所和语言列表 (用于筛选框)
    $officeSql = "SELECT DISTINCT office FROM employees WHERE role = 'driver' AND office IS NOT NULL ORDER BY office";
    $officeStmt = $pdo->query($officeSql);
    $offices = $officeStmt->fetchAll(PDO::FETCH_COLUMN);

    $langSql = "SELECT DISTINCT language_support FROM employees WHERE role = 'driver' AND language_support IS NOT NULL ORDER BY language_support";
    $langStmt = $pdo->query($langSql);
    $languages = $langStmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. 获取筛选参数
    $selectedOffice = $_GET['office'] ?? 'all';
    $selectedLanguage = $_GET['language'] ?? 'all';
    $keyword = trim($_GET['keyword'] ?? '');

    // 3. 构建动态 SQL
    $whereClause = "WHERE role = 'driver'";
    $params = [];

    if ($selectedOffice !== 'all') {
        $whereClause .= " AND office = :office";
        $params[':office'] = $selectedOffice;
    }
    
    if ($selectedLanguage !== 'all') {
        // 使用 LIKE 来匹配包含该语言的记录
        $whereClause .= " AND language_support LIKE :lang";
        $params[':lang'] = '%' . $selectedLanguage . '%';
    }
    
    if (!empty($keyword)) {
        // 关键词筛选：匹配 ID 或姓名
        $whereClause .= " AND (emp_id LIKE :keyword OR name LIKE :keyword_name)";
        $params[':keyword'] = '%' . $keyword . '%';
        $params[':keyword_name'] = '%' . $keyword . '%';
    }

    // 4. 获取所有司机信息
    $driversSql = "
        SELECT 
            id, emp_id, name, office, language_support
        FROM employees 
        " . $whereClause . " 
        ORDER BY office, emp_id ASC
    ";
    $driversStmt = $pdo->prepare($driversSql);
    $driversStmt->execute($params);
    $drivers = $driversStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ドライバー一覧</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .user-header { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px; }
        .user-info { margin-right: 15px; font-size: 14px; }
        .btn-logout { padding: 8px 15px; background: #d32f2f; color: white; text-decoration: none; border-radius: 4px; }
        .filter-form {
            background: #fafafa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 14px; color: #555; margin-bottom: 5px; }
        .filter-group select, .filter-group input[type="text"] { 
            padding: 8px 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            min-width: 150px; 
        }
        .btn-search { padding: 9px 20px; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-clear { padding: 9px 20px; background-color: #999; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .driver-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .driver-table th, .driver-table td { padding: 12px; border: 1px solid #eee; text-align: left; }
        .driver-table th { background: #f0f0f0; font-weight: bold; }
        .btn-detail { padding: 6px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 14px; }
        .bottom-actions { margin-top: 40px; display: flex; justify-content: center; gap: 20px; }
        .btn-register-driver, .btn-back {
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            min-width: 200px;
        }
        .btn-register-driver { background: #000; color: #fff; border: 1px solid #000; }
        .btn-back { background: #fff; color: #333; border: 1px solid #333; }
    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <p style="font-size: 18px; font-weight: bold;">ドライバー一覧</p>
        <div class="user-header">
            <div class="user-info">配車管理課 様</div>
            <a href="../logout.php" class="btn-logout">ログアウト</a>
        </div>
    </div>
    
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label for="office">営業所</label>
            <select name="office" id="office">
                <option value="all">すべて</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo htmlspecialchars($office); ?>" 
                        <?php echo ($selectedOffice === $office) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($office); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="language">言語対応</label>
            <select name="language" id="language">
                <option value="all">すべて</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?php echo htmlspecialchars($lang); ?>" 
                        <?php echo (strpos($selectedLanguage, $lang) !== false && $selectedLanguage !== 'all') ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($lang); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:#999; font-size: 12px;">※複数言語対応を含む</small>
        </div>

        <div class="filter-group">
            <label for="keyword">キーワード</label>
            <input type="text" name="keyword" id="keyword" placeholder="社員IDや名前" value="<?php echo htmlspecialchars($keyword); ?>">
        </div>

        <button type="submit" class="btn-search">検索</button>
        <a href="driver_list.php" class="btn-clear">クリア</a>
    </form>
    
    <table class="driver-table">
        <thead>
            <tr>
                <th>社員ID</th>
                <th>乗務員名前</th>
                <th>営業所</th>
                <th>言語対応</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($drivers)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">該当するドライバーは見つかりませんでした。</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($drivers as $driver): ?>
                <tr>
                    <td><?php echo htmlspecialchars($driver['emp_id']); ?></td>
                    <td><?php echo htmlspecialchars($driver['name']); ?></td>
                    <td><?php echo htmlspecialchars($driver['office']); ?></td>
                    <td><?php echo htmlspecialchars($driver['language_support']); ?></td>
                    <td>
                        <a href="driver_detail.php?id=<?php echo $driver['id']; ?>" class="btn-detail">詳細</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="bottom-actions">
        <a href="driver_register.php" class="btn-register-driver">ドライバーを登録する</a>
        <a href="index.php" class="btn-back">戻る</a>
    </div>

</div>

</body>
</html>