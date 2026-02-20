<?php
session_start();
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";

$driver = $_SESSION["employee"];
$driver_id = $driver["employee_id"];

/* ============================================================
    司机语言取得
============================================================ */
$sql_lang = "SELECT language_id_1, language_id_2, language_id_3 
             FROM driver WHERE employee_id = :id";
$stmt = $pdo->prepare($sql_lang);
$stmt->execute([":id" => $driver_id]);
$langRow = $stmt->fetch();

$myLangs = array_filter([
    $langRow["language_id_1"] ?? null,
    $langRow["language_id_2"] ?? null,
    $langRow["language_id_3"] ?? null
]);

/* ============================================================
    获取司机已接订单（避免冲突）
============================================================ */
$sql_myJobs = "
SELECT service_start_time, service_end_date
FROM reservation
WHERE driver_id = :id AND state_code IN ('STC02','STC04')
";
$stmt = $pdo->prepare($sql_myJobs);
$stmt->execute([":id" => $driver_id]);
$myJobs = $stmt->fetchAll();

function timeConflict($a1, $a2, $b1, $b2)
{
    return !($a2 <= $b1 || $b2 <= $a1);
}

/* ============================================================
    获取所有 STC01（仮予約）
============================================================ */
$sql_order = "
SELECT *
FROM reservation
WHERE state_code = 'STC01'
ORDER BY reservation_date ASC
";
$orders = $pdo->query($sql_order)->fetchAll();

$validOrders = [];

foreach ($orders as $o) {

    $o_start = strtotime($o["service_start_time"]);
    $o_end   = strtotime($o["service_end_date"]);

    /* -------- 语言匹配 -------- */
    $lang_ok = false;
    if (in_array($o["lang_pref_1"], $myLangs)) $lang_ok = true;
    if (!empty($o["lang_pref_2"]) && in_array($o["lang_pref_2"], $myLangs)) $lang_ok = true;

    if (!$lang_ok) continue;

    /* -------- 时间冲突 -------- */
    $conflict = false;
    foreach ($myJobs as $mj) {
        if (timeConflict(
            strtotime($mj["service_start_time"]),
            strtotime($mj["service_end_date"]),
            $o_start,
            $o_end
        )) {
            $conflict = true;
            break;
        }
    }
    if ($conflict) continue;

    $validOrders[] = $o;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>新規依頼一覧 | 丸和交通</title>

    <style>
        body {
            font-family: "Noto Sans JP", sans-serif;
            background: #fafafa;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        .table th {
            background: #f2f2f2;
            font-weight: 700;
        }

        .detail-btn {
            padding: 8px 16px;
            background: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .detail-btn:hover {
            background: #333;
        }
    </style>

</head>

<body>

    <?php include __DIR__ . "/includes/header_driver.php"; ?>

    <div class="container">

        <h1>新規依頼一覧（接単可能：<?= count($validOrders) ?> 件）</h1>

        <?php if (empty($validOrders)): ?>
            <p style="font-size:18px; text-align:center; margin:40px 0;">
                現在、お受けいただける依頼はありません。
            </p>
        <?php else: ?>

            <table class="table">
                <tr>
                    <th>予約番号</th>
                    <th>乗車日時</th>
                    <th>乗車場所</th>
                    <th>顧客名</th>
                    <th>操作</th>
                </tr>

                <?php foreach ($validOrders as $o): ?>
                    <tr>
                        <td><?= htmlspecialchars($o["reservation_number"]) ?></td>
                        <td><?= date("Y/m/d H:i", strtotime($o["service_start_time"])) ?></td>
                        <td><?= nl2br(htmlspecialchars($o["ride_location"])) ?></td>
                        <td><?= htmlspecialchars($o["customer_name"]) ?></td>
                        <td>
                            <a href="uw122_01_order_detail.php?r=<?= $o["reservation_number"] ?>" class="detail-btn">
                                詳細を見る
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>

        <?php endif; ?>

    </div>
    <div style="text-align:center; margin-top:35px;">
    <a href="uw120.php"
       style="
           display:inline-block;
           padding:12px 28px;
           background:#000;
           color:#fff;
           text-decoration:none;
           border-radius:6px;
           font-size:16px;
       ">
        メニューへ戻る
    </a>
</div>
</body>

</html>