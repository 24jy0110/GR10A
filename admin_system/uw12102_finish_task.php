<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ---------------------------------------------------
    GET パラメータチェック
--------------------------------------------------- */
if (!isset($_GET["r"])) {
    header("Location: uw121_driver_tasks.php");
    exit;
}
$resNo = $_GET["r"];

/* ---------------------------------------------------
    SQL：対象予約を取得（安全チェック）
--------------------------------------------------- */
$sql = "
SELECT reservation_number, state_code, driver_id, number_plate
FROM reservation
WHERE reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("予約が見つかりません。");
}

/* ---------------------------------------------------
    ① 予約の担当ドライバー確認
--------------------------------------------------- */
if ($res["driver_id"] !== $driver_id) {
    die("この行程の担当ではありません。");
}

/* ---------------------------------------------------
    ② 状態が STC04（運行中）であるか確認
--------------------------------------------------- */
if ($res["state_code"] !== "STC04") {
    die("この行程は完了処理できません。（運行中のみ可能）");
}

/* ---------------------------------------------------
    トランザクション開始
--------------------------------------------------- */
$pdo->beginTransaction();

try {

    /* ---------------------------------------------------
        ③ 予約ステータス：STC04 → STC05（完了）
    --------------------------------------------------- */
    $sql_upd = "
        UPDATE reservation
        SET state_code = 'STC05',
            service_end_date = NOW()
        WHERE reservation_number = :no
    ";
    $stmt = $pdo->prepare($sql_upd);
    $stmt->execute([":no" => $resNo]);

    /* ---------------------------------------------------
        ④ 車両を空車に戻す（car_id がある場合のみ）
    --------------------------------------------------- */
    if (!empty($res["car_id"])) {

        $sql_car = "
            UPDATE car
            SET car_state = 'CAR01'
            WHERE car_id = :car
        ";
        $stmt = $pdo->prepare($sql_car);
        $stmt->execute([":car" => $res["car_id"]]);
    }

    /* ---------------------------------------------------
        コミット（全成功）
    --------------------------------------------------- */
    $pdo->commit();

    /* ---------------------------------------------------
        完了後画面へ
    --------------------------------------------------- */
    header("Location: uw12102_finish_task_done.php?r=" . urlencode($resNo));
    exit;

} catch (Exception $e) {

    $pdo->rollBack();
    die("行程完了処理中にエラーが発生しました：" . $e->getMessage());
}
?>
