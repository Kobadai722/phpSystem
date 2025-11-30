<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php';

// PHP側ではデータ取得を行わず、全てJavaScriptでAPIから取得するように修正
// このブロックはHTML表示前のPHPエラー防止用として残し、値は初期化
$stock_alerts_count = 0; 
$current_month_sales = 0;
$sales_target = 0;
$last_month_ratio = 0;
$aov = 0;
$target_ratio = 0;

$stock_alerts = [];
$top_products = [];
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
                    <a href="../../customer/customer.php" class="btn btn-outline-secondary">
                        <i class="bi bi-person-lines-fill"></i> 顧客・取引先管理
                    </a>
                    <a href="report_analysis.php" class="btn btn-outline-secondary">
                        <i class="bi bi-bar-chart"></i> 詳細レポート・分析
                    </a>
                    <a href="order_list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-list-columns-reverse"></i> 統合注文一覧
                    </a>
                    <a href="sale_add.php" class="btn btn-outline-secondary">
                        <i class="bi bi-cash-stack"></i> 売上データ登録用フォーム
                    </a>
                </div>
                
                <hr>
                
                <div class="row mb-5" id="kpi-cards-section">
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary"><i class="bi bi-calendar-check"></i> 今月売上 (目標達成率)</h6>
                                <p class="card-text fs-4 fw-bold" id="current_month_sales">¥---</p>
                                <div class="progress mb-2" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" id="target_progress_bar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">目標: <span id="sales_target">¥---</span> (<span id="target_ratio">0</span>%)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-up-right-square"></i> 前月比成長率</h6>
                                <p class="card-text fs-4 fw-bold" id="last_month_ratio">---</p>
                                <small class="text-muted">前月同期間との比較</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-person-up"></i> 平均顧客単価 (AOV)</h6>
                                <p class="card-text fs-4 fw-bold" id="aov">¥---</p>
                                <small class="text-muted">直近の平均購入額</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cash-stack"></i> 翌月売上予測</h6>
                                <p class="card-text fs-4 fw-bold" id="next_month_forecast">¥---</p>
                                <small class="text-muted">予測信頼度: <span id="forecast_confidence">---</span></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">総合売上推移 (過去12ヶ月)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" style="max-height: 300px;"></canvas>
                                <p class="text-center text-muted mt-3">※ データは`get_sales_trend_api.php`から取得</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">商品別貢献度ランキング (今月)</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush" id="top-products-list">
                                    <li class="list-group-item text-center text-muted">データ取得中...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> 予測在庫アラート (<span id="alert-count">---</span>件)</h5>
                            </div>
                            <div class="card-body">
                                <div id="stock-alerts-area">
                                    <p class="text-center text-muted mb-0">データ取得中...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // グローバル変数
        let salesChart;
        // Python APIのエンドポイントは今回は使用しないため、参照のみ残す
        const PYTHON_PREDICT_API = 'http://localhost:5000/predict_sales';

        document.addEventListener('DOMContentLoaded', function() {
        // ページロード時に全データを取得
            fetchDashboardData();
        });

        // 通貨フォーマットヘルパー
        function formatCurrency(amount) {
            return '¥' + parseInt(amount).toLocaleString();
        }
        
        // --- 予測機能用の関数（今回は使用しないため、中身を空にするか、削除します） ---

        async function fetchPastSalesData() {
             return []; 
        }

        async function fetchAndRunPrediction() {
            // Python予測は無効化し、PHPから返されたダミー値を使用します。
            console.log('Python予測APIの実行は無効化されています。');
        }
        
        // --- メインデータ取得関数 ---

        // 全てのダッシュボードデータをAPIから取得し、表示を更新するメイン関数
        async function fetchDashboardData() {
            // 1. KPI & トップ商品データ取得
            try {
                // ここでAPIを呼び出す
                const kpiResponse = await fetch('../api/get_dashboard_kpis_api.php', { method: 'POST' });
                
                if (!kpiResponse.ok) {
                    throw new Error(`HTTP error! status: ${kpiResponse.status}`);
                }
                const kpiResult = await kpiResponse.json();

                if (kpiResult.success) {
                    updateKpiCards(kpiResult.kpis);
                    updateTopProducts(kpiResult.top_products);
                    
                    // ⭐ 在庫アラートの更新 (実データを使用) ⭐
                    updateStockAlerts(kpiResult.stock_alerts); 
                    
                    // 予測値の更新（PHPから返されたダミー値/計算結果を使用）
                    document.getElementById('next_month_forecast').textContent = formatCurrency(kpiResult.kpis.next_month_forecast);
                    document.getElementById('forecast_confidence').textContent = kpiResult.kpis.forecast_confidence;

                } else {
                    console.error("KPIデータ取得エラー:", kpiResult.message);
                    document.getElementById('current_month_sales').textContent = 'エラー';
                }
            } catch (error) {
                console.error('KPI Fetch error:', error);
                document.getElementById('current_month_sales').textContent = '接続エラー';
            }
            
            // 2. 売上推移グラフデータ取得
            try {
                const trendResponse = await fetch('../api/get_sales_trend_api.php', { method: 'POST' });
                if (!trendResponse.ok) {
                    throw new Error(`HTTP error! status: ${trendResponse.status}`);
                }
                const trendResult = await trendResponse.json(); 

                if (trendResult.success) {
                    updateSalesChart(trendResult.data);
                } else {
                    console.error("売上推移データ取得エラー:", trendResult.message);
                }
            } catch (error) {
                console.error('Trend Fetch error:', error);
            }
        }

        // KPIカードを更新する関数
        function updateKpiCards(kpis) {
            document.getElementById('current_month_sales').textContent = formatCurrency(kpis.current_month_sales);
            document.getElementById('sales_target').textContent = formatCurrency(kpis.sales_target);
            
            // 達成率の表示とプログレスバーのロジックを修正
            const targetRatioValue = kpis.target_ratio; 
            document.getElementById('target_ratio').textContent = targetRatioValue.toFixed(1);

            const progressBar = document.getElementById('target_progress_bar');
            
            let progressWidth = Math.min(targetRatioValue, 100);
            
            if (targetRatioValue > 0 && targetRatioValue < 1) {
                progressWidth = 1; // 0%以上1%未満の場合、視覚的に1%の幅を確保
            }
            
            progressBar.style.width = progressWidth + '%';
            progressBar.setAttribute('aria-valuenow', targetRatioValue);
            progressBar.className = 'progress-bar ' + (targetRatioValue >= 100 ? 'bg-success' : 'bg-primary');

            const ratioElement = document.getElementById('last_month_ratio');
            const ratioValue = kpis.last_month_ratio;
            ratioElement.textContent = (ratioValue > 0 ? '+' : '') + ratioValue.toFixed(1) + '%';
            ratioElement.className = 'card-text fs-4 fw-bold ' + (ratioValue >= 0 ? 'text-success' : 'text-danger');

            document.getElementById('aov').textContent = formatCurrency(kpis.aov);
        }

        function updateTopProducts(products) {
            const list = document.getElementById('top-products-list');
            list.innerHTML = ''; 

            if (products.length === 0) {
                list.innerHTML = '<li class="list-group-item text-center text-muted">今月の売上データがありません。</li>';
                return;
            }

            products.forEach((product, index) => {
                const rank = index + 1;
                const sales = formatCurrency(product.sales);

                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
                    <div>
                        <span class="badge bg-secondary me-2">${rank}</span>
                        ${product.name}
                    </div>
                    <span class="fw-bold">${sales}</span>
                `;
                list.appendChild(listItem);
            });
        }

        // ⭐ 在庫アラートの表示関数（最終版） ⭐
        function updateStockAlerts(alerts) {
            const alertArea = document.getElementById('stock-alerts-area');
            document.getElementById('alert-count').textContent = alerts.length;

            if (alerts.length === 0) {
                alertArea.innerHTML = '<p class="text-success mb-0">現在、予測に基づく在庫アラートはありません。</p>';
                return;
            }

            // テーブル構造を動的に生成
            let tableHtml = `
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>商品名</th>
                            <th>現在の在庫</th>
                            <th>平均販売数 (来月予測)</th>
                            <th>不足数</th>
                            <th>対応</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            alerts.forEach(alert => {
                // 不足しているため、警告色を適用
                const actionText = '発注検討';
                
                tableHtml += `
                    <tr class="table-warning">
                        <td>${alert.product_name}</td>
                        <td>${parseInt(alert.current_stock).toLocaleString()}</td>
                        <td>${parseInt(alert.forecast).toLocaleString()}</td>
                        <td class="text-danger fw-bold">${parseInt(alert.shortage).toLocaleString()}</td>
                        <td><a href="stock_management.php?product=${encodeURIComponent(alert.product_name)}" class="btn btn-sm btn-outline-danger">${actionText}</a></td>
                    </tr>
                `;
            });
            
            tableHtml += `
                    </tbody>
                </table>`;
            
            alertArea.innerHTML = tableHtml;
        }

        function updateSalesChart(data) {
            const labels = data.map(item => item.period);
            const sales = data.map(item => parseFloat(item.total_sales));

            // Chart.js データ構造
            const chartData = {
                labels: labels,
                datasets: [{
                    label: '総合売上高 (円)',
                    data: sales,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2,
                    fill: 'origin', // 領域を塗りつぶす
                    tension: 0.2 
                }]
            };

            const ctx = document.getElementById('salesChart').getContext('2d');
            
            // 既存のチャートがあれば破棄
            if (salesChart) {
                salesChart.destroy();
            }

            // 新しいチャートを作成
            salesChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString() + ' 円';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '期間'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '合計売上高 (円)'
                            },
                            ticks: {
                                // Y軸の表示を整形
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toLocaleString() + 'M';
                                    if (value >= 1000) return (value / 1000).toLocaleString() + 'K';
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>