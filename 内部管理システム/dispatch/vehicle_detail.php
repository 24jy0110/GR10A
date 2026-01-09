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
    header("Location: vehicle_status.php");
    exit;
}

// 车型名称和定员转换 (与 vehicle_status.php 保持一致)
function getCarName($code) {
    $names = ['crown' => 'トヨタ クラウン/3名', 'alphard' => 'トヨタ アルファード/5名', 'hiace' => 'トヨタ ハイエース/9名'];
    return $names[$code] ?? $code;
}

function getCapacity($code) {
    $capacities = ['crown' => 3, 'alphard' => 5, 'hiace' => 9];
    return $capacities[$code] ?? 0;
}

try {
    // 1. 获取车辆详情
    $vehicleSql = "SELECT * FROM vehicles WHERE id = ?";
    $vehicleStmt = $pdo->prepare($vehicleSql);
    $vehicleStmt->execute([$id]);
    $vehicle = $vehicleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) { exit("指定された車両が見つかりませんでした。"); }
    
    // 2. 获取当前运行中的订单及司机信息
    // 假设司机/员工表是 employees，司机姓名是 employees.name
    $runningResSql = "
        SELECT 
            r.id AS res_id, 
            r.status, 
            e.name AS driver_name
        FROM reservations r
        LEFT JOIN employees e ON r.driver_id = e.id
        WHERE r.vehicle_id = :id AND r.status = '運行中'
    ";
    $runningResStmt = $pdo->prepare($runningResSql);
    $runningResStmt->execute([':id' => $id]);
    $runningReservation = $runningResStmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. 获取该车辆未来的预约信息 (今後の予約)
    $schedulesSql = "
        SELECT 
            r.id, r.start_date, r.start_time, r.name, r.status
        FROM reservations r
        WHERE r.vehicle_id = ? 
        AND r.status IN ('配車済', '待機中') 
        AND r.start_date >= CURDATE()
        ORDER BY r.start_date ASC, r.start_time ASC
    ";
    $schedulesStmt = $pdo->prepare($schedulesSql);
    $schedulesStmt->execute([$id]);
    $schedules = $schedulesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

$is_running = ($vehicle['status'] === '運行中');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>車両詳細・運行状況</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        
        .header-info { font-size: 16px; margin-top: 10px; color: #555; }
        .section { margin-bottom: 30px; }
        .section h2 { border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-top: 20px; font-size: 18px; color: #333; }
        
        .info-pair { margin-bottom: 10px; display: flex; }
        .info-pair strong { display: inline-block; width: 150px; font-weight: bold; color: #333; }
        
        /* 状态标记 */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            margin-left: 10px;
        }
        .status-empty { background-color: #4CAF50; } /* 空車 */
        .status-running { background-color: #FF9800; } /* 運行中 */
        .status-stop { background-color: #F44336; } /* 使用停止/廃車 */

        .running-status-box {
            background: #ffe0b2;
            padding: 15px;
            border-radius: 6px;
            border-left: 5px solid #ff9800;
        }

        /* 预约列表 */
        .schedule-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .schedule-table th, .schedule-table td { padding: 10px; border: 1px solid #eee; text-align: left; }
        .schedule-table th { background: #f8f8f8; }
        
        /* 按钮区域 */
        .btn-area { margin-top: 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 12px 25px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid transparent; }
        
        .btn-update { background: #000; color: #fff; }
        .btn-update[disabled] { background: #ccc; color: #666; cursor: not-allowed; }
        
        .btn-back { background: #fff; color: #333; border-color: #333; }
    </style>
</head>
<body>

<div class="container">
    <h1>車両詳細: <?php echo htmlspecialchars($vehicle['plate_number']); ?></h1>
    <div class="header-info">
        所属: <?php echo htmlspecialchars($vehicle['office']); ?> / 車種: <?php echo getCarName($vehicle['car_code']); ?>
    </div>
    
    <div class="section">
        <h2>基本情報</h2>
        <div class="info-pair">
            <strong>ナンバープレート:</strong> 
            <span><?php echo htmlspecialchars($vehicle['plate_number']); ?></span>
        </div>
        <div class="info-pair">
            <strong>車種:</strong> 
            <span><?php echo getCarName($vehicle['car_code']); ?></span>
        </div>
        <div class="info-pair">
            <strong>定員:</strong> 
            <span><?php echo getCapacity($vehicle['car_code']); ?>名 (ドライバー除く)</span>
        </div>
        <div class="info-pair">
            <strong>所属営業所:</strong> 
            <span><?php echo htmlspecialchars($vehicle['office']); ?></span>
        </div>
    </div>

    <div class="section">
        <h2>現在運行状況</h2>
        <div class="info-pair">
            <strong>現在の状態:</strong> 
            <span><?php echo htmlspecialchars($vehicle['status']); ?></span>
            <span class="status-badge 
                <?php 
                    if ($vehicle['status'] === '空車') echo 'status-empty';
                    elseif ($vehicle['status'] === '運行中') echo 'status-running';
                    else echo 'status-stop';
                ?>">
                <?php echo htmlspecialchars($vehicle['status']); ?>
            </span>
        </div>
        <div class="info-pair">
            <strong>担当ドライバー:</strong> 
            <span>
                <?php echo $runningReservation ? htmlspecialchars($runningReservation['driver_name']) : '―'; ?>
            </span>
        </div>
        <div class="info-pair">
            <strong>乗車中の予約:</strong> 
            <span>
                <?php if ($runningReservation): ?>
                    <a href="dispatch_detail.php?id=<?php echo $runningReservation['res_id']; ?>">
                        <?php echo sprintf('RES%06d', $runningReservation['res_id']); ?>
                    </a>
                <?php else: ?>
                    ―
                <?php endif; ?>
            </span>
        </div>
    </div>

    <div class="section">
        <h2>今後の予約</h2>
        <?php if (!empty($schedules)): ?>
            <table class="schedule-table">
                <thead>
                    <tr><th>予約番号</th><th>乗車日時</th><th>操作</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td><?php echo sprintf('RES%06d', $s['id']); ?></td>
                        <td><?php echo htmlspecialchars($s['start_date']); ?> <?php echo substr($s['start_time'], 0, 5); ?></td>
                        <td>
                            <a href="dispatch_detail.php?id=<?php echo $s['id']; ?>" class="btn">詳細</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#999;">今後の配車予定はありません。</p>
        <?php endif; ?>
    </div>

    <div class="btn-area">
        <a href="vehicle_status_update.php?id=<?php echo $vehicle['id']; ?>" 
           class="btn btn-update"
           <?php echo $is_running ? 'style="pointer-events: none; opacity: 0.6;" disabled' : ''; ?>>
            車両状態を更新する
        </a>
        <a href="vehicle_status.php" class="btn btn-back">戻る</a>
    </div>

</div>

</body>
</html>