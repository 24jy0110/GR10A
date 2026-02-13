<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

$employee = $_SESSION['employee'];
$job_code = $employee['job_code'];
$sales_office = $employee['sales_office_code'];
$keyword     = $_GET['keyword']     ?? '';
$date_start  = $_GET['date_start']  ?? '';
$date_end    = $_GET['date_end']    ?? '';
$state       = $_GET['state']       ?? '';
$car_model   = $_GET['car_model']   ?? '';
$lang        = $_GET['lang']        ?? '';
$office_sel  = $_GET['office']      ?? '';
$where = " WHERE 1=1 ";
$params = [];

/* keyword (reservation number / name / ride&drop place / car model / plate) */
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

    if ($lang === 'LCAT00') {
        // 日本語は全件対象（制限なし）
    } else {
        $where .= " AND (r.lang_pref_1 = :lang OR r.lang_pref_2 = :lang) ";
        $params[':lang'] = $lang;
    }
}


/* office filter */
if ($job_code === "02") {
    // dispatcher: only their own office
    $where .= " AND r.sales_office_code = :office_fix ";
    $params[':office_fix'] = $sales_office;
} else {
    // reception: can filter by user selection
    if (!empty($office_sel)) {
        $where .= " AND r.sales_office_code = :office_sel ";
        $params[':office_sel'] = $office_sel;
    }
}


$limit = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;


$sql = "
SELECT
    r.reservation_number,
    r.service_start_time,
    r.service_end_date,
    r.ride_location,
    r.drop_off_location,
    r.customer_name,
    r.ride_count,
    r.usage_fee,
    r.state_code,
    s.state_name,
    cm.car_model_name,
    so.sales_office_name
FROM reservation r
LEFT JOIN reservation_state s ON r.state_code = s.state_code
LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code
LEFT JOIN sales_office so ON r.sales_office_code = so.sales_office_code
$where
ORDER BY r.service_start_time ASC
LIMIT :limit OFFSET :offset
";


$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$resList = $stmt->fetchAll(PDO::FETCH_ASSOC);


$count_sql = "
SELECT COUNT(*)
FROM reservation r
LEFT JOIN car_model cm ON r.car_model_code = cm.car_model_code    /* ★ FIXED */
$where
";
$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $k => $v) { $count_stmt->bindValue($k, $v); }
$count_stmt->execute();
$total = $count_stmt->fetchColumn();
$pages = ceil($total / $limit);


$carModels = $pdo->query("SELECT car_model_code, car_model_name FROM car_model")->fetchAll();
$offices   = $pdo->query("SELECT sales_office_code, sales_office_name FROM sales_office")->fetchAll();
$langs     = $pdo->query("SELECT language_category_id, language_category_name FROM language_category")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約一覧 | 丸和交通</title>
<link rel="stylesheet" href="assets/app.css">

<style>
.page-title { font-size:32px; font-weight:bold; margin:30px 0 20px; }
.search-box {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    align-items: flex-end;
}


.search-group {
    display: flex;
    flex-direction: column;
    min-width: 180px;
}

.search-group.large {
    min-width: 350px;
}

.search-group label {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 6px;
}

.search-group input,
.search-group select {
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.search-btn {
    height: 38px;
    padding: 0 20px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    align-self: flex-end;
}

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
.pager a { padding:6px 10px; margin:0 3px; border:1px solid #000; text-decoration:none; }

.detail-btn {
    display: inline-block;
    min-width: 70px; 
    padding: 8px 16px;
    background: #1976d2;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    font-weight: 500;
    white-space: nowrap; 
    transition: 0.2s;
}

.detail-btn:hover {
    background: #1565c0;
}

.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.table th,
.table td {
    border: 1px solid #ccc;
    padding: 10px 12px;
    vertical-align: middle;
}
.table td:nth-child(2),
.table td:nth-child(3),
.table td:nth-child(7),
.table td:nth-child(8) { 
    white-space: nowrap;
}
.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 100px;
    height: 38px;
    margin-top: 25px;
    background: #444;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.back-btn:hover {
    background: #222;
}


</style>
</head>

<body>
<?php include __DIR__ . "/includes/header.php"; ?>

<div style="max-width:1200px; margin:0 auto; padding:20px;">

<div class="page-title">予約一覧</div>

<form method="get" class="search-box" style="align-items:flex-end;">

    <!-- キーワード検索 -->
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            キーワード検索
        </label>
        <input type="text"
               name="keyword"
               class="search-input"
               style="width:380px;"
               placeholder="予約番号 / 氏名 / 乗車場所 / 降車場所 / 車種 / ナンバープレート"
               value="<?= htmlspecialchars($keyword) ?>">
    </div>

    <!-- 利用日 -->
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            利用日（開始）
        </label>
        <input type="date"
               name="date_start"
               value="<?= htmlspecialchars($date_start) ?>">
    </div>

    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            利用日（終了）
        </label>
        <input type="date"
               name="date_end"
               value="<?= htmlspecialchars($date_end) ?>">
    </div>

    <!-- 状態 -->
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            状態
        </label>
        <select name="state">
            <option value="">すべて</option>
            <option value="STC01" <?= $state==='STC01'?'selected':'' ?>>仮予約</option>
            <option value="STC02" <?= $state==='STC02'?'selected':'' ?>>予約確定</option>
            <option value="STC04" <?= $state==='STC04'?'selected':'' ?>>運行中</option>
            <option value="STC05" <?= $state==='STC05'?'selected':'' ?>>完了</option>
            <option value="STC03" <?= $state==='STC03'?'selected':'' ?>>キャンセル</option>
        </select>
    </div>

    <!-- 車種 -->
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            車種
        </label>
        <select name="car_model">
            <option value="">すべて</option>
            <?php foreach ($carModels as $cm): ?>
                <option value="<?= $cm['car_model_code'] ?>"
                    <?= $car_model===$cm['car_model_code']?'selected':'' ?>>
                    <?= htmlspecialchars($cm['car_model_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 言語 -->
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            言語
        </label>
        <select name="lang">
            <option value="">すべて</option>
            <?php foreach ($langs as $lg): ?>
                <option value="<?= $lg['language_category_id'] ?>"
                    <?= $lang===$lg['language_category_id']?'selected':'' ?>>
                    <?= htmlspecialchars($lg['language_category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($job_code === "01"): ?>
    <div style="display:flex; flex-direction:column;">
        <label style="font-weight:bold; margin-bottom:4px;">
            営業所
        </label>
        <select name="office">
            <option value="">すべて</option>
            <?php foreach ($offices as $of): ?>
                <option value="<?= $of['sales_office_code'] ?>"
                    <?= $office_sel===$of['sales_office_code']?'selected':'' ?>>
                    <?= htmlspecialchars($of['sales_office_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div style="margin-left:auto;">
        <button class="search-btn">検索</button>
    </div>

</form>

<table class="table">
<tr>
    <th>予約番号</a></th>
    <th>営業所</th>
    <th>乗車日時</a></th>
    <th>乗車場所</th>
    <th>降車場所</th>
    <th>顧客名</a></th>
    <th>車種 / 人数</th>
    <th>状態</a></th>
    <th>操作</th>
</tr>

<?php if (empty($resList)): ?>
<tr>
    <td colspan="8" style="text-align:center; padding:30px; color:#888;">
        該当する予約はありません
    </td>
</tr>
<?php else: ?>
    <?php foreach ($resList as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['reservation_number']) ?></td>
        <td><?= htmlspecialchars($r['sales_office_name']) ?></td>
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
            <a class="detail-btn" href="uw102.php?r=<?= urlencode($r['reservation_number']) ?>">詳細</a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>


</table>

<div class="pager">
<?php for ($i=1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>&keyword=<?= htmlspecialchars($keyword) ?>"><?= $i ?></a>
<?php endfor; ?>
</div>

<a href="uw100.php" class="back-btn">戻る</a>

</div>
</body>
</html>
