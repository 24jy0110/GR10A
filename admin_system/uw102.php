<?php
require_once __DIR__ . '/includes/check_login.php';
require_once __DIR__ . '/includes/db_connect.php';

/* ---------------------------------------------------
   SESSION employee 必須
--------------------------------------------------- */
$employee = $_SESSION['employee'];
$sales_office = $employee['sales_office_code'];

/* ---------------------------------------------------
   GET パラメータ（101 → 102）
--------------------------------------------------- */
if (!isset($_GET['r'])) {
    header("Location: uw101.php");
    exit;
}
$resNo = $_GET['r'];

/* ---------------------------------------------------
   言語マップ
--------------------------------------------------- */
$langMap = [
    "LCAT00" => "日本語",
    "LCAT01" => "英語",
    "LCAT02" => "中国語",
    "LCAT03" => "韓国語",
    "LCAT04" => "ドイツ語",
    "LCAT05" => "スペイン語",
    "LCAT06" => "フランス語"
];

/* ---------------------------------------------------
   SQL 予約 + 状態 + 車種 + ドライバー + employee 名前
--------------------------------------------------- */
$sql = "
SELECT 
    r.*,
    s.state_name,
    cm.car_model_name,
    cm.car_model_capacity,

    d.language_id_1,
    d.language_id_2,
    d.language_id_3,
    d.driver_email,

    e.employee_name AS driver_name

FROM reservation r
LEFT JOIN reservation_state s ON r.state_code = s.state_code
LEFT JOIN car_model cm ON cm.car_model_code = r.car_model_code
LEFT JOIN driver d ON d.employee_id = r.driver_id
LEFT JOIN employee e ON e.employee_id = d.employee_id
WHERE r.reservation_number = :no
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":no" => $resNo]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    die("該当する予約が見つかりません。");
}

/* ---------------------------------------------------
   表示加工
--------------------------------------------------- */
$rideDate = date("Y/m/d H:i", strtotime($res["service_start_time"]));

$start = new DateTime($res["service_start_time"]);
$end   = new DateTime($res["service_end_date"]);
$days  = $start->diff($end)->days;
if ($start->diff($end)->days == 0) {
    $days = 1;
} else {
    $days = $start->diff($end)->days + 2;
}

/* ドライバー情報 */
$driverName  = $res["driver_name"] ?: "未定";
$driverEmail = $res["driver_email"] ?: "未定";

/* 言語 */
$requestLangs = [];

foreach (["lang_pref_1", "lang_pref_2"] as $col) {
    if (!empty($res[$col]) && isset($langMap[$res[$col]])) {
        $requestLangs[] = $langMap[$res[$col]];
    }
}

$requestLangText = $requestLangs ? implode(" / ", $requestLangs) : "指定なし";


/* 状態色 */
$stateColor = [
    "STC01" => "#ff9800",
    "STC02" => "#2196f3",
    "STC04" => "#00bcd4",
    "STC05" => "#4caf50",
    "STC03" => "#9e9e9e"
];
$badgeColor = $stateColor[$res["state_code"]] ?? "#333";

/* キャンセル可能状態 */
$canCancel = in_array($res["state_code"], ["STC01", "STC02"]);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約詳細 | 丸和交通</title>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            text-align: center;
            width: 400px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-box h3 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #d32f2f;
        }

        .modal-box p {
            margin-bottom: 25px;
            font-size: 15px;
        }

        .modal-btn-area {
            text-align: center;
        }

        .modal-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        body {
            font-family: "Noto Sans JP", sans-serif;
            background: #fafafa;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .section-title {
            margin-top: 35px;
            font-size: 20px;
            font-weight: bold;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            text-align: center;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ccc;
            padding: 12px;
            font-size: 15px;
        }

        .detail-table th {
            background: #f2f2f2;
            width: 220px;
        }

        .state-badge {
            padding: 8px 16px;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
        }

        .btn-area {
            margin-top: 35px;
            text-align: center;
        }

        .btn-back {
            padding: 12px 30px;
            background: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 20px;
        }

        .btn-cancel {
            padding: 12px 30px;
            background: #d32f2f;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>

    <script>
        function cancelReserve(no) {
            if (confirm("本当にキャンセルしますか？")) {
                location.href = "uw103.php?r=" + no;
            }
        }
    </script>

</head>

<body>

    <?php include __DIR__ . "/includes/header.php"; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'cancel'): ?>
        <div class="modal-overlay">
            <div class="modal-box">
                <h3>キャンセル不可</h3>
                <p>
                    すでに運行開始済み、<br>
                    または状態が変更されています。
                </p>
                <div class="modal-btn-area">
                    <a href="uw102.php?r=<?= htmlspecialchars($resNo) ?>" class="modal-btn">
                        戻る
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">

        <h1 style="display:flex; justify-content:space-between; align-items:center;">
            予約詳細
            <span class="state-badge" style="background:<?= $badgeColor ?>">
                <?= htmlspecialchars($res["state_name"]) ?>
            </span>
        </h1>

        <p><b>予約番号：</b><?= htmlspecialchars($res["reservation_number"]) ?></p>

        <!-- ①乗車情報 -->
        <div class="section-title">① 乗車情報</div>
        <table class="detail-table">
            <tr>
                <th>乗車日時</th>
                <td><?= $rideDate ?></td>
            </tr>
            <tr>
                <th>利用日数</th>
                <td><?= $days ?> 日</td>
            </tr>
            <tr>
                <th>降車日</th>
                <td><?= htmlspecialchars($res["service_end_date"]) ?></td>
            </tr>
            <tr>
                <th>乗車場所</th>
                <td><?= nl2br(htmlspecialchars($res["ride_location"])) ?></td>
            </tr>
            <tr>
                <th>降車場所</th>
                <td><?= nl2br(htmlspecialchars($res["drop_off_location"])) ?></td>
            </tr>
        </table>

        <!-- ②乗客情報 -->
        <div class="section-title">② 乗客情報</div>
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td><?= htmlspecialchars($res["customer_name"]) ?></td>
            </tr>
            <tr>
                <th>カタカナ</th>
                <td><?= htmlspecialchars($res["customer_name_kana"] ?: "なし") ?></td>
            </tr>
            <tr>
                <th>電話番号</th>
                <td><?= htmlspecialchars($res["customer_phone"]) ?></td>
            </tr>
            <tr>
                <th>メール</th>
                <td><?= htmlspecialchars($res["customer_email"]) ?></td>
            </tr>
            <tr>
                <th>人数</th>
                <td><?= htmlspecialchars($res["ride_count"]) ?> 名</td>
            </tr>
        </table>

        <!-- ③配車情報 -->
        <div class="section-title">③ 配車情報</div>
        <table class="detail-table">
            <tr>
                <th>車種</th>
                <td><?= htmlspecialchars($res["car_model_name"] ?: "未定") ?></td>
            </tr>
            <tr>
                <th>ナンバープレート</th>
                <td><?= htmlspecialchars($res["number_plate"] ?: "未定") ?></td>
            </tr>
            <tr>
                <th>ドライバー名</th>
                <td><?= htmlspecialchars($driverName) ?></td>
            </tr>
            <tr>
                <th>連絡先</th>
                <td><?= htmlspecialchars($driverEmail) ?></td>
            </tr>
            <tr>
                <th>希望言語</th>
                <td><?= htmlspecialchars($requestLangText) ?></td>
            </tr>
            <tr>
                <th>合計料金</th>
                <td><?= number_format($res["usage_fee"]) ?> 円</td>
            </tr>
        </table>

        <div class="btn-area">
            <a href="uw101.php" class="btn-back">一覧に戻る</a>

            <?php if ($canCancel): ?>
                <button class="btn-cancel" onclick="cancelReserve('<?= $resNo ?>')">
                    予約をキャンセルする
                </button>
            <?php endif; ?>
        </div>

    </div>

</body>

</html>