<?php
session_start();
include("../includes/db_connect.php");


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: vehicle_status.php");
    exit;
}

try {

    $vehicleSql = "SELECT id, plate_number, status FROM vehicles WHERE id = ?";
    $vehicleStmt = $pdo->prepare($vehicleSql);
    $vehicleStmt->execute([$id]);
    $vehicle = $vehicleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) { exit("指定された車両が見つかりませんでした。"); }
    
    $futureResSql = "
        SELECT COUNT(id) AS future_count
        FROM reservations
        WHERE vehicle_id = ? 
        AND status IN ('配車済', '待機中') 
        AND start_date >= CURDATE()
    ";
    $futureResStmt = $pdo->prepare($futureResSql);
    $futureResStmt->execute([$id]);
    $futureCount = $futureResStmt->fetch(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

$current_status = $vehicle['status'];
$is_running = ($current_status === '運行中');

$selectable_statuses = ['空車', '使用停止', '廃車'];

function getStatusClass($status) {
    if ($status === '空車') return 'status-empty';
    if ($status === '使用停止') return 'status-stop';
    if ($status === '廃車') return 'status-scrapped';
    return 'status-default';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>車両状態更新</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .modal-box { 
            max-width: 500px; 
            margin: 50px auto; 
            background: #fff; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        h1 { margin-top: 0; font-size: 24px; display: flex; align-items: center; }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            margin-left: 10px;
        }
        .status-empty { background-color: #4CAF50; }
        .status-stop { background-color: #F44336; }
        .status-scrapped { background-color: #607d8b; }
        .status-running { background-color: #FF9800; }
        .status-default { background-color: #999; }
        
        .status-options { margin-top: 20px; }
        .status-option { margin-bottom: 15px; display: flex; align-items: center; cursor: pointer; }
        .status-option input[type="radio"] { margin-right: 15px; transform: scale(1.5); }
        .status-option label { font-size: 18px; display: flex; align-items: center; width: 100%; padding: 10px; border-radius: 4px; }
        
        .status-label { 
            display: inline-block; 
            padding: 8px 15px; 
            border-radius: 4px; 
            font-weight: bold; 
            color: white; 
            text-align: center;
        }

        .warning-box {
            background: #fff3e0;
            border: 1px solid #ffcc80;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
            font-size: 14px;
            color: #555;
        }
        .warning-box strong { color: #d84315; }

        .btn-area { margin-top: 30px; display: flex; justify-content: space-between; gap: 20px; }
        .btn { padding: 12px 20px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid transparent; }
        .btn-submit { background: #000; color: #fff; }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; }
        .btn-back { background: #fff; color: #333; border-color: #333; }
        
        .running-locked { 
            background: #fbe9e7; 
            border: 1px solid #ff8a80; 
            color: #d32f2f; 
            padding: 15px; 
            text-align: center;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="modal-box">
    <h1>
        現在車両の状態
        <span class="status-badge <?php echo getStatusClass($current_status); ?>">
            <?php echo htmlspecialchars($current_status); ?>
        </span>
    </h1>
    
    <p style="margin-top: 10px;">ナンバープレート: <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>

    <?php if ($is_running): ?>
        <div class="running-locked">
            現在「運行中」のため、状態を変更することはできません。
        </div>
        <div class="btn-area">
            <a href="vehicle_detail.php?id=<?php echo $id; ?>" class="btn btn-back" style="width:100%;">戻る</a>
        </div>
        
    <?php else: ?>
        <form action="vehicle_process.php" method="POST">
            <input type="hidden" name="action" value="status_change">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            
            <p style="margin-top: 20px; font-weight: bold;">実際の状態を選択してください</p>
            
            <div class="status-options">
                <?php foreach ($selectable_statuses as $status): ?>
                    <div class="status-option">
                        <input type="radio" id="status_<?php echo $status; ?>" name="new_status" value="<?php echo htmlspecialchars($status); ?>" 
                               <?php echo ($current_status === $status) ? 'checked' : ''; ?> required>
                        <label for="status_<?php echo $status; ?>">
                            <span class="status-label <?php echo getStatusClass($status); ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="warning-box">
                <h2>※注意事項</h2>
                <p>現在「運行中」の場合は、状態を変更することはできません。</p>
                <p>
                    現在「<?php echo htmlspecialchars($current_status); ?>」ですが、今後の予約に割り当てた場合 (<strong><?php echo $futureCount; ?>件</strong>)、
                    状態を「使用停止」や「廃車」に変更する時に、該当予約の車両変更手続きが必要です。
                </p>
            </div>

            <div class="btn-area">
                <button type="submit" class="btn btn-submit">状態を更新する</button>
                <a href="vehicle_detail.php?id=<?php echo $id; ?>" class="btn btn-back">戻る</a>
            </div>
        </form>
        
    <?php endif; ?>

</div>

</body>
</html>