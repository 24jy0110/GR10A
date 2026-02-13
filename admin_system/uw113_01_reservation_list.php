<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

$employee = $_SESSION['employee'];
$job_code = $employee['job_code'];
$sales_office = $employee['sales_office_code'];

/* ----------------------------------------------
   GET parameters
---------------------------------------------- */
$keyword     = $_GET['keyword']     ?? '';
$date_start  = $_GET['date_start']  ?? '';
$date_end    = $_GET['date_end']    ?? '';
$state       = $_GET['state']       ?? '';
$car_model   = $_GET['car_model']   ?? '';
$lang        = $_GET['lang']        ?? '';
$office_sel  = $_GET['office']      ?? '';

/* ----------------------------------------------
   WHERE build
---------------------------------------------- */
$where = " WHERE 1=1 ";
$params = [];

/* keyword */
if (!empty($keyword)) {
    $where .= "
       AND (
           r.reservation_number LIKE :kw
        OR r.customer_name LIKE :kw
        OR r.ride_location LIKE :kw
        OR r.drop_off_location LIKE :kw
        OR cm.car_model_name LIKE :kw
        OR r.number_plate LIKE :kw
       )
    ";
    $params[':kw'] = "%{$keyword}%";
}

/* date range */
if (!empty($date_start)) {
    $where .= " AND DATE(r.service_start_time) >= :date_start ";
    $params[':date_start'] = $date_start;
}
if (!empty($date_end)) {
    $where .= " AND DATE(r.service_start_time) <= :date_end ";
    $params[':date_end'] = $date_end;
}

/* state */
if (!empty($state)) {
    $where .= " AND r.state_code = :state ";
    $params[':state'] = $state;
}

/* car model */
if (!empty($car_model)) {
    $where .= " AND r.car_model_code = :car_model ";
    $params[':car_model'] = $car_model;
}

/* language */
if (!empty($lang)) {
    $where .= " AND (r.lang_pref_1 = :lang OR r.lang_pref_2 = :lang) ";
    $params[':lang'] = $lang;
}

/* office rules */
if ($job_code === "02") {
    $where .= " AND r.sales_office_code = :office_fix ";
    $params[':office_fix'] = $sales_office;
} elseif ($job_code === "01") {
    if (!empty($office_sel)) {
        $where .= " AND r.sales_office_code = :office_sel ";
        $params[':office_sel'] = $office_sel;
    }
} else {
    die("アクセス権限がありません。");
}

/* ----------------------------------------------
   pagination
---------------------------------------------- */
$limit = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

/* ----------------------------------------------
   Main SQL
---------------------------------------------- */
$sql = "
SELECT
    r.reservation_number,
    r.service_start_time,
    r.ride_location,
    r.drop_off_location,
    r.customer_name,
    r.ride_count,
    r.state_code,
    s.state_name,
    cm.car_model_name
FROM reservation r
LEFT JOIN reservation_state s ON r.state_code = s.state_code
LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code
$where
ORDER BY r.service_start_time ASC
LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$resList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ----------------------------------------------
   Count
---------------------------------------------- */
$count_sql = "
SELECT COUNT(*)
FROM reservation r
LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code
$where
";

$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $k => $v) {
    $count_stmt->bindValue($k, $v);
}
$count_stmt->execute();
$total = $count_stmt->fetchColumn();
$pages = ceil($total / $limit);

/* ----------------------------------------------
   masters
---------------------------------------------- */
$carModels = $pdo->query("SELECT car_model_code, car_model_name FROM car_model")->fetchAll();
$langs     = $pdo->query("SELECT language_category_id, language_category_name FROM language_category")->fetchAll();
$offices   = $pdo->query("SELECT sales_office_code, sales_office_name FROM sales_office")->fetchAll();

$hasData = ($total > 0);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>配車予定一覧 | 丸和交通</title>
<link rel="stylesheet" href="assets/app.css">

<style>
.page-title { font-size:32px; font-weight:bold; margin:30px 0 20px; }
.search-box { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.search-input { padding:10px; width:260px; border:1px solid #ccc; border-radius:4px; }
.search-btn { padding:10px 18px; background:#000; color:#fff; border:none; border-radius:4px; cursor:pointer; }

.badge { padding:5px 12px; border-radius:12px; color:#fff; font-size:12px; }
.badge-STC01 { background:#ff9800; }
.badge-STC02 { background:#2196f3; }
.badge-STC04 { background:#00bcd4; }
.badge-STC05 { background:#4caf50; }
.badge-STC03 { background:#9e9e9e; }

.table { width:100%; border-collapse:collapse; margin-top:20px; }
.table th, .table td { border:1px solid #ccc; padding:12px; }
.table th { background:#f7f7f7; }

.pager { margin-top:20px; text-align:center; }
.pager a {
    padding:6px 10px;
    margin:0 3px;
    border:1px solid #000;
    text-decoration:none;
}
.pager a.active {
    background:#000;
    color:#fff;
}

.detail-btn {
    display:inline-block;
    padding:6px 14px;
    background:#0A84FF;
    color:#fff;
    border-radius:4px;
    text-decoration:none;
}

.back-btn {
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#555;
    color:#fff;
    border-radius:5px;
    text-decoration:none;
}
</style>
</head>

<body>
<?php include __DIR__ . "/includes/header.php"; ?>

<div style="max-width:1200px; margin:0 auto; padding:20px;">

<div class="page-title">配車予定一覧</div>

<!-- 検索フォーム -->
<form method="get" class="search-box">

<input type="text" name="keyword" class="search-input"
placeholder="予約番号 / 氏名 / 乗車 / 降車 / 車種 / ナンバープレート"
value="<?= htmlspecialchars($keyword) ?>">

<input type="date" name="date_start" value="<?= htmlspecialchars($date_start) ?>">
<input type="date" name="date_end" value="<?= htmlspecialchars($date_end) ?>">

<select name="state">
<option value="">状態（すべて）</option>
<option value="STC01" <?= $state==='STC01'?'selected':'' ?>>仮予約</option>
<option value="STC02" <?= $state==='STC02'?'selected':'' ?>>予約確定</option>
<option value="STC04" <?= $state==='STC04'?'selected':'' ?>>運行中</option>
<option value="STC05" <?= $state==='STC05'?'selected':'' ?>>完了</option>
<option value="STC03" <?= $state==='STC03'?'selected':'' ?>>キャンセル</option>
</select>

<select name="car_model">
<option value="">車種（すべて）</option>
<?php foreach ($carModels as $cm): ?>
<option value="<?= $cm['car_model_code'] ?>" <?= $car_model===$cm['car_model_code']?'selected':'' ?>>
<?= htmlspecialchars($cm['car_model_name']) ?>
</option>
<?php endforeach; ?>
</select>

<select name="lang">
<option value="">言語（すべて）</option>
<?php foreach ($langs as $lg): ?>
<option value="<?= $lg['language_category_id'] ?>" <?= $lang===$lg['language_category_id']?'selected':'' ?>>
<?= htmlspecialchars($lg['language_category_name']) ?>
</option>
<?php endforeach; ?>
</select>

<?php if ($job_code === "01"): ?>
<select name="office">
<option value="">営業所（すべて）</option>
<?php foreach ($offices as $of): ?>
<option value="<?= $of['sales_office_code'] ?>" <?= $office_sel===$of['sales_office_code']?'selected':'' ?>>
<?= htmlspecialchars($of['sales_office_name']) ?>
</option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<button class="search-btn">検索</button>
</form>

<div style="margin-bottom:10px; font-weight:bold;">
検索結果：<?= $total ?>件
</div>

<table class="table">
<tr>
<th>予約番号</th>
<th>乗車日時</th>
<th>乗車場所</th>
<th>降車場所</th>
<th>顧客名</th>
<th>車種 / 人数</th>
<th>状態</th>
<th>操作</th>
</tr>

<?php if (!$hasData): ?>
<tr>
<td colspan="8" style="text-align:center; padding:40px; color:#888;">
該当する予約はありません。
</td>
</tr>
<?php else: ?>
<?php foreach ($resList as $r): ?>
<tr>
<td><?= htmlspecialchars($r['reservation_number']) ?></td>
<td><?= date("Y/m/d H:i", strtotime($r["service_start_time"])) ?></td>
<td><?= htmlspecialchars($r['ride_location']) ?></td>
<td><?= htmlspecialchars($r['drop_off_location']) ?></td>
<td><?= htmlspecialchars($r['customer_name']) ?></td>
<td><?= htmlspecialchars($r['car_model_name'] . " / " . $r['ride_count']) ?></td>
<td>
<span class="badge badge-<?= $r['state_code'] ?>">
<?= htmlspecialchars($r['state_name']) ?>
</span>
</td>
<td>
<a class="detail-btn"
href="uw113_02_reservation_detail.php?r=<?= urlencode($r['reservation_number']) ?>">
詳細
</a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php if ($hasData && $pages > 1): ?>
<div class="pager">
<?php for ($i=1; $i <= $pages; $i++): ?>
<a class="<?= $i==$page ? 'active' : '' ?>"
href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>">
<?= $i ?>
</a>
<?php endfor; ?>
</div>
<?php endif; ?>

<a href="uw110.php" class="back-btn">戻る</a>

</div>
</body>
</html>
