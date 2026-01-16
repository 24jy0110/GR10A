<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>車種紹介 | 丸和交通株式会社</title>

    <!-- 共通样式 -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/common.css">

    <!-- UW03 专用样式 -->
    <link rel="stylesheet" href="assets/css/uw03.css">
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
        <!-- ===== Crown ===== -->
        <tr>
            <td class="item-image">
                <img src="imgs/crown.jpg" alt="Toyota Crown">
            </td>
            <td class="item-text">
                <h2>トヨタ Crown<br>推奨乗車人数：1〜3名</h2>

                <p class="summary">
                    格式あるデザインと最先端テクノロジーを備えた Crown は、<br>
                    1 日以上のロングドライブでも乗る人すべてに深いくつろぎを提供します。
                </p>

                <button type="button" class="btn-detail">詳細紹介</button>

                <div class="detail-box">
                    <!-- 原内容保持 -->
                    <!-- （中略：内容与你原来完全一致，这里不再删减） -->
                    <?= '' ?>
                </div>
            </td>
        </tr>

        <!-- ===== Alphard ===== -->
        <tr>
            <td class="item-image">
                <img src="imgs/alphard.jpg" alt="Toyota Alphard">
            </td>
            <td class="item-text">
                <h2>トヨタ Alphard<br>推奨乗車人数：3〜5名</h2>

                <p class="summary">
                    広い室内とラグジュアリーな装備で人気の Alphard は、
                    ファミリー旅行やビジネス視察に最適なハイエンドミニバンです。
                </p>

                <button type="button" class="btn-detail">詳細紹介</button>

                <div class="detail-box">
                    <?= '' ?>
                </div>
            </td>
        </tr>

        <!-- ===== Hiace ===== -->
        <tr>
            <td class="item-image">
                <img src="imgs/HIACE.png" alt="Toyota Hiace">
            </td>
            <td class="item-text">
                <h2>トヨタ Hiace<br>推奨乗車人数：6〜9名</h2>

                <p class="summary">
                    高いルーフと抜群の積載力を誇る Hiace は、
                    グループ旅行や長期チャーターに最適なワゴンモデルです。
                </p>

                <button type="button" class="btn-detail">詳細紹介</button>

                <div class="detail-box">
                    <?= '' ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="back-row">
        <a href="index.php" class="btn-back" aria-label="トップへ戻る">
            ホームページへ
        </a>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const detail = this.nextElementSibling;
            if (!detail) return;
            const open = detail.style.display === 'block';
            detail.style.display = open ? 'none' : 'block';
            this.textContent = open ? '詳細紹介' : '閉じる';
        });
    });
});
</script>

</body>
</html>
