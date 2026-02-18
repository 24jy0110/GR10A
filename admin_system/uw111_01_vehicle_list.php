<?php
require_once __DIR__ . "/includes/check_login.php";
require_once __DIR__ . "/includes/db_connect.php";
require_once __DIR__ . "/includes/header.php";

/* ------------------------------
   搜索条件取得（GET方式）
------------------------------ */
$sales_office_code = $_GET['sales_office_code'] ?? '';
$vehicle_state = $_GET['vehicle_state'] ?? '';
$car_model_code = $_GET['car_model_code'] ?? '';
$keyword = $_GET['keyword'] ?? '';

/* ------------------------------
   分页设置（1 页 20 件）
------------------------------ */
$per_page = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

/* ------------------------------
   下拉：营业所
------------------------------ */
$sql = "SELECT sales_office_code, sales_office_name FROM sales_office ORDER BY sales_office_code";
$stmt = $pdo->query($sql);
$sales_offices = $stmt->fetchAll();

/* ------------------------------
   下拉：车型（car_model）
------------------------------ */
$sql = "SELECT car_model_code, car_model_name FROM car_model ORDER BY car_model_code";
$stmt = $pdo->query($sql);
$car_models = $stmt->fetchAll();

/* ------------------------------
   车辆状态固定数据
------------------------------ */
$vehicle_states = ["空車", "運行中", "使用停止", "廃車"];

/* ------------------------------
   主查询：车辆数据 + LIMIT / OFFSET
------------------------------ */
$sql = "
    SELECT 
        v.number_plate,
        v.vehicle_state,
        so.sales_office_name,
        cm.car_model_name,
        cm.car_model_capacity
    FROM vehicle v
    JOIN sales_office so ON v.sales_office_code = so.sales_office_code
    JOIN car_model cm ON v.car_model_code = cm.car_model_code
    WHERE 1 = 1
";

$params = [];

if ($sales_office_code !== '') {
    $sql .= " AND v.sales_office_code = :sales_office_code";
    $params[':sales_office_code'] = $sales_office_code;
}
if ($vehicle_state !== '') {
    $sql .= " AND v.vehicle_state = :vehicle_state";
    $params[':vehicle_state'] = $vehicle_state;
}
if ($car_model_code !== '') {
    $sql .= " AND v.car_model_code = :car_model_code";
    $params[':car_model_code'] = $car_model_code;
}
if ($keyword !== '') {
    $sql .= " AND v.number_plate LIKE :keyword";
    $params[':keyword'] = "%{$keyword}%";
}

$sql .= " ORDER BY v.number_plate LIMIT :per_page OFFSET :offset";

$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":per_page", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();
$vehicles = $stmt->fetchAll();

/* ------------------------------
   计算总数量（用于分页）
------------------------------ */
$sql_count = "
    SELECT COUNT(*) AS cnt
    FROM vehicle v
    WHERE 1 = 1
";
$params_count = [];

if ($sales_office_code !== '') {
    $sql_count .= " AND v.sales_office_code = :sales_office_code";
    $params_count[':sales_office_code'] = $sales_office_code;
}
if ($vehicle_state !== '') {
    $sql_count .= " AND v.vehicle_state = :vehicle_state";
    $params_count[':vehicle_state'] = $vehicle_state;
}
if ($car_model_code !== '') {
    $sql_count .= " AND v.car_model_code = :car_model_code";
    $params_count[':car_model_code'] = $car_model_code;
}
if ($keyword !== '') {
    $sql_count .= " AND v.number_plate LIKE :keyword";
    $params_count[':keyword'] = "%{$keyword}%";
}

$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params_count);
$total = $stmt_count->fetchColumn();

$total_pages = ceil($total / $per_page);
/* ------------------------------
   分页安全修正（防止页码越界）
------------------------------ */
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}


?>

<style>
    .container {
        width: 95%;
        max-width: 1100px;
        margin: 20px auto;
        font-family: "Yu Gothic", sans-serif;
    }

    h2 {
        font-size: 22px;
        margin-bottom: 20px;
    }

    .return-btn {
        display: inline-block;
        padding: 8px 14px;
        background: #555;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 30px;
        margin-bottom: 18px;
    }

    .search-box {
        padding: 15px;
        border: 1px solid #ccc;
        margin-bottom: 25px;
        border-radius: 6px;
    }

    .search-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .search-item {
        margin-right: 18px;
        margin-bottom: 8px;
    }

    .search-item label {
        font-size: 14px;
        margin-right: 6px;
    }

    select,
    input[type="text"] {
        padding: 5px;
        font-size: 14px;
    }

    .search-btn {
        padding: 7px 16px;
        background: #000;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 10px 8px;
        text-align: left;
        font-size: 14px;
    }

    th {
        background: #eee;
    }

    .detail-btn {
        padding: 6px 12px;
        background: #1e90ff;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        font-size: 13px;
    }

    .bottom-btn {
        margin-top: 25px;
        margin-bottom: 20px;
        padding: 10px 18px;
        background: #067a0b;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
    }

    .pagination {
        margin-top: 22px;
        text-align: center;
    }

    .page-btn {
        padding: 6px 10px;
        margin: 0 3px;
        background: #eee;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        color: #000;
    }

    .page-btn.active {
        background: #000;
        color: #fff;
    }

    .page-ellipsis {
        margin: 0 6px;
        font-size: 14px;
        color: #555;
    }

    .page-btn.active {
        cursor: default;
    }
</style>


<div class="container">

    <h2>車両ステータス一覧</h2>
    <a class="bottom-btn" href="uw114_01_vehicle_add.php">車両を登録する</a>

    <form method="get" class="search-box">
        <div class="search-row">

            <div class="search-item">
                <label>営業所：</label>
                <select name="sales_office_code">
                    <option value="">指定なし</option>
                    <?php foreach ($sales_offices as $so): ?>
                        <option value="<?= $so['sales_office_code'] ?>"
                            <?= ($sales_office_code === $so['sales_office_code']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($so['sales_office_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="search-item">
                <label>状態：</label>
                <select name="vehicle_state">
                    <option value="">指定なし</option>
                    <?php foreach ($vehicle_states as $state): ?>
                        <option value="<?= $state ?>" <?= ($vehicle_state === $state) ? 'selected' : '' ?>>
                            <?= $state ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="search-item">
                <label>車種：</label>
                <select name="car_model_code">
                    <option value="">指定なし</option>
                    <?php foreach ($car_models as $cm): ?>
                        <option value="<?= $cm['car_model_code'] ?>"
                            <?= ($car_model_code === $cm['car_model_code']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cm['car_model_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>


        <div class="search-row">
            <div class="search-item">
                <label>キーワード：</label>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="ナンバープレート">
            </div>

            <button class="search-btn">検索</button>
        </div>
    </form>



    <table>
        <tr>
            <th>ナンバープレート</th>
            <th>営業所</th>
            <th>車種 / 定員</th>
            <th>状態</th>
            <th>操作</th>
        </tr>

        <?php if (count($vehicles) > 0): ?>
            <?php foreach ($vehicles as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['number_plate']) ?></td>
                    <td><?= htmlspecialchars($v['sales_office_name']) ?></td>
                    <td><?= htmlspecialchars($v['car_model_name']) ?> / <?= $v['car_model_capacity'] ?>名</td>
                    <td><?= htmlspecialchars($v['vehicle_state']) ?></td>
                    <td>
                        <a class="detail-btn"
                            href="uw111_02_vehicle_detail.php?number_plate=<?= urlencode($v['number_plate']) ?>">
                            詳細
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center; padding:20px; color:#555;">
                    該当する車両はありません。
                </td>
            </tr>
        <?php endif; ?>


    </table>



    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $range = 2;

            $start = max(1, $page - $range);
            $end   = min($total_pages, $page + $range);


            if ($start > 1) {
                echo '<a class="page-btn" href="' . buildPageUrl(1) . '">1</a>';
                if ($start > 2) {
                    echo '<span class="page-ellipsis">...</span>';
                }
            }


            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    echo '<span class="page-btn active">' . $i . '</span>';
                } else {
                    echo '<a class="page-btn" href="' . buildPageUrl($i) . '">' . $i . '</a>';
                }
            }


            if ($end < $total_pages) {
                if ($end < $total_pages - 1) {
                    echo '<span class="page-ellipsis">...</span>';
                }
                echo '<a class="page-btn" href="' . buildPageUrl($total_pages) . '">' . $total_pages . '</a>';
            }
            ?>
        </div>
    <?php endif; ?>




    <a class="return-btn" href="uw110.php">← 戻る（配車センター）</a>


</div>

<?php
function buildPageUrl($page)
{
    return '?page=' . $page
        . '&sales_office_code=' . urlencode($_GET['sales_office_code'] ?? '')
        . '&vehicle_state=' . urlencode($_GET['vehicle_state'] ?? '')
        . '&car_model_code=' . urlencode($_GET['car_model_code'] ?? '')
        . '&keyword=' . urlencode($_GET['keyword'] ?? '');
}
?>

<?php require_once __DIR__ . "/includes/footer.php"; ?>