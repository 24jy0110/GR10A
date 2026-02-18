<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/check_login.php';

// -----------------------
// 検索条件受け取り
// -----------------------
$office = $_GET['office'] ?? '';
$lang = $_GET['lang'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// -----------------------
// 営業所リスト取得
// -----------------------
$sql_office = "SELECT sales_office_code, sales_office_name FROM sales_office ORDER BY sales_office_code";
$office_list = $pdo->query($sql_office)->fetchAll();

// -----------------------
// 検索用 SQL 生成
// -----------------------
$sql = "
SELECT 
    e.employee_id,
    e.employee_name,
    e.employee_name_kana,
    so.sales_office_name,
    d.language_id_1, d.language_id_2, d.language_id_3,
    lc1.language_category_name AS lang1,
    lc2.language_category_name AS lang2,
    lc3.language_category_name AS lang3
FROM driver d
JOIN employee e ON d.employee_id = e.employee_id
JOIN sales_office so ON e.sales_office_code = so.sales_office_code
LEFT JOIN language_category lc1 ON d.language_id_1 = lc1.language_category_id
LEFT JOIN language_category lc2 ON d.language_id_2 = lc2.language_category_id
LEFT JOIN language_category lc3 ON d.language_id_3 = lc3.language_category_id
WHERE 1 = 1
";

// 営業所
if ($office !== '') {
    $sql .= " AND e.sales_office_code = :office ";
}
// 言語条件（どれか1つでも一致すれば OK）
if ($lang !== '') {
    $sql .= " AND (:lang IN (d.language_id_1, d.language_id_2, d.language_id_3)) ";
}
// キーワード検索
if ($keyword !== '') {
    $sql .= " AND (
        e.employee_name LIKE :kw OR 
        e.employee_name_kana LIKE :kw OR
        e.employee_id LIKE :kw
    ) ";
}

$sql .= " ORDER BY e.employee_id ASC";

$stmt = $pdo->prepare($sql);

if ($office !== '') $stmt->bindValue(':office', $office, PDO::PARAM_STR);
if ($lang !== '') $stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
if ($keyword !== '') $stmt->bindValue(':kw', "%{$keyword}%", PDO::PARAM_STR);

$stmt->execute();
$drivers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ドライバー管理（配車センター）</title>
<style>
body {
    font-family: "Noto Sans JP", sans-serif;
    margin: 40px 60px;
}
h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
}
.search-box {
    padding: 15px;
    border: 2px solid #000;
    width: 80%;
    margin-bottom: 25px;
}
label {
    font-weight: 700;
    margin-right: 10px;
}
input[type=text], select {
    padding: 6px 10px;
    font-size: 16px;
    margin-right: 20px;
}
.btn {
    display: inline-block;
    padding: 10px 28px;
    font-size: 16px;
    border: 2px solid #000;
    background: #fff;
    color: #000;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover {
    background: #000;
    color: #fff;
}
.table-list {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.table-list th, .table-list td {
    border: 1px solid #000;
    padding: 10px;
    font-size: 16px;
}
.table-list th {
    background: #eee;
    font-weight: 700;
}
.btn-detail {
    display: inline-block;
    padding: 6px 16px;
    background: #1e90ff;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
}
.btn-detail:hover {
    background: #0a70d0;
}
.btn-add {
    margin-top: 25px;
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<h1>ドライバー管理（配車センター）</h1>

<!-- ★★ ここから追加：ボタン2つ ★★ -->
<div style="margin-bottom: 20px;">
    <a href="uw110.php" class="btn">戻る（配車センターへ）</a>
    <a href="uw112_06_driver_register.php" class="btn" style="margin-left: 15px;">新規ドライバー登録</a>
</div>
<!-- ★★ ここまで追加 ★★ -->

<!-- 検索フォーム -->
<form method="get" class="search-box">
    <label>営業所：</label>
    <select name="office">
        <option value="">すべて</option>
        <?php foreach ($office_list as $o): ?>
            <option value="<?= $o['sales_office_code'] ?>"
                <?= ($office === $o['sales_office_code']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($o['sales_office_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>対応言語：</label>
    <select name="lang">
        <option value="">すべて</option>
        <?php
        $langs = $pdo->query("SELECT * FROM language_category ORDER BY language_category_id")->fetchAll();
        foreach ($langs as $l):
        ?>
            <option value="<?= $l['language_category_id'] ?>"
                <?= ($lang === $l['language_category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['language_category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>キーワード：</label>
    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>">

    <button type="submit" class="btn">検索</button>
</form>

<!-- 一覧テーブル -->
<table class="table-list">
<tr>
    <th>社員ID</th>
    <th>氏名</th>
    <th>営業所</th>
    <th>対応言語</th>
    <th>操作</th>
</tr>

<?php foreach ($drivers as $d): ?>
<tr>
    <td><?= htmlspecialchars($d['employee_id']) ?></td>
    <td><?= htmlspecialchars($d['employee_name']) ?></td>
    <td><?= htmlspecialchars($d['sales_office_name']) ?></td>
    <td>
        <?= implode(' / ', array_filter([$d['lang1'], $d['lang2'], $d['lang3']])) ?>
    </td>
    <td>
        <a class="btn-detail"
           href="uw112_02_driver_detail.php?employee_id=<?= $d['employee_id'] ?>">
           詳細
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
