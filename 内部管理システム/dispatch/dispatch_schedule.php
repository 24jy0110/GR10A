<?php
session_start();
include("../includes/db_connect.php"); // 引入数据库连接
include("../includes/header.php");
// 1. 权限检查 (只有 dispatch 也就是配车员能看)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

// 2. 获取数据 SQL
// 逻辑：从 reservations 表里拿所有数据
// 为了显示“车牌号”，我们需要 LEFT JOIN 刚刚建立的 vehicles 表
// 按照 start_date 和 start_time 排序，最近的订单在上面
$sql = "
    SELECT 
        r.*, 
        v.plate_number 
    FROM reservations r
    LEFT JOIN vehicles v ON r.vehicle_id = v.id
    ORDER BY r.start_date DESC, r.start_time DESC
";

try {
    $stmt = $pdo->query($sql);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}

// 辅助函数：把数据库里的 crown 变成日语显示
function getCarName($code) {
    $names = [
        'crown'   => 'トヨタ クラウン',
        'alphard' => 'トヨタ アルファード',
        'hiace'   => 'トヨタ ハイエース'
    ];
    return $names[$code] ?? $code;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>配車予定一覧</title>
    <style>
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        h1 { margin: 0; font-size: 24px; }
        .btn-back {
            text-decoration: none;
            color: #333;
            border: 1px solid #ccc;
            padding: 8px 16px;
            border-radius: 4px;
            background: #fff;
        }
        .btn-back:hover { background: #eee; }

        /* 表格样式 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
        }
        tr:hover { background-color: #f9f9f9; }

        /* 状态 Badge */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: #fff;
            font-weight: bold;
        }
        /* 根据你的数据库 enum 设置颜色 */
        .status-wait { background-color: #ff9800; } /* 待機中 - 橙色 */
        .status-done { background-color: #2196f3; } /* 配車済 - 蓝色 */
        .status-run  { background-color: #4caf50; } /* 運行中 - 绿色 */
        .status-fin  { background-color: #9e9e9e; } /* 完了 - 灰色 */
        .status-can  { background-color: #f44336; } /* キャンセル - 红色 */

        /* 按钮 */
        .btn-action {
            display: inline-block;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            color: #fff;
            background-color: #333;
        }
        .btn-urgent {
            background-color: #d32f2f; /* 红色，提示需要操作 */
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(211, 47, 47, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(211, 47, 47, 0); }
            100% { box-shadow: 0 0 0 0 rgba(211, 47, 47, 0); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-area">
        <h1>配車予定一覧 (予約リスト)</h1>
        <a href="index.php" class="btn-back">TOPへ戻る</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>予約番号</th>
                <th>利用日時</th>
                <th>お客様名</th>
                <th>場所 (お迎え)</th>
                <th>車種</th>
                <th>ステータス</th>
                <th>車両番号</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reservations) === 0): ?>
                <tr><td colspan="8" style="text-align:center;">現在、予約はありません。</td></tr>
            <?php else: ?>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td>#<?php echo sprintf('%06d', $res['id']); ?></td>
                        
                        <td>
                            <?php echo htmlspecialchars($res['start_date']); ?><br>
                            <small><?php echo htmlspecialchars(substr($res['start_time'], 0, 5)); ?>〜</small>
                        </td>
                        
                        <td>
                            <?php echo htmlspecialchars($res['name']); ?> 様<br>
                            <small style="color:#888;"><?php echo htmlspecialchars($res['people']); ?>名</small>
                        </td>
                        
                        <td>
                            <?php echo htmlspecialchars($res['pickup_detail']); ?>
                        </td>

                        <td>
                            <?php echo getCarName($res['car_code']); ?>
                        </td>

                        <td>
                            <?php
                                // 根据你的 status enum 值设置 class
                                $s = $res['status'];
                                $badgeClass = 'status-fin'; // 默认
                                if ($s === '待機中') $badgeClass = 'status-wait';
                                elseif ($s === '配車済') $badgeClass = 'status-done';
                                elseif ($s === '運行中') $badgeClass = 'status-run';
                                elseif ($s === 'キャンセル') $badgeClass = 'status-can';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($s); ?>
                            </span>
                        </td>

                        <td>
                            <?php if (!empty($res['plate_number'])): ?>
                                <b><?php echo htmlspecialchars($res['plate_number']); ?></b>
                            <?php else: ?>
                                <span style="color:#ccc;">未定</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($res['status'] === '待機中'): ?>
                                <a href="dispatch_detail.php?id=<?php echo $res['id']; ?>" class="btn-action btn-urgent">配車手配</a>
                            <?php else: ?>
                                <a href="dispatch_detail.php?id=<?php echo $res['id']; ?>" class="btn-action">詳細確認</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>