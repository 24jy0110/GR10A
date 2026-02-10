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
        <li>「予約開始」ボタンを押します。</li>
        <li>表示される「予約日程の選択」画面で、<br>
            利用開始日・開始時間・終了日を選択し、<br>
            「確認」ボタンを押します。</li>
        <li>
        つづいて、<br>
        ・乗客人数<br>
        ・乗車場所／降車場所（都道府県・市区町村・詳細住所）<br>
        ・ご希望の対応言語（第1希望／第2希望［任意］）<br>
          を入力し、「次へ」ボタンを押します。
        </li>
        <li>ご利用人数に対応した車種一覧が表示されますので、<br>
            ご希望の車種を1つ選択し、「次へ」ボタンを押します。</li>
        <li>
          「予約内容確認」画面で日程・乗車場所などの内容を確認し、<br>
          画面下部のフォームに<br>
          ・お名前<br>
          ・お名前（カタカナ）<br>
          ・メールアドレス<br>
          ・電話番号（海外の電話番号も可）<br>
            を入力して「次へ」ボタンを押します。
        </li>
        <li>「ご予約内容の最終確認」画面で、予約内容とお客様情報をもう一度ご確認のうえ、<br>
            「予約を確定する」ボタンを押します。</li>
        <li>画面に「仮予約を受け付けました」と表示され、<br>
            同じ内容の仮予約受付メールが自動送信されます。<br>
            メールの内容をご確認いただき、そのままお待ちください。</li>
        <li>
          後ほどお送りする「予約確定メール」を受信された時点で、
          予約成立となり、手続き完了です。<br>
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
