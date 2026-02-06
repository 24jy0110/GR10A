<?php
session_start();
require_once __DIR__ . '/db_connect.php';

/* ---------- 入力取得 ---------- */
$employee_id = strtoupper(trim(mb_convert_kana($_POST['employee_id'] ?? "", "as")));
$password    = trim($_POST['password'] ?? "");

/* 入力チェック */
if ($employee_id === "" || $password === "") {
    header("Location: index.php?error=1");
    exit;
}

/* ----------------------------------------------------------
   JOIN で社員情報 + 支店名 を取得
---------------------------------------------------------- */
$sql = "
SELECT 
    e.employee_id,
    e.employee_name,
    e.password,
    e.sales_office_code,
    s.sales_office_name
FROM employee e
LEFT JOIN sales_office s
       ON e.sales_office_code = s.sales_office_code
WHERE e.employee_id = :eid
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":eid", $employee_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ---------- 社員存在チェック ---------- */
if (!$user) {
    header("Location: index.php?error=1");
    exit;
}

/* ---------- パスワードチェック（ハッシュなし） ---------- */
if ($password !== $user['password']) {
    header("Location: index.php?error=1");
    exit;
}

/* ----------------------------------------------------------
   職種コード抽出（EMPLyyyyaaxxx → aa = 8-9 文字目）
---------------------------------------------------------- */
$job_code = substr($employee_id, 8, 2);

/* 職種名（部署名）判定 */
$department_name = "";

switch ($job_code) {
    case "01": $department_name = "受付"; break;
    case "02": $department_name = "配車センター"; break;
    case "03": $department_name = "ドライバー"; break;
    default:
        header("Location: index.php?error=1");
        exit;
}

/* ----------------------------------------------------------
   セッション保存（内部ページで使用）
---------------------------------------------------------- */
$_SESSION['employee_id']        = $user['employee_id'];
$_SESSION['employee_name']      = $user['employee_name'];
$_SESSION['job_code']           = $job_code;

$_SESSION['sales_office_code']  = $user['sales_office_code'];  // OFCE001
$_SESSION['sales_office_name']  = $user['sales_office_name'];  // 亀沢本社

$_SESSION['department_name']    = $department_name;

/* ----------------------------------------------------------
   職種別ページ遷移
---------------------------------------------------------- */
switch ($job_code) {
    case "01": header("Location: uw100.php"); break;
    case "02": header("Location: uw110.php"); break;
    case "03": header("Location: uw120.php"); break;
}

exit;
