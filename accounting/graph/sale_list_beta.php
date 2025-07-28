<?php
// ----- ページ設定 -----
$page_title = "売上高一覧";
$current_page = "graph"; // サイドバーのハイライト用
require_once __DIR__ . '/../a_header.php';

// ----- 部品の読み込み -----
// パスは実際のファイル配置に合わせて調整してください
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';

// ----- データ処理 -----
$sales_sum = 0;
$selected_year = $_GET['year'] ?? null;
$selected_month = $_GET['month'] ?? null;

$sql_base = "SELECT SALE_DATE, AMOUNT FROM SALES_ENTRIES";
$where_conditions = [];
$params = [];

if (!empty($selected_year)) {
    $where_conditions[] = "YEAR(SALE_DATE) = ?";
    $params[] = $selected_year;
}
if (!empty($selected_month)) {
    $where_conditions[] = "MONTH(SALE_DATE) = ?";
    $params[] = $selected_month;
}

$sql_query = $sql_base;
if (!empty($where_conditions)) {
    $sql_query .= " WHERE " . implode(' AND ', $where_conditions);
}
$sql_query .= " ORDER BY SALE_DATE DESC";

try {
    $stmt = $PDO->prepare($sql_query);
    $stmt->execute($params);
    $sales_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// a_header.phpをここで読み込む
require_once __DIR__ . '/../a_header.php';
?>

<body>

    <!-- ハンバーガーメニュー (Offcanvasを表示させるためのボタン) -->
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php
    // Bootstrap版のサイドバー部品を読み込む
    // このファイルは以前作成したsidebar_bootstrap.phpを指します
    require_once __DIR__ . '/../sidebar_bootstrap.php';
    ?>

    <!-- メインコンテンツ -->
    <!-- サイドバーと重ならないように左に余白を設ける -->
    <main class="container-fluid" style="padding-left: 80px; padding-top: 20px;">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>

        <!-- 表示する期間選択フォーム -->
        <form action="" method="GET" class="border rounded p-3 my-4 bg-light">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="year" class="form-label">年を選択</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">-- 全て --</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected_attr = ($y == $selected_year) ? 'selected' : '';
                            echo "<option value='{$y}' {$selected_attr}>{$y}年</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="month" class="form-label">月を選択</label>
                    <select name="month" id="month" class="form-select">
                        <option value="">-- 全て --</option>
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $selected_attr = ($m == $selected_month) ? 'selected' : '';
                            echo "<option value='{$m}' {$selected_attr}>{$m}月</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">この期間で集計</button>
                </div>
            </div>
        </form>

        <!-- 売上高一覧テーブル -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>売上日</th>
                        <th class="text-end">売上金額</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales_entries)): ?>
                        <tr>
                            <td colspan="2" class="text-center">該当するデータがありません。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales_entries as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['SALE_DATE']); ?></td>
                                <td class="text-end"><?php echo number_format($row['AMOUNT']); ?> 円</td>
                            </tr>
                            <?php $sales_sum += $row['AMOUNT']; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-group-divider">
                        <th class="text-end">合計</th>
                        <th class="text-end"><?php echo number_format($sales_sum); ?> 円</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>

    <?php
    // フッター部品を読み込む
    // require_once __DIR__ . '/../a_footer.php';
    ?>

    <!-- BootstrapのJavaScriptバンドルを読み込む (Offcanvasの動作に必須) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
