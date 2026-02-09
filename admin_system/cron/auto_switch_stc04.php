<?php
require_once __DIR__ . "/../includes/db_connect.php";

$sql = "
UPDATE reservation
SET state_code = 'STC04'
WHERE state_code = 'STC02'
  AND service_start_time <= NOW()
  AND service_end_date >= CURDATE()
";
$pdo->exec($sql);

echo "auto switch to STC04 done\n";