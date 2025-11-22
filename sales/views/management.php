<?php
// DB接続とデータ取得、エラー処理
// 実際の環境に合わせてパスを修正してください。
require_once '../../config.php';

// ***************************************************************
// TODO: ここに実際のデータを取得するPHPロジックを記述してください
// ***************************************************************

// === 仮のデータ（表示確認用） ==================================
$current_month_sales = 15000000;
$sales_target = 20000000;
$last_month_ratio = 12.5; // 前月比
$aov = 8500; // 平均顧客単価
$target_ratio = ($current_month_sales / $sales_target) * 100;

$stock_alerts = [
    ['product_name' => '商品A (予測不足)', 'reason' => '予測販売数超過', 'current_stock' => 300, 'forecast' => 500],
    ['product_name' => '商品B (過剰在庫)', 'reason' => '在庫滞留リスク', 'current_stock' => 1200, 'forecast' => 50]
];
$top_products = [
    ['name' => '商品X', 'sales' => 5200000],
    ['name' => '商品Y', 'sales' => 3100000],
    ['name' => '商品Z', 'sales' => 2800000],
];
// ===============================================================
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上管理ダッシュボード</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php';?>

        <section class="content">
            <div class="container-fluid">
                <h1 class="mb-4"><i class="bi bi-graph-up"></i> 売上管理ダッシュボード</h1>

                <div class="d-flex mb-5 gap-2 flex-wrap">


                    <a href="order_create.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> 新規注文作成
                    </a>
                    <a href="stock_management.php" class="btn btn-outline-secondary">
                        <i class="bi bi-box"></i> 商品・在庫管理
                    </a>
                    <a href="customer.php" class="btn btn-outline-secondary">
                        <i class="bi bi-person-lines-fill"></i> 顧客・取引先管理
                    </a>
                    <a href="report_analysis.php" class="btn btn-outline-secondary">
                        <i class="bi bi-bar-chart"></i> 詳細レポート・分析
                    </a>
                    <a href="order_list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-list-columns-reverse"></i> 統合注文一覧
                    </a>
                </div>
                
                <hr>

                <div class="row mb-5">
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary"><i class="bi bi-calendar-check"></i> 今月売上 (目標達成率)</h6>
                                <p class="card-text fs-4 fw-bold">¥<?php echo number_format($current_month_sales); ?></p>
                                <div class="progress mb-2" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $target_ratio; ?>%;" aria-valuenow="<?php echo $target_ratio; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">目標: ¥<?php echo number_format($sales_target); ?> (<?php echo round($target_ratio); ?>%)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-up-right-square"></i> 前月比成長率</h6>
                                <p class="card-text fs-4 fw-bold <?php echo $last_month_ratio >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $last_month_ratio > 0 ? '+' : ''; ?><?php echo htmlspecialchars($last_month_ratio); ?>%
                                </p>
                                <small class="text-muted">前月同期間との比較</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-person-up"></i> 平均顧客単価 (AOV)</h6>
                                <p class="card-text fs-4 fw-bold">¥<?php echo number_format($aov); ?></p>
                                <small class="text-muted">直近の平均購入額</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cash-stack"></i> 翌月売上予測</h6>
                                <p class="card-text fs-4 fw-bold text-info">¥<?php echo number_format(18500000); // TODO: 予測DBから取得 ?></p>
                                <small class="text-muted">予測信頼度: 88%</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">総合売上・利益推移 (チャネル別)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" style="max-height: 300px;"></canvas>
                                <p class="text-center text-muted mt-3">※ 法人(B2B)と個人(B2C)の売上を合算</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">商品別貢献度ランキング</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php 
                                    $rank = 1;
                                    foreach ($top_products as $product): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-2"><?php echo $rank++; ?></span>
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </div>
                                        <span class="fw-bold">¥<?php echo number_format($product['sales']); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> 予測在庫アラート (要対応: <?php echo count($stock_alerts); ?>件)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($stock_alerts)): ?>
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>商品名</th>
                                                <th>アラート内容</th>
                                                <th>現在の在庫</th>
                                                <th>予測販売数 (来月)</th>
                                                <th>対応</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stock_alerts as $alert): 
                                                $alert_class = ($alert['reason'] === '予測販売数超過') ? 'table-warning' : 'table-info';
                                                $action_text = ($alert['reason'] === '予測販売数超過') ? '緊急発注検討' : '在庫状況確認';
                                            ?>
                                            <tr class="<?php echo $alert_class; ?>">
                                                <td><?php echo htmlspecialchars($alert['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($alert['reason']); ?></td>
                                                <td><?php echo number_format($alert['current_stock']); ?></td>
                                                <td><?php echo number_format($alert['forecast']); ?></td>
                                                <td><a href="#" class="btn btn-sm btn-outline-secondary"><?php echo $action_text; ?></a></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-success mb-0">現在、予測に基づく在庫アラートはありません。</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // グラフ描画ロジック (Chart.jsを使用)
        const ctx = document.getElementById('salesChart');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
                datasets: [{
                    label: '法人 (B2B) 売上',
                    data: [30, 40, 35, 50, 45, 60, 55, 65, 70, 75, 80, 85], // 単位は万など
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false,
                    tension: 0.1
                }, {
                    label: '個人 (B2C) 売上',
                    data: [15, 20, 18, 25, 22, 30, 28, 32, 35, 38, 40, 42],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>