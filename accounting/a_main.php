<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
        <?php
        // $page_titleにタイトルを入れる。nullの場合 ”私のウェブサイト” を表示
        if (isset($page_title)) {
            echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' | 私のウェブサイト';
        } else {
            echo '私のウェブサイト';
        }
        ?>
    </title>
  <!-- Bootstrap (主にテーブルなどのコンポーネント用) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- 独自のレイアウトCSS -->
  <link rel="stylesheet" href="../css/siwake.css">
  <link rel="stylesheet" href="/accounting/css/a_mian.css">
    <?php
    $page_title = 'home';
    $current_page = 'home'; 
    ?>
</head>
<body>
    <div class="page-container">
        <!-- メインコンテンツ -->
        <main class="main-content">
            <!-- 1. ヘッダー部分 -->
            <header class="dashboard-header">
                <div class="date-range">
                    <h1>会計システム--Prototype--</h1>
                </div>
            </header>

            <!-- 2. ダッシュボードのカード部分 -->
            <div class="dashboard-grid">
                
                <!-- 上段のカード (数値中心) 左-->
                <section class="card">
                    <div class="card-header">
                        <h3>資産</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">
                            <!-- 737 <small>名 [217組]</small> -->
                            //TODO 総資産額を表示
                        </p>
                        <div class="comparison">
                            <span class="up">▲ 前月 14.1%</span>
                            <span class="up">▲ 前年 37.8%</span>
                        </div>
                    </div>
                </section>
                <!-- 真ん中 -->
                <section class="card">
                    <div class="card-header">
                        <h3>負債</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">1073 <small>名 [349組]</small></p>
                        <div class="comparison">
                            <span class="up">▲ 前月 5.1%</span>
                            <span class="up">▲ 前年 10.5%</span>
                        </div>
                    </div>
                </section>
                <!-- 右 -->
                <section class="card">
                    <div class="card-header">
                        <h3>純利益</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">37.9<small>%</small></p>
                        <div class="comparison">
                            <!-- <span class="down">▼ 前月比 0.5%</span>
                            <span class="up">▲ 前年比 2.1%</span> -->
                            //TODO 総純利益を表示
                        </div>
                    </div>
                </section>

                <!-- 下段のカード (グラフ中心) -->
                <section class="card">
                    <div class="card-header">
                        <h3>売上</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body chart-card">
                        <div class="gauge-chart" style="--percentage: 52;"></div>
                        <div class="legend">
                        <!--   <div><span class="color-dot red"></span>席のみ: 563名</div>
                            <div><span class="color-dot gray"></span>コースあり: 510名</div> -->
                            //TODO 売上高を表示
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h3>収益</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body chart-card">
                        <div class="gauge-chart" style="--percentage: 68;"></div>
                        <div class="legend">
                            <!-- <div><span class="color-dot red"></span>予約: 737名</div>
                            <div><span class="color-dot gray"></span>直接来店: 336名</div> -->
                            //TODO 総収益を表示
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h3>費用</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body chart-card">
                        <div class="gauge-chart" style="--percentage: 61;"></div>
                        <div class="legend">
                            <!-- <div><span class="color-dot red"></span>新規: 649名</div>
                            <div><span class="color-dot gray"></span>リピート: 404名</div> -->
                            //TODO 総費用を表示
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <?php
    require_once 'a_footer.php';
    ?>
</body>
</html>
