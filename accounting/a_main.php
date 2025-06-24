<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会計システム --Prototype--</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- 独自のCSS (a_main.phpからの相対パスに修正) -->
    <link rel="stylesheet" href="css/siwake.css">
    <link rel="stylesheet" href="css/a_main.css"> 

    <?php
    $page_title = 'home';
    $current_page = 'home'; 
    ?>
</head>
<body>
    <?php
        require_once '../header.php';
    ?>
    <!-- ページ全体のコンテナ -->
    <div class="page-container">
        <?php
        // ----- サイドバー部品を読み込む -----
        require_once './sideber.php'; 
        ?>
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
                <!-- 上段のカード -->
                <section class="card">
                    <div class="card-header">
                        <h3>資産</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">
                            <!-- TODO: 総資産額を表示 -->
                        </p>
                        <div class="comparison">
                            <span class="up">▲ 前月 14.1%</span>
                            <span class="up">▲ 前年 37.8%</span>
                        </div>
                    </div>
                </section>
                <section class="card">
                    <div class="card-header">
                        <h3>負債</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">
                            <!-- TODO: 総負債額を表示 -->
                        </p>
                        <div class="comparison">
                            <span class="up">▲ 前月 5.1%</span>
                            <span class="up">▲ 前年 10.5%</span>
                        </div>
                    </div>
                </section>
                <section class="card">
                    <div class="card-header">
                        <h3>純利益</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body">
                        <p class="metric-value">
                            <!-- TODO: 総純利益を表示 -->
                        </p>
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
                        <!-- TODO: 売上高を表示 -->
                        </div>
                    </div>
                </section>
                <section class="card">
                    <div class="card-header">
                        <h3>収益</h3>
                        <a href="#">> 詳しく見る</a>
                    </div>
                    <div class="card-body chart-card">
                        <div class="gauge-chart" style="--percentage: 80;"></div>
                        <div class="legend">
                        <!-- TODO: 総収益を表示 -->
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
                            <!-- TODO: 総費用を表示 -->
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?php
    // ----- フッター部品を読み込む -----
    require_once 'a_footer.php';
    ?>
    
    <!-- JavaScript -->
    <script>
        // ハンバーガーメニューのスクリプトは省略
    </script>
</body>
</html>
