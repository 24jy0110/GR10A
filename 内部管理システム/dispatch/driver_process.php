<?php
session_start();
include("../includes/db_connect.php");

// 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dispatch') {
    exit('Unauthorized');
}


const DEFAULT_PASSWORD = '123456';

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;


function generateEmpId($pdo, $office_name) {

    $prefixMap = [
        '亀沢本社'     => 'K', // Kamezawa
        '豊島支社'     => 'T', // Toshima
        '桃井支社'     => 'M', // Momoi
        '本羽田支社'   => 'H', // Honhaneda
        '富士見町支社' => 'F', // Fujimicho
        '中区支社'     => 'N', // Nakaku
    ];
    

    $prefix = $prefixMap[$office_name] ?? 'X'; 
    
    try {
     
        $sql = "SELECT emp_id FROM employees WHERE emp_id LIKE :prefix AND role = 'driver' ORDER BY emp_id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':prefix' => $prefix . '%']);
        $last_id = $stmt->fetchColumn();

      
        if ($last_id) {
            $num = (int)substr($last_id, 1);
            $new_num = $num + 1;
        } else {
            $new_num = 1; // 从 1 开始
        }
        

        return $prefix . str_pad($new_num, 4, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        return false;
    }
}


if ($action === 'register') {
    
    $name = trim($_POST['name'] ?? '');
    $office = $_POST['office'] ?? '';
    $language_support = trim($_POST['language_support'] ?? '');
    $raw_password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($office) || empty($raw_password)) {
        header("Location: driver_register.php?msg=error_required");
        exit;
    }
    
    $emp_id = generateEmpId($pdo, $office);
    if (!$emp_id) {
        header("Location: driver_register.php?msg=error_id_gen");
        exit;
    }

    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    
    try {
        $sql = "INSERT INTO employees (emp_id, password, name, office, language_support, role) VALUES (?, ?, ?, ?, ?, 'driver')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id, $hashed_password, $name, $office, $language_support]);
        
        header("Location: driver_list.php?msg=success&id=" . urlencode($emp_id));
        exit;

    } catch (PDOException $e) {
        header("Location: driver_register.php?msg=error_db");
        exit;
    }
}


elseif ($action === 'update' && $id) {
    
    $name = trim($_POST['name'] ?? '');
    $office = $_POST['office'] ?? '';
    $language_support = trim($_POST['language_support'] ?? '');
    
    if (!$id || empty($name) || empty($office)) {
        header("Location: driver_detail.php?id={$id}&msg=error_required");
        exit;
    }
    
    try {
        $sql = "UPDATE employees SET name = ?, office = ?, language_support = ? WHERE id = ? AND role = 'driver'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $office, $language_support, $id]);

        header("Location: driver_detail.php?id={$id}&msg=success_update");
        exit;

    } catch (PDOException $e) {
        header("Location: driver_detail.php?id={$id}&msg=error_db");
        exit;
    }
}


elseif ($action === 'reset_password' && $id) {
    
    if (!$id) {
        header("Location: driver_list.php?msg=error_required");
        exit;
    }
    
    $hashed_password = password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT);
    
    try {
        $sql = "UPDATE employees SET password = ? WHERE id = ? AND role = 'driver'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hashed_password, $id]);

        header("Location: driver_detail.php?id={$id}&msg=success_reset");
        exit;

    } catch (PDOException $e) {
        header("Location: driver_detail.php?id={$id}&msg=error_db");
        exit;
    }
}

header("Location: driver_list.php");
exit;
?>