<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$employee_id = strtoupper(trim(mb_convert_kana($_POST['employee_id'] ?? "", "as")));
$password    = trim($_POST['password'] ?? "");

if ($employee_id === "" || $password === "") {
    header("Location: index.php?error=1");
    exit;
}


$sql = "SELECT employee_id, employee_name, password
        FROM employee 
        WHERE employee_id = :eid";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":eid", $employee_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php?error=1");
    exit;
}


if ($password !== $user['password']) {
    header("Location: index.php?error=1");
    exit;
}


$job_code = substr($employee_id, 8, 2);
$_SESSION['employee_id']   = $user['employee_id'];
$_SESSION['employee_name'] = $user['employee_name'];
$_SESSION['job_code']      = $job_code;


switch ($job_code) {
    case "01":  // 受付
        header("Location: uw100.php");
        break;

    case "02":  // 配車センター
        header("Location: uw110.php");
        break;

    case "03":  // ドライバー
        header("Location: uw120.php");
        break;

    default:    // 想定外のID形式
        header("Location: index.php?error=1");
        break;
}

exit;
