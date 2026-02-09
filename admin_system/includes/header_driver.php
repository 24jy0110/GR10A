<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$employee = $_SESSION['employee'] ?? null;

$employee_name = $employee['employee_name'] ?? '';
$job_code      = $employee['job_code'] ?? '';
$sales_code    = $employee['sales_office_code'] ?? '';

/* ============================================================
   営業所名を取得
============================================================ */
$sales_office_name = "";

if (!empty($sales_code)) {
    require_once __DIR__ . "/db_connect.php";

    $sql = "SELECT sales_office_name FROM sales_office WHERE sales_office_code = :c";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":c" => $sales_code]);
    $row = $stmt->fetch();
    if ($row) {
        $sales_office_name = $row["sales_office_name"];
    }
}

$job_text = [
    "01" => "カスタマーサービス課（受付）",
    "02" => "配車センター",
    "03" => "乗務員課（ドライバー）"
][$job_code] ?? "スタッフ";
?>

<header style="
    width:100%;
    padding:20px 40px;
    box-sizing:border-box;
    border-bottom:1px solid #dcdcdc;
    background:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-family:'Noto Sans JP', sans-serif;
">


    <!-- 左：会社名 -->
    <div>
        <div style="font-size:20px; font-weight:700;">丸和交通株式会社</div>
        <div style="font-size:12px; color:#555;">
            maruwa transportation co.,LTD.<br>
            旅をつなぐ、笑顔を運ぶ。
        </div>
    </div>

    <!-- 右：ユーザー情報 -->
    <div style="text-align:right;">
        <div style="font-size:14px;">
            <?= htmlspecialchars($sales_office_name) ?>　
            <?= htmlspecialchars($job_text) ?>
        </div>

        <div style="font-size:15px; font-weight:600;">
            <?= htmlspecialchars($employee_name) ?> 様
        </div>

        <a href="logout.php"
           style="
             display:inline-block; margin-top:8px; padding:6px 14px;
             border:1px solid #333; border-radius:4px;
             text-decoration:none; color:#333; font-size:13px;">
            ログアウト
        </a>
    </div>
</header>
