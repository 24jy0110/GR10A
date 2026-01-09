<?php
session_start();
include("../includes/db_connect.php");
include("../includes/header.php");
// 1. 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    header("Location: ../index.php");
    exit;
}

// 2. 接收 ID
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "IDが指定されていません。";
    exit;
}

// 3. 获取详细数据（关联车辆表和司机表，以便显示已分配的信息）
// 这里的 employees 表是你之前提到的，假设用来存司机名字
$sql = "
    SELECT 
        r.*, 
        v.plate_number, 
        v.status as v_status,
        e.name as driver_name
    FROM reservations r
    LEFT JOIN vehicles v ON r.vehicle_id = v.id
    LEFT JOIN employees e ON r.driver_id = e.id
    WHERE r.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    echo "予約が見つかりません。";
    exit;
}

// 车型名称转换
function getCarName($code) {
    $names = ['crown' => 'トヨタ クラウン', 'alphard' => 'トヨタ アルファード', 'hiace' => 'トヨタ ハイエース'];
    return $names[$code] ?? $code;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>予約詳細 - 配車管理</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 15px; }
        
        .info-group { margin-bottom: 30px; }
        .info-title { font-weight: bold; font-size: 18px; margin-bottom: 10px; border-left: 5px solid #000; padding-left: 10px; background: #eee; padding-top: 5px; padding-bottom: 5px;}
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { width: 30%; background: #fafafa; color: #555; }
        
        .status-box { padding: 20px; text-align: center; margin-top: 20px; border: 2px solid #ddd; border-radius: 8px; }
        .status-waiting { border-color: #ff9800; background: #fff3e0; color: #e65100; }
        .status-done { border-color: #2196f3; background: #e3f2fd; color: #0d47a1; }

        .btn-area { margin-top: 40px; text-align: center; display: flex; justify-content: space-between; gap: 20px; }
        .btn { padding: 15px 0; width: 100%; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer; }
        .btn-back { background: #ccc; color: #000; }
        .btn-assign { background: #000; color: #fff; }
        .btn-assign:hover { opacity: 0.8; }
        .btn-cancel { background: #d32f2f; color: #fff; }
        
        /* 错误/成功消息 */
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .msg-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .msg-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    </style>
</head>
<body>

<div class="container">
    
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'success'): ?>
            <div class="msg msg-success">配車が完了しました！ (車両ID: <?php echo htmlspecialchars($_GET['v_id'] ?? ''); ?>)</div>
        <?php elseif ($_GET['msg'] == 'full'): ?>
            <div class="msg msg-error">申し訳ありません。指定された日時に空いている車両がありません。</div>
        <?php elseif ($_GET['msg'] == 'reset'): ?>
            <div class="msg msg-success">配車を解除しました。</div>
        <?php endif; ?>
    <?php endif; ?>

    <h1>予約詳細情報</h1>

    <div class="status-box <?php echo ($res['status']=='待機中') ? 'status-waiting' : 'status-done'; ?>">
        現在のステータス：<strong><?php echo htmlspecialchars($res['status']); ?></strong>
    </div>

    <div class="info-group">
        <div class="info-title">運行スケジュール</div>
        <table>
            <tr><th>予約ID</th><td>#<?php echo sprintf('%06d', $res['id']); ?></td></tr>
            <tr><th>利用開始日時</th><td><?php echo htmlspecialchars($res['start_date']); ?> <?php echo htmlspecialchars(substr($res['start_time'], 0, 5)); ?></td></tr>
            <tr><th>利用終了日</th><td><?php echo htmlspecialchars($res['end_date']); ?></td></tr>
            <tr><th>お迎え場所</th><td><?php echo htmlspecialchars($res['pickup_detail']); ?></td></tr>
            <tr><th>行き先</th><td><?php echo htmlspecialchars($res['drop_detail']); ?></td></tr>
        </table>
    </div>

    <div class="info-group">
        <div class="info-title">お客様・車両情報</div>
        <table>
            <tr><th>お客様名</th><td><?php echo htmlspecialchars($res['name']); ?> 様</td></tr>
            <tr><th>希望車種</th><td><?php echo getCarName($res['car_code']); ?></td></tr>
            <tr><th>乗車人数</th><td><?php echo htmlspecialchars($res['people']); ?> 名</td></tr>
        </table>
    </div>
    
    <?php if ($res['vehicle_id']): ?>
    <div class="info-group">
        <div class="info-title">配車・運行情報</div>
        <table>
            <tr><th>担当車両</th><td><?php echo htmlspecialchars($res['plate_number']); ?></td></tr>
            <tr><th>担当ドライバー</th>
                <td>
                    <?php echo $res['driver_id'] ? htmlspecialchars($res['driver_name']) : '<span style="color:red">未確定（ドライバーの挙手待ち）</span>'; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <div class="btn-area">
        <a href="dispatch_schedule.php" class="btn btn-back">一覧に戻る</a>

        <?php if ($res['status'] === '待機中'): ?>
            <form action="dispatch_action.php" method="POST" style="width: 100%;">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                <button type="submit" class="btn btn-assign">空き車両を自動検索して配車する</button>
            </form>
        
        <?php elseif ($res['status'] === '配車済'): ?>
            <form action="dispatch_action.php" method="POST" style="width: 100%;">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                <button type="submit" class="btn btn-cancel" onclick="return confirm('本当に配車を解除しますか？');">配車を解除する</button>
            </form>
        <?php endif; ?>
    </div>

</div>

</body>
</html>