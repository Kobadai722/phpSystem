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
<body>
    <?php
    // パスは実際のファイル配置に合わせて調整してください
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../header.php';

    // =================================================================
    // ダッシュボード用データ取得・計算処理
    // =================================================================
    try {
        // --- 1. ユーザーが選択した値を取得 ---
        $selected_year = $_GET['year'] ?? null;
        $selected_month = $_GET['month'] ?? null;
        $target_goal = $_GET['target_goal'] ?? 150; // デフォルト目標を150万円に設定

        // --- 2. 表示する年月を決定 ---
        if (!empty($selected_year) && !empty($selected_month)) {
            // ユーザーが年月を選択した場合
            $display_year = (int)$selected_year;
            $display_month = (int)$selected_month;
        } else {
            // デフォルト：DBに登録されている最新の売上月を取得
            $sql_latest_month = "SELECT MAX(h.ENTRY_DATE) as latest_date 
                                FROM JOURNAL_ENTRIES e
                                JOIN JOURNAL_HEADERS h ON e.HEADER_ID = h.ID
                                WHERE e.ACCOUNT_ID = 8"; // ACCOUNT_ID=8 が「売上高」と仮定
            $latest_date_str = $PDO->query($sql_latest_month)->fetchColumn();

            if ($latest_date_str) {
                $latest_date = new DateTime($latest_date_str);
                $display_year = (int)$latest_date->format('Y');
                $display_month = (int)$latest_date->format('n');
            } else {
                // 売上データが1件もない場合は現在の年月を使用
                $display_year = date('Y');
                $display_month = date('n');
            }
        }

        // --- 3. 選択された年月の売上を取得 ---
        $sql_sales = "SELECT SUM(e.AMOUNT) 
                    FROM JOURNAL_ENTRIES e
                    JOIN JOURNAL_HEADERS h ON e.HEADER_ID = h.ID
                    WHERE e.ACCOUNT_ID = 8 AND YEAR(h.ENTRY_DATE) = ? AND MONTH(h.ENTRY_DATE) = ?";
        $stmt = $PDO->prepare($sql_sales);
        $stmt->execute([$display_year, $display_month]);
        $sales_for_month = $stmt->fetchColumn() ?: 0;

        // --- 4. 各種数値を計算 ---
        // 達成率
        $achievement_rate = ($target_goal > 0) ? ($sales_for_month / ($target_goal * 10000)) * 100 : 0;
        // 目標までの残額
        $remaining_amount = $target_goal * 10000 - $sales_for_month;
    } catch (PDOException $e) {
        die("データベースエラー: " . $e->getMessage());
    }
    ?>
    
    <!-- ハンバーガーメニュー (Offcanvasを表示させるためのボタン) -->
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php
    require_once __DIR__ . '/../includes/sidebar_bootstrap.php';
    ?>

    <!-- メインコンテンツ -->
    <!-- ハンバーガーボタンと重ならないように左に余白を設定 -->
    <main class="main-content" style="padding-left: 80px;">
        <header class="dashboard-header">
            <h1>会計システム--Prototype--</h1>
        </header>

        <div class="dashboard-grid">
            <!-- 売上カード -->
            <section class="card">
                <div class="card-header">
                    <h3>月間売上目標</h3>
                </div>
                <div class="card-body">
                    <!-- 期間と目標金額の選択フォーム -->
                    <form action="a_main_beta.php" method="GET" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-sm">
                                <label for="target_goal" class="form-label small">目標金額（万円）</label>
                                <select name="target_goal" id="target_goal" class="form-select">
                                    <?php for ($goal = 200; $goal >= 50; $goal -= 10): ?>
                                        <option value="<?php echo $goal; ?>" <?php if ($goal == $target_goal) echo 'selected'; ?>>
                                            <?php echo number_format($goal); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="year" class="form-label small">年</label>
                                <select name="year" id="year" class="form-select">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php if ($y == $display_year) echo 'selected'; ?>><?php echo $y; ?>年</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="month" class="form-label small">月</label>
                                <select name="month" id="month" class="form-select">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php if ($m == $display_month) echo 'selected'; ?>><?php echo $m; ?>月</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-sm-auto">
                                <button type="submit" class="btn btn-primary">表示</button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <!-- 結果表示エリア -->
                    <div class="text-center mb-3">
                        <h4 class="mb-0"><?php echo $display_year; ?>年<?php echo $display_month; ?>月 売上実績</h4>
                        <p class="metric-value mb-1"><?php echo number_format($sales_for_month); ?><small>円</small></p>
                    </div>

                    <div class="gauge-chart" style="--percentage: <?php echo $achievement_rate; ?>;"></div>

                    <div class="d-flex justify-content-around mt-3">
                        <div>
                            <div class="small text-muted">達成率</div>
                            <div class="fw-bold fs-5 <?php echo ($achievement_rate >= 100) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo number_format($achievement_rate, 1); ?>%
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted">目標まで残り</div>
                            <div class="fw-bold fs-5">
                                <?php echo number_format(max(0, $remaining_amount)); ?>円
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ▼▼▼ 以下、デモカードの内容を追記 ▼▼▼ -->
            <section class="card">
                <div class="card-header">
                    <h3>総資産</h3>
                </div>
                <div class="card-body">
                    <p class="metric-value">12,345,678<small>円</small></p>
                    <div class="comparison">
                        <span class="up">▲ 前月比 2.5%</span>
                    </div>
                </div>
            </section>
            <section class="card">
                <div class="card-header">
                    <h3>総負債</h3>
                </div>
                <div class="card-body">
                    <p class="metric-value">5,432,109<small>円</small></p>
                    <div class="comparison">
                        <span class="down">▼ 前月比 1.2%</span>
                    </div>
                </div>
            </section>
            <section class="card">
                <div class="card-header">
                    <h3>純利益</h3>
                </div>
                <div class="card-body">
                    <p class="metric-value">8,765,432<small>円</small></p>
                    <div class="comparison">
                        <span class="up">▲ 前月比 5.8%</span>
                    </div>
                </div>
            </section>
            <section class="card">
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
            <section class="card">
                <div class="card-header">
                    <h3>レポート</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">月次レポートをダウンロード</a>
                        <a href="#" class="btn btn-outline-secondary">年間レポートをダウンロード</a>
                    </div>
                </div>
            </section>
            <!-- ▲▲▲ ここまでデモカード ▲▲▲ -->

        </div>
    </main>

    <!-- === ▼ BootstrapのJavaScriptを読み込むように変更 (Offcanvasの動作に必須) ▼ === -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
