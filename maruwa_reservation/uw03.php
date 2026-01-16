<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>車種紹介 | 丸和交通株式会社</title>

  <!-- 共通CSS（保留） -->
  <link rel="stylesheet" href="assets/css/common.css">
  <link rel="stylesheet" href="assets/css/header.css">

  <!-- ==============================
       UW03 車種紹介 页面专用样式
       （已内嵌，避免路径/覆盖问题）
       ============================== -->
  <style>
    .container {
      max-width: 1000px;
      margin: 20px auto;
      padding: 0 20px;
    }

    .note {
      text-align: center;
      font-size: 0.95em;
      color: #666;
      margin-bottom: 30px;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      margin-top: 20px;
    }

    .info-table tr + tr td {
      border-top: 1px solid #ddd;
    }

    .info-table td {
      padding: 16px;
      vertical-align: top;
    }

    .item-image img {
      max-width: 220px;
      height: auto;
      display: block;
    }

    .item-text h2 {
      margin: 0 0 8px;
      font-size: 20px;
    }

    .item-text .summary {
      margin: 0 0 10px;
      color: #555;
      line-height: 1.6;
    }

    .btn-detail {
      padding: 6px 14px;
      border: none;
      cursor: pointer;
      border-radius: 4px;
      background-color: #007bff;
      color: #fff;
      font-size: 14px;
    }

    .btn-detail:hover {
      opacity: 0.9;
    }

    .detail-box {
      margin-top: 10px;
      padding: 14px;
      background-color: #f5f5f5;
      border-radius: 4px;
      display: none;
      font-size: 14px;
      color: #333;
      line-height: 1.7;
    }

    .detail-box ul {
      margin: 8px 0 12px;
      padding-left: 18px;
    }

    .detail-box p {
      margin: 6px 0;
      font-weight: bold;
    }

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

<?php include("includes/header.php"); ?>

<div class="container">
  <h2>車種紹介</h2>
  <p class="note">
    丸和交通のハイヤー車両ラインアップです。<br>
    ご利用シーンに合わせてお選びいただけます。
  </p>

  <table class="info-table">

    <!-- ================= Crown ================= -->
    <tr>
      <td class="item-image">
        <img src="./imgs/crown.jpg" alt="Toyota Crown">
      </td>
      <td class="item-text">
        <h2>トヨタ Crown<br>推奨乗車人数：1～3名</h2>

        <p class="summary">
          格式あるデザインと最先端テクノロジーを備えた Crown は、<br>
          1 日以上のロングドライブでも深いくつろぎを提供します。
        </p>

        <button type="button" class="btn-detail">詳細紹介</button>

        <div class="detail-box">
          <ul>
            <p>█ くつろぎを追求したキャビン</p>
            <li>二重ガラス＋高性能吸音材による静粛性</li>
            <li>後席電動リクライニング＆オットマン</li>
            <li>独立エアコン＋空気清浄機能</li>
          </ul>

          <ul>
            <p>█ ラゲッジ＆電源</p>
            <li>大型トランク（28インチ×2）</li>
            <li>全席USB電源＋車載Wi-Fi</li>
          </ul>

          <ul>
            <p>▶ おすすめ</p>
            <li>役員出張・空港送迎・記念日旅行</li>
          </ul>
        </div>
      </td>
    </tr>

    <!-- ================= Alphard ================= -->
    <tr>
      <td class="item-image">
        <img src="./imgs/alphard.jpg" alt="Toyota Alphard">
      </td>
      <td class="item-text">
        <h2>トヨタ Alphard<br>推奨乗車人数：3～5名</h2>

        <p class="summary">
          ラグジュアリーな室内と広さを兼ね備えた高級ミニバン。
        </p>

        <button type="button" class="btn-detail">詳細紹介</button>

        <div class="detail-box">
          <ul>
            <p>█ 室内空間</p>
            <li>エグゼクティブシート＋オットマン</li>
            <li>独立エアコン＆間接照明</li>
          </ul>

          <ul>
            <p>▶ おすすめ</p>
            <li>家族旅行・VIP送迎・長距離観光</li>
          </ul>
        </div>
      </td>
    </tr>

    <!-- ================= Hiace ================= -->
    <tr>
      <td class="item-image">
        <img src="./imgs/HIACE.png" alt="Toyota Hiace">
      </td>
      <td class="item-text">
        <h2>トヨタ Hiace<br>推奨乗車人数：6～9名</h2>

        <p class="summary">
          大人数・大量荷物に最適な高天井ワゴン。
        </p>

        <button type="button" class="btn-detail">詳細紹介</button>

        <div class="detail-box">
          <ul>
            <p>█ 室内＆積載</p>
            <li>ハイルーフ＋大型ラゲッジ</li>
            <li>全席リクライニング</li>
          </ul>

          <ul>
            <p>▶ おすすめ</p>
            <li>団体旅行・ゴルフ・社員研修</li>
          </ul>
        </div>
      </td>
    </tr>

  </table>

  <div class="back-row">
    <a href="index.php" class="btn-back">ホームページへ</a>
  </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', () => {
      const box = btn.nextElementSibling;
      const open = box.style.display === 'block';
      box.style.display = open ? 'none' : 'block';
      btn.textContent = open ? '詳細紹介' : '閉じる';
    });
  });
});
</script>

</body>
</html>
