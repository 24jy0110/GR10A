<?php
session_start();

/* ---------------------------------------------------
   受付（01）以外はアクセス禁止
--------------------------------------------------- */
if (!isset($_SESSION['employee']) || $_SESSION['employee']['job_code'] !== "01") {
    header("Location: login.php");
    exit;
}

$employee = $_SESSION['employee'];
$job_code = $employee["job_code"];
$sales_office = $employee["sales_office_code"];

/* ---------------------------------------------------
   GET パラメータ確認
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw101.php");
    exit;
}
$resNo = $_GET['r'];

require_once __DIR__ . "/includes/db_connect.php";

/* ---------------------------------------------------
   SQL: 対象予約の取得
   受付（01）→ 全件OK
   配車センター（02）→ 営業所制限
--------------------------------------------------- */
$sql = "
SELECT reservation_number, state_code, sales_office_code
FROM reservation
WHERE reservation_number = :no
";

if ($job_code === "02") {
    // 配車センターのみ営業所制限
    $sql .= " AND sales_office_code = :office ";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":no", $resNo, PDO::PARAM_STR);

if ($job_code === "02") {
    $stmt->bindValue(":office", $sales_office, PDO::PARAM_STR);
}

$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("この予約は閲覧・操作できません。（権限エラー）");
}

/* ---------------------------------------------------
   キャンセル可能状態？
   STC01: 仮予約
   STC02: 予約確定
--------------------------------------------------- */
if (!in_array($res['state_code'], ['STC01', 'STC02'])) {
    die("この予約はキャンセルできません。（状態：{$res['state_code']}）");
}

/* ---------------------------------------------------
   キャンセル実行
--------------------------------------------------- */
$updateSql = "
UPDATE reservation
SET state_code = 'STC03'
WHERE reservation_number = :no
";

$stmt = $pdo->prepare($updateSql);
$stmt->execute([
    ':no' => $resNo
]);

/* ---------------------------------------------------
   完了ページへ
--------------------------------------------------- */
header("Location: uw104.php?r=" . urlencode($resNo));
exit;

