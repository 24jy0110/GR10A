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
            overflow: auto;
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

    <p class="note" data-key="modal_note_index">※当日予約は1日分として計算されます。</p>

    <button id="confirmBtn" class="btn-confirm" data-key="btn_confirm">確認</button>
  </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
// ==== 今日以降しか選べない ====
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
const todayStr = `${yyyy}-${mm}-${dd}`;

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('startDate').min = todayStr;
    document.getElementById('endDate').min = todayStr;
    
    // 页面加载完成后立即应用一次翻译，确保所有 data-key 都被正确翻译
    if (window.applyTranslation) {
        const savedLang = localStorage.getItem('appLang') || 'ja';
        window.applyTranslation(savedLang);
    }
});

// ==== モーダル開閉 ====
const modal = document.getElementById('myModal');
const btn   = document.getElementById('openModal');
const span  = document.getElementsByClassName('close')[0];

btn.onclick  = () => modal.style.display = 'block';
span.onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

// ==== 確認ボタン ====
document.getElementById('confirmBtn').onclick = function(e) {
    e.preventDefault(); 

    const start     = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const end       = document.getElementById('endDate').value;
    
    // 获取当前的语言字典（通过 header.php 脚本设置的全局变量）
    const dict = window.translations ? window.translations[window.currentLang] : null;

    if (!start) {
        // 使用字典中的翻译文本进行 Alert
        alert(dict ? dict['alert_start_date_missing'] : "開始日を選択してください。");
        return;
    }

    if (end && end < start) {
        // 使用字典中的翻译文本进行 Alert
        alert(dict ? dict['alert_end_date_invalid'] : "終了日は開始日以降を選択してください。");
        return;
    }

    document.getElementById('formStartDate').value = start;
    document.getElementById('formStartTime').value = startTime;
    document.getElementById('formEndDate').value   = end;
    document.getElementById('reserveForm').submit();
};
</script>

</body>
</html>