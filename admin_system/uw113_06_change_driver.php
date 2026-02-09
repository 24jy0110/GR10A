<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   GET reservation number
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw113_01_reservation_list.php");
    exit;
}

$resNo = $_GET['r'];

/* ---------------------------------------------------
   予約データ取得
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    so.sales_office_name,
    cm.car_model_name
FROM reservation r
LEFT JOIN sales_office so ON so.sales_office_code = r.sales_office_code
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) { die("予約データが取得できません。"); }

$office_code = $res["sales_office_code"];
$start_date  = $res["service_start_time"];
$end_date    = $res["service_end_date"];
$req_lang1   = $res["lang_pref_1"];
$req_lang2   = $res["lang_pref_2"];
$current_driver_id = $res["driver_id"];

/* ---------------------------------------------------
   言語名マップ
--------------------------------------------------- */
$langMap = $pdo->query("SELECT language_category_id, language_category_name FROM language_category")->fetchAll(PDO::FETCH_KEY_PAIR);

/* ---------------------------------------------------
   ドライバー一覧（同営業所）
--------------------------------------------------- */
$sql_driver = "
SELECT 
    d.employee_id,
    e.employee_name,
    e.sales_office_code,

    d.language_id_1,
    d.language_id_2,
    d.language_id_3,

    (
        SELECT COUNT(*)
        FROM reservation r2
        WHERE r2.driver_id = d.employee_id
          AND r2.reservation_number != :res_no
          AND r2.service_start_time <= :end_date
          AND r2.service_end_date   >= :start_date
    ) AS used_count

FROM driver d
JOIN employee e ON e.employee_id = d.employee_id
WHERE e.sales_office_code = :office
  AND ( :current_driver IS NULL OR d.employee_id <> :current_driver )
ORDER BY d.employee_id

";

$stmt2 = $pdo->prepare($sql_driver);
$stmt2->execute([
    ":office"         => $office_code,
    ":res_no"         => $resNo,
    ":start_date"     => $start_date,
    ":end_date"       => $end_date,
    ":current_driver" => $current_driver_id
]);


$drivers = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------
   全部过滤语言的函数（避免 undefined key）
--------------------------------------------------- */
function getDriverLanguages($d, $langMap) {
    $list = array_filter([
        $d["language_id_1"],
        $d["language_id_2"],
        $d["language_id_3"]
    ], function($x){
        return !empty($x) && $x !== "LCAT00";
    });

    return implode(" / ", array_map(function($x) use ($langMap){
        return $langMap[$x] ?? "―";
    }, $list));
}

/* ---------------------------------------------------
   グループ分け（完全一致 / 主言語一致）
--------------------------------------------------- */
$group_full = [];
$group_main = [];

foreach ($drivers as $d) {

    if ($d["used_count"] > 0) continue; // スケジュール重複 → NG

    $langs = array_filter([
        $d["language_id_1"],
        $d["language_id_2"],
        $d["language_id_3"]
    ], function($x){
        return !empty($x) && $x !== "LCAT00";
    });

    $has_main = in_array($req_lang1, $langs);
    $has_sub  = $req_lang2 ? in_array($req_lang2, $langs) : true;

    if ($has_main && $has_sub) {
        $group_full[] = $d;   // 完全一致
    } elseif ($has_main) {
        $group_main[] = $d;   // 主言語一致
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー変更（候補一覧） | 丸和交通</title>

<style>
body { font-family:"Noto Sans JP",sans-serif; background:#f5f5f5; }
.container {
    max-width:900px; margin:40px auto; padding:30px;
    background:#fff; border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
.section-title { font-size:22px; font-weight:bold; margin-top:25px; }
.driver-box { margin-top:15px; border:1px solid #ccc; border-radius:6px; padding:15px; }
.driver-row {
    display:flex; justify-content:space-between; align-items:center;
    border-bottom:1px solid #eee; padding:12px 5px;
}
.driver-row:last-child { border-bottom:none; }
.lang { color:#555; font-size:14px; margin-top:3px; }
.btn-select {
    padding:7px 16px; background:#0A84FF; color:#fff;
    border-radius:5px; text-decoration:none;
}
.btn-back {
    display:inline-block; margin-top:25px;
    padding:10px 24px; background:#555; color:#fff;
    border-radius:5px; text-decoration:none;
}
.group-title {
    background:#f0f0f0; padding:10px; border-radius:5px;
    font-weight:bold; margin-top:25px;
}
</style>

</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container">

    <h1 class="section-title">ドライバー変更（候補一覧）</h1>

    <p><b>予約番号：</b><?= htmlspecialchars($resNo) ?></p>
    <p><b>利用日程：</b><?= htmlspecialchars($start_date) ?> ～ <?= htmlspecialchars($end_date) ?></p>
    <p><b>言語要件：</b>
        <?= htmlspecialchars($langMap[$req_lang1] ?? "―") ?>
        <?php if ($req_lang2): ?>
            / <?= htmlspecialchars($langMap[$req_lang2] ?? "―") ?>
        <?php endif; ?>
    </p>

    <!-- ★ 完全一致 -->
    <div class="group-title">★ お客様の言語要件を満たすドライバー</div>
    <?php if (empty($group_full)): ?>
        <p style="color:#d32f2f;">該当するドライバーがいません。</p>
    <?php else: ?>
        <div class="driver-box">
        <?php foreach ($group_full as $d): ?>
            <div class="driver-row">
                <div>
                    <b><?= htmlspecialchars($d["employee_name"]) ?></b>
                    （<?= htmlspecialchars($d["employee_id"]) ?>）
                    <div class="lang"><?= htmlspecialchars(getDriverLanguages($d, $langMap)) ?></div>
                </div>
                <a class="btn-select"
                   href="uw113_07_change_driver_confirm.php?r=<?= $resNo ?>&driver=<?= $d["employee_id"] ?>">
                    選択
                </a>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ☆ 主言語一致 -->
    <div class="group-title">☆ 主言語のみ対応可能なドライバー</div>

    <?php if (empty($group_main)): ?>
        <p style="color:#555;">該当ドライバーなし</p>
    <?php else: ?>
        <div class="driver-box">
        <?php foreach ($group_main as $d): ?>
            <div class="driver-row">
                <div>
                    <b><?= htmlspecialchars($d["employee_name"]) ?></b>
                    （<?= htmlspecialchars($d["employee_id"]) ?>）
                    <div class="lang"><?= htmlspecialchars(getDriverLanguages($d, $langMap)) ?></div>
                </div>
                <a class="btn-select"
                   href="uw113_07_change_driver_confirm.php?r=<?= $resNo ?>&driver=<?= $d["employee_id"] ?>">
                    選択
                </a>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="uw113_02_reservation_detail.php?r=<?= $resNo ?>" class="btn-back">戻る</a>

</div>

</body>
</html>
