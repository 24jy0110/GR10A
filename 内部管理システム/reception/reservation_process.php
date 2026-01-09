<?php
session_start();
include("../includes/db_connect.php");

$res_id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if ($action == 'cancel' && !empty($res_id)) {
    try {
        $sql = "UPDATE yoyaku SET reservation_status = 'キャンセル' WHERE reservation_number = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$res_id]);
        header("Location: reservation_list.php?status=success");
    } catch (Exception $e) {
        header("Location: reservation_list.php?status=error");
    }
} else {
    header("Location: reservation_list.php");
}
exit;
