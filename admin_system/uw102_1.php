<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

if (!isset($_GET['r'])) {
    header("Location: uw101.php");
    exit;
}

$resNo = $_GET['r'];

try {

    // 开启事务
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    $sql = "
        UPDATE reservation
        SET state_code = 'STC03'
        WHERE reservation_number = :no
        AND state_code IN ('STC01','STC02')
        AND service_start_time > NOW()
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":no" => $resNo]);

    if ($stmt->rowCount() === 0) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        header("Location: uw102.php?r=" . $resNo . "&error=cancel");
        exit;
    }

    // 车辆状态改为空车
    $sqlVehicle = "
        UPDATE vehicle v
        JOIN reservation r ON r.number_plate = v.number_plate
        SET v.vehicle_state = '空車'
        WHERE r.reservation_number = :no
    ";

    $stmtVehicle = $pdo->prepare($sqlVehicle);
    $stmtVehicle->execute([":no" => $resNo]);

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

    header("Location: uw102_2.php?r=" . $resNo);

    exit;
} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("エラーが発生しました：" . $e->getMessage());
}
