<?php
session_start();
include("../includes/db_connect.php");
include("../includes/header.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    exit('Unauthorized');
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;

if ($action === 'register') {
    $plate_number = trim($_POST['plate_number'] ?? '');
    $car_code = $_POST['car_code'] ?? '';
    $office = $_POST['office'] ?? '';
    
    if (empty($plate_number) || empty($car_code) || empty($office)) {
        header("Location: vehicle_register.php?msg=error");
        exit;
    }
    
    try {
        $sql = "INSERT INTO vehicles (plate_number, car_code, office, status) VALUES (?, ?, ?, '空車')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$plate_number, $car_code, $office]);
        header("Location: vehicle_register.php?msg=success&plate=" . urlencode($plate_number));
        exit;
    } catch (PDOException $e) {
        header("Location: vehicle_register.php?msg=error");
        exit;
    }
}


elseif ($action === 'update' && $id) {
    
    $plate_number = trim($_POST['plate_number'] ?? '');
    $car_code = $_POST['car_code'] ?? '';
    $office = $_POST['office'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (empty($plate_number) || empty($car_code) || empty($office) || empty($status)) {
        header("Location: vehicle_detail.php?id={$id}&msg=error");
        exit;
    }
    
    try {
        $sql = "UPDATE vehicles SET plate_number = ?, car_code = ?, office = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $plate_number, 
            $car_code, 
            $office, 
            $status, 
            $id
        ]);
        
        header("Location: vehicle_detail.php?id={$id}&msg=success");
        exit;

    } catch (PDOException $e) {

        header("Location: vehicle_detail.php?id={$id}&msg=error_db");
        exit;
    }
}


elseif ($action === 'decommission' && $id) {
    try {

        $sql = "UPDATE vehicles SET status = '廃車' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: vehicle_status.php?msg=decommission_success");
        exit;
    } catch (PDOException $e) {
        header("Location: vehicle_detail.php?id={$id}&msg=error");
        exit;
    }
}
elseif ($action === 'status_change' && $id) {
    
    $new_status = $_POST['new_status'] ?? '';
    

    if (empty($new_status)) {
        header("Location: vehicle_detail.php?id={$id}&msg=error_status_missing");
        exit;
    }
    
    try {

        $sql = "UPDATE vehicles SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $id]);

  
        header("Location: vehicle_detail.php?id={$id}&msg=success_status_updated");
        exit;

    } catch (PDOException $e) {

        header("Location: vehicle_detail.php?id={$id}&msg=error_db");
        exit;
    }
}
header("Location: vehicle_status.php");
exit;
?>