<?php
require_once __DIR__ . "/../includes/db_connect.php";
$sql = "
UPDATE reservation r
JOIN vehicle v ON r.number_plate = v.number_plate
SET
    r.state_code = 'STC04',
    v.vehicle_state = '運行中'
WHERE r.state_code = 'STC02'
  AND r.service_start_time <= NOW()
  AND r.service_end_date >= CURDATE()
  AND r.number_plate IS NOT NULL
";

$affected = $pdo->exec($sql);

echo "auto switch to STC04 done, affected={$affected}\n";