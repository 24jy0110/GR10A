<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>車種紹介 | 丸和交通株式会社</title>
    <link rel="stylesheet" href="./assets/app.css">
    <style>
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 20px;
        }

        .info-table tr+tr td {
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

        .item-text h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .item-text .summary {
            margin: 0 0 10px;
            color: #555;
            line-height: 1.6;
        }

        .btn-detail {
            padding: 6px 12px;
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
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            display: none;
            /* 默认隐藏 */
            font-size: 14px;
            color: #333;
            line-height: 1.6;
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
            <tr>
                <td class="item-image">
                    <img src="imgs/crown.jpg" alt="crown">
                </td>
                <td class="item-text">
                    <h2>トヨタ Crown<br>
                        推奨乗車人数：1~3名</h2>

                    <p class="summary">
                        格式あるデザインと最先端テクノロジーを備えた Crown は、<br>
                        1 日以上のロングドライブでも乗る人すべてに深いくつろぎを提供します。<br>
                        都内・近郊へのビジネスや空港送迎などに幅広くご利用いただけます。
                    </p>

                    <button type="button" class="btn-detail">詳細紹介</button>

                    <div class="detail-box">
                        <ul>
                            <p>█ くつろぎを追求したキャビン</p>
                            <li>静粛性クラス最上位。<br>
                                二重ガラスと高性能吸音材により、高速走行中でも会話がクリア。</li>
                            <li>後席電動リクライニング＆オットマン<br>
                                最大 30° のリクライニングとフットレストで、移動中もビジネスクラス級の休息。</li>
                            <li>独立エアコン＋イオン空気清浄<br>
                                前後席で温度を個別設定。花粉・PM2.5 も自動除去し、空気まで快適。</li>
                        </ul>
                        <ul>
                            <p>█ 長期旅行でも安心のラゲッジ＆電源</p>
                            <li>大型ラゲッジルーム<br>
                                28 インチスーツケース 2 個＋ボストンバッグを収納可能。ゴルフバッグも縦置き対応。</li>
                            <li>全席 USB-C/USB-A+車載Wi-Fi<br>
                                ノート PC やカメラを常時給電し、リモートワークやオンラインミーティングもスムーズ。</li>
                        </ul>
                        <ul>
                            <p>█ ドライバーとの豊かな時間</p>
                            <li>パノラミックガラスルーフ（一部車両） <br>
                                風景を楽しみながら、ガイドの案内もクリアに届きます。</li>
                            <li>英語・中国語対応ドライバー <br>
                                観光案内やビジネス通訳にも対応し、VIP 送迎も安心。</li>
                        </ul>
                        <ul>
                            <p>█ 安全と環境性能</p>
                            <li>Toyota Safety Sense（自動ブレーキ／全車速 ACC／LTA）を標準装備し、安全運行をサポート。</li>
                            <li>ハイブリッド採用でセダンクラス屈指の低燃費 19 km/L、長距離でも環境負荷と燃料費を抑制。 </li>
                        </ul>
                        <ul>
                            <p>▶ こんなシーンにおすすめ</p>
                            <li>役員出張や国際会議の都市間移動</li>
                            <li>ご両親や記念日旅行など、ゆったりとしたプライベート観光 </li>
                            <li>ゴルフ・温泉・美食巡りなど 2 泊 3 日以上の周遊ツアー<br>
                                Crownは、移動時間を上質な滞在時間へと昇華させるプレミアムセダン。<br>
                                静けさと心地よさを備えた快適空間を、ぜひ長期チャーターでご体感ください。</li>
                        </ul>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="item-image">
                    <img src="imgs/alphard.jpg" alt="alphard">
                </td>
                <td class="item-text">
                    <h2>トヨタ Alphard<br>
                        推奨乗車人数：3~5名</h2>

                    <p class="summary">
                        広い室内とラグジュアリーな装備で人気の Alphard は、1 日以上の観光周遊やファミリー旅行、<br>
                        ビジネス視察に最適な 4〜6 名乗車のハイエンドミニバンです。<br>
                        ゆったりとしたキャビンで移動そのものが特別な体験へと変わります。
                    </p>

                    <button type="button" class="btn-detail">詳細紹介</button>

                    <div class="detail-box">
                        <ul>
                            <p>█ ラグジュアリーな室内空間</p>
                            <li>エグゼクティブパワーシート<br>
                                2 列目は電動オットマン付きキャプテンシート。最大 50° のリクライニングで長距離でも快適。</li>
                            <li>天井独立エアコン & LEDアンビエントライト<br>
                                後席専用の温調パネルと、穏やかな間接照明が“プライベートラウンジ”の雰囲気を演出。</li>
                            <li>静粛性の高いキャビン<br>
                                吸音ガラスと高剛性ボディにより、会話や映画鑑賞を妨げない静けさを実現。</li>
                        </ul>
                        <ul>
                            <p>█ 大容量ラゲッジ＆多目的収納</p>
                            <li>3 列目跳ね上げで広々トランク<br>
                                スーツケース、ベビーカー、スポーツ用品も余裕で収納。</li>
                            <li>室内ポケット & USB 電源 <br>
                                各席に小物ポケット、USB-C／USB-A を完備。長旅でも充電切れの心配なし。</li>
                        </ul>
                        <ul>
                            <p>█ エンタメ & リラックス機能</p>
                            <li>12.1 インチ後席モニター（一部車両）<br>
                                長時間の移動も映画や音楽で快適に。</li>
                            <li>天井大型サンルーフ<br>
                                昼は自然光、夜は星空が楽しめ、旅の高揚感を演出。</li>
                        </ul>

                        <ul>
                            <p>█ 安全 & 走行性能</p>
                            <li>Toyota Safety Sense（自動ブレーキ・全車速クルーズ）＋ 360°パノラミックビュー</li>
                            <li>3.5L V6 or 2.5L ハイブリッドでパワフルかつ低燃費。坂道の多い観光ルートでも余裕の走り。 </li>
                        </ul>
                        <ul>
                            <p>▶ こんなシーンにおすすめ</p>
                            <li>家族旅行：チャイルドシートもゆったり設置、荷物も余裕</li>
                            <li>小グループのプライベートツアー：友人同士での温泉・ゴルフ・ワイナリーツアーなど </li>
                            <li>VIP送迎・商談ツアー：車内で資料確認やオンラインミーティングも可能<br>
                                Alphard は、自宅リビングのくつろぎとホテルラウンジの上質さを兼ね備えた MPV。<br>
                                “移動も旅の一部” と考えるお客様に、最上級の時間をご提供します。</li>
                        </ul>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="item-image">
                    <img src="imgs/HIACE.png" alt="hiace">
                </td>
                <td class="item-text">
                    <h2>トヨタ Hiace<br>
                        推奨乗車人数：6~9名</h2>

                    <p class="summary">
                        高いルーフとスクエアなボディで抜群の積載力を誇る Hiace コミューター仕様は、ファミリーや<br>
                        友人グループ、アウトドア仲間の長期チャーターに最適なワゴンモデルです。<br>
                        広さと機能性を両立し、移動も滞在もストレスフリーにサポートします。
                    </p>

                    <button type="button" class="btn-detail">詳細紹介</button>

                    <div class="detail-box">
                        <ul>
                            <p>█ 余裕の室内＆ラゲッジスペース</p>
                            <li>ワイドボディ・ハイルーフ<br>
                                室内高が充分にあるため、大人でも立ったまま乗降が可能。</li>
                            <li>大型トランクエリア<br>
                                スーツケース、キャンプギア、ゴルフバッグもまとめて積載。</li>
                            <li>リクライニング付シート<br>
                                全席3点式シートベルト+角度調整で長距離でも快適。</li>
                        </ul>
                        <ul>
                            <p>█ グループ移動を快適にする装備</p>
                            <li>独立リアエアコン&ヒーター<br>
                                車内全体を均一に空調。夏の海、冬の雪山でも安心。</li>
                            <li>各席 USB 電源 & ドリンクホルダー<br>
                                スマホやカメラを同時充電、移動中もバッテリー切れの不安なし。</li>
                            <li>大型サイドウインドウ<br>
                                景色を遮らず、観光ガイドの説明も臨場感アップ。</li>
                        </ul>
                        <ul>
                            <p>█ 走行と安全</p>
                            <li>トヨタ最新の安全装備（プリクラッシュセーフティ／レーンディパーチャーアラート等）を搭載。</li>
                            <li>ディーゼルターボのトルクフルな走りで山道もスムーズ。 </li>
                        </ul>
                        <ul>
                            <p>▶ こんなシーンにおすすめ</p>
                            <li>ファミリーや友人グループの周遊旅行・フェス遠征</li>
                            <li>ゴルフ・スキー・サイクリングなど大型荷物を伴うアクティビティ </li>
                            <li>社員旅行や研修など、少人数チームの長距離移動<br>
                                Hiace は“荷物も思い出も、たっぷり積んで走る”頼れるワゴン。<br>
                                広さと機動力で、グループ旅をさらに快適に彩ります。</li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>

        <div class="back-row">
            <a href="index.php" class="btn-back" aria-label="トップへ戻る"> ホームページへ</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-detail');
            buttons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const detail = this.nextElementSibling;
                    if (!detail) return;
                    const isHidden = (detail.style.display === '' || detail.style.display === 'none');
                    detail.style.display = isHidden ? 'block' : 'none';
                    this.textContent = isHidden ? '閉じる' : '詳細表示';
                });
            });
        });
    </script>

</body>

</html>