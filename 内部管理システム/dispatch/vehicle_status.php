<?php
session_start();
include("../includes/db_connect.php");
include("../includes/header.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

function getCarName($code) {
    $names = [
        'crown'   => 'トヨタ クラウン/3名',
        'alphard' => 'トヨタ アルファード/5名',
        'hiace'   => 'トヨタ ハイエース/9名'
    ];
    return $names[$code] ?? $code;
}

try {
    $selectedOffice = $_GET['office'] ?? 'all';
    $selectedStatus = $_GET['status'] ?? 'all';
    $selectedCarCode = $_GET['car_code'] ?? 'all';

    $whereClause = "WHERE 1=1";
    $params = [];

    if ($selectedOffice !== 'all') {
        $whereClause .= " AND office = :office";
        $params[':office'] = $selectedOffice;
    }
    if ($selectedStatus !== 'all') {
        $whereClause .= " AND status = :status";
        $params[':status'] = $selectedStatus;
    }
    if ($selectedCarCode !== 'all') {
        $whereClause .= " AND car_code = :car_code";
        $params[':car_code'] = $selectedCarCode;
    }
    
    $officeSql = "SELECT DISTINCT office FROM vehicles ORDER BY office";
    $officeStmt = $pdo->query($officeSql);
    $offices = $officeStmt->fetchAll(PDO::FETCH_COLUMN);


    $vehiclesSql = "SELECT * FROM vehicles " . $whereClause . " ORDER BY car_code, plate_number ASC";
    $vehiclesStmt = $pdo->prepare($vehiclesSql);
    $vehiclesStmt->execute($params);
    $vehicles = $vehiclesStmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

$car_codes = ['crown', 'alphard', 'hiace'];
$statuses = ['空車', '運行中', '使用停止', '廃車'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>車両ステータス確認</title>
    <style>
        /* ... (CSS 样式保持不变) ... */
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        
        .filter-form {
            background: #fafafa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 14px; color: #555; margin-bottom: 5px; }
        .filter-group select { padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; min-width: 150px; }
        .btn-filter { padding: 9px 20px; 
            background-color: #000; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .btn-register {
            background-color: #388e3c;
            color: white;
            padding: 9px 20px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s;
            display: inline-block;
        }
        .vehicle-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .vehicle-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            transition: transform 0.2s;
        }
        .vehicle-link:hover .vehicle-card {
            border-color: #000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transform: translateY(-3px);
        }
        .vehicle-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            background: #fff;
           
            padding-bottom: 15px; 
        }

        .card-header { padding: 15px; background: #f8f8f8; display: flex; justify-content: space-between; align-items: center; }
        .plate-number { font-size: 20px; font-weight: bold; color: #000; }
        .car-info { 
            font-size: 14px;
            color: #666;
            margin-top: 4px;
        }
        
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #fff; font-weight: bold; }
        .v-status-empty { background-color: #4CAF50; }
        .v-status-used { background-color: #FF9800; }
        .v-status-stop { background-color: #F44336; }
        .v-status-def { background-color: #607d8b; }
        
        

    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>車両ステータス確認 (配車状況)</h1>
        <a href="index.php" class="btn-back">TOPへ戻る</a>
    </div>
    <div style="margin-top: 40px; text-align: left;">
        <a href="vehicle_register.php" class="btn-register">車両を登録する</a>
    </div>
    </br>
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label for="office">営業所</label>
            <select name="office" id="office">
                <option value="all">全て</option>
                <?php foreach ($offices as $office): ?>
                    <option value="<?php echo htmlspecialchars($office); ?>" 
                        <?php echo ($selectedOffice === $office) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($office); ?>
                    </option>
                <?php endforeach; ?>
            </select>
           
        </div>

        <div class="filter-group">
            <label for="status">状態</label>
            <select name="status" id="status">
                <option value="all">全て</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo htmlspecialchars($status); ?>" 
                        <?php echo ($selectedStatus === $status) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="car_code">車種</label>
            <select name="car_code" id="car_code">
                <option value="all">全て</option>
                <?php foreach ($car_codes as $code): ?>
                    <option value="<?php echo htmlspecialchars($code); ?>" 
                        <?php echo ($selectedCarCode === $code) ? 'selected' : ''; ?>>
                        <?php echo getCarName($code); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-filter">車両を検索</button>
        
    </form>
    <div class="vehicle-list">
        <?php if (empty($vehicles)): ?>
             <p style="grid-column: 1 / -1; text-align: center; padding: 50px; font-size: 1.2em;">
                該当する車両は見つかりませんでした。
            </p>
        <?php endif; ?>

        <?php foreach ($vehicles as $vehicle): 
            $v_status = $vehicle['status'];
            $status_class = 'v-status-def';
            if ($v_status == '空車') $status_class = 'v-status-empty';
            elseif ($v_status == '運行中' || $v_status == '配車済') $status_class = 'v-status-used';
            elseif ($v_status == '使用停止') $status_class = 'v-status-stop';
        ?>
            <a href="vehicle_detail.php?id=<?php echo $vehicle['id']; ?>" class="vehicle-link">
                <div class="vehicle-card">
                    <div class="card-header">
                        <div>
                            <span class="plate-number"><?php echo htmlspecialchars($vehicle['plate_number']); ?></span>
                            <div class="car-info">
                                <?php echo getCarName($vehicle['car_code']); ?> (<?php echo htmlspecialchars($vehicle['office']); ?>)
                            </div>
                        </div>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($v_status); ?>
                        </span>
                    </div>
                    
                    </div>
            </a>
        <?php endforeach; ?>
    </div>
    
    

</div>

</body>
</html>