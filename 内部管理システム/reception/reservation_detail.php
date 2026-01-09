<?php
session_start();
include("../includes/db_connect.php");

$res_id = $_GET['id'] ?? '';
$sql = "SELECT y.*, g1.language_category_name AS lang1, g2.language_category_name AS lang2 
        FROM yoyaku y
        LEFT JOIN gengo_category g1 ON y.first_language_preference = g1.language_category_id
        LEFT JOIN gengo_category g2 ON y.second_language_preference = g2.language_category_id
        WHERE y.reservation_number = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$res_id]);
$res = $stmt->fetch();

if (!$res) die("予約が見つかりません。");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>予約詳細</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
        .info-row { display: flex; border-bottom: 1px solid #eee; padding: 10px 0; }
        .label { width: 150px; font-weight: bold; color: #666; }
        .btn-cancel { background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>予約詳細情報 (<?php echo $res['reservation_status']; ?>)</h2>
        <div class="info-row"><div class="label">予約番号</div><div><?php echo $res['reservation_number']; ?></div></div>
        <div class="info-row"><div class="label">顧客名</div><div><?php echo htmlspecialchars($res['customer_name']); ?> 様</div></div>
        <div class="info-row"><div class="label">乗车地点</div><div><?php echo htmlspecialchars($res['pickup_location']); ?></div></div>
        <div class="info-row"><div class="label">目的地</div><div><?php echo htmlspecialchars($res['dropoff_location']); ?></div></div>
        <div class="info-row"><div class="label">希望言語1</div><div><?php echo htmlspecialchars($res['lang1'] ?? '未設定'); ?></div></div>
        
        <?php if ($res['reservation_status'] == '待機中'): ?>
            <a href="reservation_process.php?action=cancel&id=<?php echo $res['reservation_number']; ?>" 
               class="btn-cancel" onclick="return confirm('本当にキャンセルしますか？')">予約をキャンセルする</a>
        <?php endif; ?>
        <br><br>
        <a href="reservation_list.php">一覧に戻る</a>
    </div>
</body>
</html>