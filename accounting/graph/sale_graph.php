<?php
// ----- ページ設定 -----
$page_title = '売上推移グラフ';
$current_page = 'graph'; // サイドバーで「グラフ」がアクティブになるように

// ----- 必要な部品を読み込み -----
// パスは実際のファイル配置に合わせて調整してください
require_once __DIR__ . '/../includes/a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';


// =================================================================
// グラフ用データ取得・計算処理
// =================================================================
try {
    // --- 1. ユーザーが選択した年を取得 ---
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
    $monthly_sales_data = array_fill(1, 12, 0);

    foreach ($results as $row) {
        $monthly_sales_data[(int)$row['sales_month']] = (int)$row['monthly_total'];
    }

    $chart_labels = [];
    for ($m = 1; $m <= 12; $m++) {
        $chart_labels[] = $m . '月';
    }

    $chart_data = array_values($monthly_sales_data);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="../css/a_main_beta.css">
<link rel="stylesheet" href="../css/sidebar_bootstrap.css">

<body>
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php require_once __DIR__ . '/../includes/sidebar_bootstrap.php'; ?>

    <div class="page-container">
        
        <main class="main-content" style="padding-left: 80px; padding-top: 20px;">
            
            <header class="dashboard-header mb-4">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
            </header>

            <div class="card mb-4 shadow-sm">
                <div class="card-body bg-light">
                    <form action="" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="year" class="form-label fw-bold">表示年</label>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> 表示
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($selected_year); ?>年 月別売上推移</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 400px; width: 100%;">
                        <canvas id="salesLineChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartLabels = <?php echo json_encode($chart_labels); ?>;
            const chartData = <?php echo json_encode($chart_data); ?>;

            const ctx = document.getElementById('salesLineChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: '月間売上高',
                        data: chartData,
                        backgroundColor: 'rgba(13, 110, 253, 0.1)', // Bootstrap Primary
                        borderColor: '#0d6efd',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + '円';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
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