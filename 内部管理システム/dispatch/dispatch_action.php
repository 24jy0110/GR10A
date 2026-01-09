<?php
session_start();
include("../includes/db_connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    exit('Unauthorized');
}

$action = $_POST['action'] ?? '';
$res_id = $_POST['reservation_id'] ?? '';

if (!$res_id) {
    header("Location: dispatch_list.php");
    exit;
}

if ($action === 'assign') {

    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$res_id]);
    $reservation = $stmt->fetch();

    if (!$reservation) exit('Error: Reservation not found');

    $target_car_code = $reservation['car_code'];
    $start_date = $reservation['start_date'];
    $end_date   = $reservation['end_date'];
    if (empty($end_date)) {
        $end_date = $start_date;
    }

    try {

        $pdo->beginTransaction();
        
        $sql = "
            SELECT id FROM vehicles
            WHERE car_code = :car_code 
            AND status = '空車'
            AND id NOT IN (
                SELECT vehicle_id FROM reservations
                WHERE status IN ('配車済', '運行中') 
                AND vehicle_id IS NOT NULL
                AND (
                    -- 这里的逻辑是：检查时间是否有重叠 (Overlap)
                    -- 已有订单的开始时间 <= 新订单的结束时间
                    -- 且 已有订单的结束时间 >= 新订单的开始时间
                    (start_date <= :end_date AND (end_date IS NULL OR end_date >= :start_date))
                )
            )
            ORDER BY RAND() -- 随机抓取一辆
            LIMIT 1         -- 只要一辆
            FOR UPDATE      -- 锁行，防止别人抢
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':car_code'   => $target_car_code,
            ':start_date' => $start_date,
            ':end_date'   => $end_date
        ]);
        
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            $vehicle_id = $vehicle['id'];
            
            $updateSql = "UPDATE reservations SET vehicle_id = ?, status = '配車済' WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$vehicle_id, $res_id]);

            $pdo->commit();
            

            header("Location: dispatch_detail.php?id={$res_id}&msg=success&v_id={$vehicle_id}");
            exit;

        } else {
  
            $pdo->rollBack();
            header("Location: dispatch_detail.php?id={$res_id}&msg=full");
            exit;
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
        exit;
    }


} elseif ($action === 'reset') {
    
    $sql = "UPDATE reservations SET vehicle_id = NULL, driver_id = NULL, status = '待機中' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$res_id]);

    header("Location: dispatch_detail.php?id={$res_id}&msg=reset");
    exit;
}
?>