<?php
// ----- ページ設定と部品の読み込み -----
$page_title = "売上高一覧";
$current_page = "graph";

require_once __DIR__ . '/../a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';



// ----- データの取得と絞り込み処理 -----

$sales_sum = 0;

// フォームから送信された年・月を取得
$selected_year = $_GET['year'] ?? null;
$selected_month = $_GET['month'] ?? null;


// === ▼▼▼ SQLクエリの組み立てロジックを修正 ▼▼▼ ===

// SQLクエリの組み立て準備
$sql_base = "SELECT SALE_DATE, AMOUNT FROM SALES_ENTRIES";
$where_conditions = []; // WHERE句の条件を格納する配列
$params = [];           // パラメータを格納する配列

// 年が選択されていたら、年の条件を追加
if (!empty($selected_year)) {
    $where_conditions[] = "YEAR(SALE_DATE) = ?";
    $params[] = $selected_year;
}

// 月が選択されていたら、月の条件を追加
if (!empty($selected_month)) {
    $where_conditions[] = "MONTH(SALE_DATE) = ?";
    $params[] = $selected_month;
}

// 組み立てた条件を元に、最終的なSQLクエリを生成
$sql_query = $sql_base;
if (!empty($where_conditions)) {
    // 条件が1つ以上あれば、"WHERE" と "AND" で連結する
    $sql_query .= " WHERE " . implode(' AND ', $where_conditions);
}
$sql_query .= " ORDER BY SALE_DATE DESC";

// === ▲▲▲ ここまで修正 ▲▲▲ ===


// データベースからデータを取得
try {
    $stmt = $PDO->prepare($sql_query);
    $stmt->execute($params);
    $sales_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

?>
<div class="page-container">
    <?php require_once __DIR__ . "/../includes/sidebar_bootstrap.php"; ?>
    <main class="main-content">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>

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
            </table>
            <h2>
                <!-- 総額表示 -->
                合計 <?php echo number_format($sales_sum); ?> 円
            </h2>
        </div>
    </main>
</div>

<?php
// require_once __DIR__ . '/../a_footer.php';
?>
