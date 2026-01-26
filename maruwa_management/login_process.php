<?php
session_start();

/* ----------------------------------------------------------
   DB接続
   このファイルを読み込むと $pdo が使えるようになる
---------------------------------------------------------- */
require_once __DIR__ . '/db_connect.php';

/* ---------- 入力値取得 ---------- */
$employee_id = $_POST['employee_id'] ?? '';
$password    = $_POST['password'] ?? '';

/* ---------- 社員IDチェック ---------- */
$sql = "SELECT employee_id, employee_name, password 
        FROM employee 
        WHERE employee_id = :eid";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":eid", $employee_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch();

/* ---------- 社員IDが存在しない ---------- */
if (!$user) {
    header("Location: index.php?error=id");
    exit;
}

/* ---------- パスワードエラー ---------- */
if ($password !== $user['password']) {
    header("Location: index.php?error=password");
    exit;
}


/* ---------- 職種コード抽出（EMPLyyyyaaxxx） ---------- */
$job_code = substr($employee_id, 8, 2);

/* ---------- セッション保存 ---------- */
$_SESSION['employee_id']   = $user['employee_id'];
$_SESSION['employee_name'] = $user['employee_name'];

/* ---------- 職種別遷移 ---------- */
switch ($job_code) {
    case "01":
        header("Location: uw100.php");
        break;
    case "02":
        header("Location: uw110.php");
        break;
    case "03":
        header("Location: uw120.php");
        break;
    default:
        header("Location: index.php?error=id");
}

exit;
