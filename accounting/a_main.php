<?php
// ----- このページ固有の情報を定義 -----
$page_title = '会計システム --Prototype--';
$current_page = 'home'; 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- 独自のCSS -->
    <link rel="stylesheet" href="css/a_main.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/siwake.css">
</head>
<?php
        require_once __DIR__ . '/../header.php';
        require_once __DIR__ . '/../config.php';

    ?>
<body>
    <!-- ページ全体のコンテナ -->
    <div class="page-container">
        <?php
        // ----- サイドバー部品を読み込む -----
        require_once __DIR__ . '/sidebar.php';
        ?>
        <!-- メインコンテンツ -->
        <main class="main-content">
            <!-- 1. ページのヘッダー部分 -->
            <header class="dashboard-header">
                <div class="date-range">
                    <h1>会計システム--Prototype--</h1>
                </div>
            </header>
            
            <!-- 2. ダッシュボードのカード部分 -->
            <div class="dashboard-grid main-content">
                <!-- 上段のカード -->
                <!-- 資産 -->
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
                <!-- 負債 -->
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
                <!-- 純利益 -->
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
                <!-- 売上 -->
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
                <!-- 収益 -->
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
                <!-- 費用 -->
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
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle-btn');
            const body = document.body;

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
</body>
</html>
