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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- 独自のCSS -->
    <link rel="stylesheet" href="css/a_main_beta.css">
    <link rel="stylesheet" href="css/sidebar_bootstrap.css">
    <link rel="stylesheet" href="css/siwake.css">
</head>
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/a_main_beta.css">
    <link rel="stylesheet" href="css/sidebar_bootstrap.css">
    <link rel="stylesheet" href="css/siwake.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <style>
        .clickable-card:hover {
            cursor: pointer;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-2px);
            transition: all 0.2s ease-in-out;
        }
        .row-gap-custom {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../header.php';

    // =================================================================
    // データ取得・計算処理
    // =================================================================
    try {
        // --- 1. ユーザー選択値の取得 ---
        $selected_year = $_GET['year'] ?? null;
        $selected_month = $_GET['month'] ?? null;
        $target_goal = $_GET['target_goal'] ?? 150; 

        // --- 2. 表示対象の年月を決定 ---
        // ここで決まった $display_year がグラフにも使われます
        if (!empty($selected_year) && !empty($selected_month)) {
            $display_year = (int)$selected_year;
            $display_month = (int)$selected_month;
        } else {
            // データから最新月を取得、なければ現在年月
            $sql_latest = "SELECT MAX(h.ENTRY_DATE) FROM JOURNAL_ENTRIES e JOIN JOURNAL_HEADERS h ON e.HEADER_ID = h.ID WHERE e.ACCOUNT_ID = 8";
            $latest_date_str = $PDO->query($sql_latest)->fetchColumn();
            if ($latest_date_str) {
                $latest = new DateTime($latest_date_str);
                $display_year = (int)$latest->format('Y');
                $display_month = (int)$latest->format('n');
            } else {
                $display_year = date('Y');
                $display_month = date('n');
            }
        }

        // --- 3. [ゲージ用] 選択月の売上合計 ---
        $sql_sales = "SELECT SUM(e.AMOUNT) 
                    FROM JOURNAL_ENTRIES e
                    JOIN JOURNAL_HEADERS h ON e.HEADER_ID = h.ID
                    WHERE e.ACCOUNT_ID = 8 AND YEAR(h.ENTRY_DATE) = ? AND MONTH(h.ENTRY_DATE) = ?";
        $stmt = $PDO->prepare($sql_sales);
        $stmt->execute([$display_year, $display_month]);
        $sales_for_month = $stmt->fetchColumn() ?: 0;

        // 達成率計算
        $target_amount_yen = $target_goal * 10000;
        $achievement_rate = ($target_amount_yen > 0) ? ($sales_for_month / $target_amount_yen) * 100 : 0;
        $remaining_amount = $target_amount_yen - $sales_for_month;

        // --- 4. [グラフ用] 年間の月別売上推移データを取得 ---
        // sale_graph.php と同じロジックです
        // 選択された年（$display_year）のデータを取得します
        $sql_graph = "SELECT MONTH(h.ENTRY_DATE) as m, SUM(e.AMOUNT) as total
                      FROM JOURNAL_ENTRIES e
                      JOIN JOURNAL_HEADERS h ON e.HEADER_ID = h.ID
                      WHERE e.ACCOUNT_ID = 8 AND YEAR(h.ENTRY_DATE) = ?
                      GROUP BY m ORDER BY m";
        $stmt_graph = $PDO->prepare($sql_graph);
        $stmt_graph->execute([$display_year]);
        
        // 配列を0で初期化 (1月～12月)
        $monthly_data = array_fill(1, 12, 0);
        while ($row = $stmt_graph->fetch(PDO::FETCH_ASSOC)) {
            $monthly_data[(int)$row['m']] = (int)$row['total'];
        }
        // JavaScriptに渡すためにJSON化
        $js_chart_data = json_encode(array_values($monthly_data));

    } catch (PDOException $e) {
        die("データベースエラー: " . $e->getMessage());
    }
    ?>
    
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php require_once __DIR__ . '/includes/sidebar_bootstrap.php'; ?>

    <div class="page-container">
        <main class="main-content" style="padding-left: 80px; padding-top: 20px;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>会計システム--Prototype--</h1>
            </div>

            <div class="container-fluid p-0">
                
                <div class="row row-gap-custom">
                    <div class="col-lg-4 d-flex">
                        <section class="card w-100">
                            <div class="card-header">
                                <h3>月間売上目標</h3>
                            </div>
                            <div class="card-body">
                                <form action="a_main_beta.php" method="GET" class="mb-3">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-sm">
                                            <label class="form-label small">目標(万円)</label>
                                            <select name="target_goal" class="form-select form-select-sm">
                                                <?php for ($g = 200; $g >= 50; $g -= 10): ?>
                                                    <option value="<?= $g ?>" <?= ($g == $target_goal) ? 'selected' : '' ?>><?= $g ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm">
                                            <label class="form-label small">年</label>
                                            <select name="year" class="form-select form-select-sm">
                                                <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                                                    <option value="<?= $y ?>" <?= ($y == $display_year) ? 'selected' : '' ?>><?= $y ?>年</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm">
                                            <label class="form-label small">月</label>
                                            <select name="month" class="form-select form-select-sm">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= ($m == $display_month) ? 'selected' : '' ?>><?= $m ?>月</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-auto">
                                            <button type="submit" class="btn btn-primary btn-sm">表示</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="text-center mb-2">
                                    <h5 class="mb-0"><?= $display_year ?>年<?= $display_month ?>月 売上実績</h5>
                                    <p class="metric-value mb-0"><?= number_format($sales_for_month) ?><small>円</small></p>
                                </div>

                                <div class="gauge-chart" style="--percentage: <?= $achievement_rate ?>;"></div>

                                <div class="d-flex justify-content-around mt-3">
                                    <div class="text-center">
                                        <div class="small text-muted">達成率</div>
                                        <div class="fw-bold fs-5 <?= ($achievement_rate >= 100) ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($achievement_rate, 1) ?>%
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="small text-muted">目標まで残り</div>
                                        <div class="fw-bold fs-5">
                                            <?= number_format(max(0, $remaining_amount)) ?>円
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-8 d-flex">
                        <section class="card w-100 clickable-card" onclick="location.href='graph/sale_graph.php?year=<?= $display_year ?>'">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3>売上推移 (<?= $display_year ?>年)</h3>
                                <div>
                                    <span class="badge bg-primary me-1">Click to Detail</span>
                                    <span class="badge bg-secondary">年次推移</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div style="position: relative; height: 350px; width: 100%;">
                                    <canvas id="salesTrendChart"></canvas>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row row-gap-custom">
                    <div class="col-lg-6 d-flex">
                        <section class="card w-100">
                            <div class="card-header">
                                <h3>総費用</h3>
                            </div>
                            <div class="card-body">
                                <p class="metric-value">2,345,678<small>円</small></p>
                                <div class="comparison">
                                    <span class="down">▼ 前月比 0.5%</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-6 d-flex">
                        <section class="card w-100">
                            <div class="card-header">
                                <h3>レポート</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary">月次レポートをダウンロード</button>
                                    <button class="btn btn-outline-secondary">年間レポートをダウンロード</button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // PHPから受け取ったデータ
            const salesData = <?php echo $js_chart_data; ?>;
            const labels = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];

            const ctx = document.getElementById('salesTrendChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'line', 
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '売上高',
                            data: salesData,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false } 
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + '円';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>