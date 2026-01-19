<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>利用方法 | 丸和交通株式会社</title>
    <style>
    .back-row {
      text-align: center;
      padding: 30px 0;
    }

    .btn-back {
      display: inline-block;
      padding: 10px 24px;
      border: 1px solid #000;
      color: #000;
      text-decoration: none;
      border-radius: 4px;
    }
        </style>
</head>
<body>

<?php
$currentPage = 'uw01';
include('includes/header.php');
?>

<div class="content-box">
    <h2>利用方法</h2>

    <h3>予約方法</h3>
    <ol class="usage-steps">
        <li>予約開始ボタンを押す</li>
        <li>利用開始時間と終了日を選択する</li>
        <li>
            乗客人数、車椅子／ベビーカーの有無、  
            乗降車場所、お客様のご希望の対応言語を入力する
        </li>
        <li>お気に入り車種を選択する</li>
        <li>
            入力内容を確認し、  
            お客様のお名前（カタカナ必須）、  
            電話番号（海外電話も構いません）、  
            メールアドレスを入力する
        </li>
        <li>予約内容と入力内容を再度確認し、予約確定ボタンを押す</li>
        <li>仮予約メールを受け取った後、そのままお待ちください</li>
        <li>
            予約確定メールを受信しましたら、  
            予約成功となり、予約手続きは完了です
        </li>
    </ol>

    <div class="back-row">
    <a href="index.php" class="btn-back">ホームページへ</a>
  </div>
</div>
</div>

<?php include("includes/footer.php"); ?>

</body>
</html>
