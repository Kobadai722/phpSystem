<?php
// ----- ページ設定 -----
$page_title = '売上推移グラフ';
$current_page = 'graph'; // サイドバーで「グラフ」がアクティブになるように

// ----- 必要な部品を読み込み -----
// パスは実際のファイル配置に合わせて調整してください
require_once __DIR__ . '/../a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';


// =================================================================
// グラフ用データ取得・計算処理
// =================================================================
try {
    // --- 1. ユーザーが選択した年を取得 ---
    // GETパラメータがなければ、現在の年をデフォルトに設定
    $selected_year = $_GET['year'] ?? date('Y');

    // --- 2. 選択された年の月別売上データを取得 ---
    $sql = "
        SELECT 
            MONTH(h.ENTRY_DATE) as sales_month,
            SUM(e.AMOUNT) as monthly_total
        FROM 
            JOURNAL_ENTRIES e
        JOIN 
            JOURNAL_HEADERS h ON e.HEADER_ID = h.ID
        WHERE 
            e.ACCOUNT_ID = 8 AND YEAR(h.ENTRY_DATE) = ?
        GROUP BY 
            sales_month
        ORDER BY 
            sales_month ASC
    ";
    
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$selected_year]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. JavaScriptで使いやすいようにデータを整形 ---
    // 1月から12月までの売上データを格納する配列を0で初期化
    $monthly_sales_data = array_fill(1, 12, 0);

    // データベースから取得した結果を配列に格納
    foreach ($results as $row) {
        $monthly_sales_data[(int)$row['sales_month']] = (int)$row['monthly_total'];
    }

    // グラフのラベル（1月, 2月...）を作成
    $chart_labels = [];
    for ($m = 1; $m <= 12; $m++) {
        $chart_labels[] = $m . '月';
    }

    // グラフのデータ（売上額）を準備
    $chart_data = array_values($monthly_sales_data);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<body>
    <!-- ハンバーガーメニュー (Offcanvasを表示させるためのボタン) -->
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php require_once __DIR__ . '/sidebar_bootstrap.php'; ?>

    <!-- ページ全体のコンテナ -->
    <div class="page-container">
        <!-- メインコンテンツ -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
            </header>

            <!-- 年選択フォーム -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="year" class="form-label">表示年</label>
                            <select name="year" id="year" class="form-select">
                                <?php
                                $current_year = date('Y');
                                for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                    $selected_attr = ($y == $selected_year) ? 'selected' : '';
                                    echo "<option value='{$y}' {$selected_attr}>{$y}年</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">表示</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- グラフ表示エリア -->
            <div class="card">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($selected_year); ?>年 月別売上推移</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesLineChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap と Chart.js の JavaScript を読み込み -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- グラフ描画用スクリプト -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PHPからグラフ用のデータをJSON形式で受け取る
            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const chartData = <?php echo json_encode($chart_data); ?>;

            const ctx = document.getElementById('salesLineChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line', // グラフの種類を「折れ線グラフ」に設定
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: '月間売上高',
                        data: chartData,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        fill: true, // 線の下を塗りつぶす
                        tension: 0.3 // 少し滑らかな線にする
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Y軸の目盛りを「〇〇円」の形式にする
                                callback: function(value, index, values) {
                                    return value.toLocaleString() + '円';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            // ツールチップ（マウスオーバー時の表示）も「〇〇円」にする
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString() + '円';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
