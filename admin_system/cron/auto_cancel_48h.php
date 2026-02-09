<?php
require_once __DIR__ . "/../includes/db_connect.php";

// 48時間前（仮予約日）
$limitTime = date("Y-m-d H:i:s", time() - 48 * 3600);

$sql = "
SELECT reservation_number, customer_email, customer_name
FROM reservation
WHERE state_code = 'STC01'
  AND reservation_date < :limit
  AND service_start_time > NOW()
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":limit" => $limitTime]);
$list = $stmt->fetchAll();

// --- 更新 & メール送信 ---
foreach ($list as $r) {

    $upd = "
    UPDATE reservation
    SET state_code = 'STC03',
        driver_id = NULL,
        number_plate = NULL
    WHERE reservation_number = :no
    ";
    $stmt2 = $pdo->prepare($upd);
    $stmt2->execute([":no" => $r["reservation_number"]]);

    // --- send mail ---
    $to = $r["customer_email"];
    $subj = "【丸和交通】予約自動キャンセルのお知らせ";
    $msg = "{$r['customer_name']} 様\n\n"
         . "ご予約（予約番号：{$r['reservation_number']}）について、\n"
         . "48時間以内にドライバー受付が無かったため自動キャンセルされました。\n\n"
         . "ご迷惑をおかけし申し訳ございません。\n";

    mb_send_mail($to, $subj, $msg);
}

echo "auto cancel completed\n";
