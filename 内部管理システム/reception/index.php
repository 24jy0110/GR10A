<?php
session_start();
include("../includes/db_connect.php");
include("../includes/header.php"); // 包含顶部导航

$officeCode = $_SESSION['office_code'];
$officeName = "未登録支社";

// 查支社名字
$stmt = $pdo->prepare("SELECT sales_office_name FROM sales_office WHERE sales_office_code = ?");
$stmt->execute([$officeCode]);
$office = $stmt->fetch();
if ($office) { $officeName = $office['sales_office_name']; }
?>

<div style="text-align: center; padding: 60px 20px;">
    <h2 style="color:#666;">ようこそ、<?php echo htmlspecialchars($officeName); ?></h2>
    <p style="font-size: 40px; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['user_name']); ?> 様</p>
    <br>
    <a href="reservation_list.php" style="display:inline-block; width:350px; padding:30px; background:#000; color:#fff; text-decoration:none; border-radius:10px; font-size:24px;">予約一覧・検索</a>
</div>

<?php include("../includes/footer.php"); ?>