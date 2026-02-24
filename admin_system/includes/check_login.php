<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* -----------------------------------
   ログイン済みチェック
----------------------------------- */
if (!isset($_SESSION['employee']) || empty($_SESSION['employee']['employee_id'])) {
    header("Location: login.php");
    exit;
}

/* -----------------------------------
   退職ドライバーはログイン禁止
----------------------------------- */
$emp_id = $_SESSION['employee']['employee_id'];

require_once __DIR__ . '/db_connect.php';

$sql = "SELECT driver_status FROM driver WHERE employee_id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $emp_id]);
$status = $stmt->fetchColumn();

/* ドライバーであり、かつ「退職」の場合ログアウト */
if ($status === '退職') {
    session_destroy();
    header("Location: login.php?error=retired");
    exit;
}