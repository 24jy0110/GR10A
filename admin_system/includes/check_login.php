<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 
 * 正しいログインセッションは:
 * $_SESSION['employee'] = [
 *     'employee_id',
 *     'employee_name',
 *     'sales_office_code',
 *     'job_code'
 * ]
 */
if (!isset($_SESSION['employee']) || empty($_SESSION['employee']['employee_id'])) {
    header("Location: login.php");
    exit;
}
