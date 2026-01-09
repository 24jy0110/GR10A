<?php
session_start();
include("includes/db_connect.php");

$emp_id = $_POST['employee_id'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($emp_id) || empty($password)) {
    header("Location: index.php?error=empty");
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE employee_id = ?");
    $stmt->execute([$emp_id]);
    $user = $stmt->fetch();


    if ($user && $password === $user['password']) {
        // 关键：Sessionキーを includes/header.php の参照先と完全一致させる
        $_SESSION['user_id'] = $user['employee_id'];
        $_SESSION['employee_name'] = $user['employee_name']; // 物理名: employee_name
        $_SESSION['sales_office_code'] = $user['sales_office_code']; // 物理名: sales_office_code
        // 例: EMPL202401001 なら "01"
        $job_type = substr($emp_id, 8, 2);

        switch ($job_type) {
            case '01': // 受付
                header("Location: reception/index.php");
                break;
            case '02': // 配車センター
                header("Location: dispatch/index.php");
                break;
            case '03': // ドライバー
                header("Location: driver/index.php");
                break;
            default:
                header("Location: index.php?error=role_error");
                break;
        }
        exit;
    } else {
        header("Location: index.php?error=auth_failed");
        exit;
    }
} catch (PDOException $e) {

    error_log($e->getMessage());
    header("Location: index.php?error=db_error");
    exit;
}
