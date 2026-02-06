<?php
session_start();

if (!isset($_SESSION['employee'])) {
    header("Location: login.php");
    exit;
}

$employee = $_SESSION['employee'];

// 職種コード（EMPLyyyyaa### → aa が職種）
$job_code = substr($employee['employee_id'], 8, 2);

/**
 * job_code:
 * 01…受付
 * 02…配車センター
 * 03…ドライバー
 */
