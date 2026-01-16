<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title data-key="page_title_index">トップページ | 丸和交通</title>
    <link rel="stylesheet" href="default.css">

    <style>
        .btn-start {
            background-color: black;
            color: white;
            font-size: 32px;
            padding: 20px 60px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-start:hover {
            opacity: 0.9;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px 24px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content label {
            display: block;
            margin-top: 10px;
        }

        .modal-content input[type="date"],
        .modal-content input[type="time"] {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            margin-top: 4px;
        }

        .close {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .note {
            font-size: 12px;
            margin-top: 10px;
            color: #666;
        }

        .btn-confirm {
            margin-top: 15px;
            padding: 8px 20px;
        }
    </style>
</head>
<body>

<?php include("includes/header.php"); ?>

<div style="text-align:center; margin-top: 150px;">
    <button id="openModal" class="btn-start" data-key="btn_start">予約開始</button>
</div>

<form id="reserveForm" action="uw05_01.php" method="post">
    <input type="hidden" name="start_date" id="formStartDate">
    <input type="hidden" name="start_time" id="formStartTime">
    <input type="hidden" name="end_date"   id="formEndDate">
</form>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>

        <h3 data-key="modal_title_index">予約日程の選択</h3>

        <label data-key="modal_label_start_date">開始日:</label>
        <input type="date" id="startDate">

        <label data-key="modal_label_start_time">開始時間:</label>
        <input type="time" id="startTime">

        <label data-key="modal_label_end_date">終了日（任意）:</label>
        <input type="date" id="endDate">

        <p class="note">
            ※ご予約は本日から<strong>3日後</strong>以降のみ可能です。<br>
            ※開始時間は<strong>7:00以降</strong>となります。
        </p>

        <button id="confirmBtn" class="btn-confirm" data-key="btn_confirm">確認</button>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
// ===============================
// 日付制御：今日 + 3日
// ===============================
const today = new Date();
today.setDate(today.getDate() + 3);

const yyyy = today.getFullYear();
const mm   = String(today.getMonth() + 1).padStart(2, '0');
const dd   = String(today.getDate()).padStart(2, '0');
const minDateStr = `${yyyy}-${mm}-${dd}`;

document.addEventListener('DOMContentLoaded', () => {
    const startDate = document.getElementById('startDate');
    const endDate   = document.getElementById('endDate');
    const startTime = document.getElementById('startTime');

    // 日期最小值 = 今天 + 3 天
    startDate.min = minDateStr;
    endDate.min   = minDateStr;

    // 开始时间限制：07:00 以后
    startTime.min = "07:00";

    // 默认值（用户体验）
    startDate.value = minDateStr;
    startTime.value = "07:00";

    if (window.applyTranslation) {
        const savedLang = localStorage.getItem('appLang') || 'ja';
        window.applyTranslation(savedLang);
    }
});

// ===============================
// モーダル開閉
// ===============================
const modal = document.getElementById('myModal');
const btn   = document.getElementById('openModal');
const span  = document.querySelector('.close');

btn.onclick  = () => modal.style.display = 'block';
span.onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

// ===============================
// 確認処理
// ===============================
document.getElementById('confirmBtn').onclick = function(e) {
    e.preventDefault();

    const start     = startDate.value;
    const startTimeVal = startTime.value;
    const end       = endDate.value;

    const dict = window.translations ? window.translations[window.currentLang] : null;

    if (!start) {
        alert(dict ? dict['alert_start_date_missing'] : "開始日を選択してください。");
        return;
    }

    if (end && end < start) {
        alert(dict ? dict['alert_end_date_invalid'] : "終了日は開始日以降を選択してください。");
        return;
    }

    if (startTimeVal < "07:00") {
        alert("開始時間は7:00以降を選択してください。");
        return;
    }

    document.getElementById('formStartDate').value = start;
    document.getElementById('formStartTime').value = startTimeVal;
    document.getElementById('formEndDate').value   = end;

    document.getElementById('reserveForm').submit();
};
</script>

</body>
</html>
