<?php
session_start();
include("../includes/db_connect.php");

$sql = "SELECT * FROM yoyaku ORDER BY reservation_date DESC, service_start_time DESC";
$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>予約一覧 - 受付</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f8f9fa; }
        .status-待機中 { color: #007bff; font-weight: bold; }
        .status-キャンセル { color: #dc3545; }
        .status-完了 { color: #28a745; }
        .btn-view { text-decoration: none; color: #007bff; }
    </style>
</head>
<body>
    <h1>予約一覧</h1>
    <table>
        <thead>
            <tr>
                <th>予約番号</th>
                <th>乗車日</th>
                <th>時間</th>
                <th>顧客名</th>
                <th>乗車場所</th>
                <th>降車場所</th>
                <th>状態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?php echo $r['reservation_number']; ?></td>
                <td><?php echo $r['reservation_date']; ?></td>
                <td><?php echo substr($r['service_start_time'], 0, 5); ?></td>
                <td><?php echo htmlspecialchars($r['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($r['pickup_location']); ?></td>
                <td><?php echo htmlspecialchars($r['dropoff_location']); ?></td>
                <td><span class="status-<?php echo $r['reservation_status']; ?>"><?php echo $r['reservation_status']; ?></span></td>
                <td><a href="reservation_detail.php?id=<?php echo $r['reservation_number']; ?>" class="btn-view">詳細表示</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>