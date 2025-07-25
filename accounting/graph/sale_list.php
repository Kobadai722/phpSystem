<?php
$page_title = "売上高一覧";
$current_page = "graph";
require_once __DIR__ . '/../a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';
// ----- データの取得と絞り込み処理 -----

$sales_sum = 0; 

// 1. フォームから送信された年・月を取得する
// GETパラメータがなければ、nullを設定
$selected_year = $_GET['year'] ?? null;
$selected_month = $_GET['month'] ?? null;

// 2. SQLクエリを準備する
// 基本のSQL文
$sql_base = "SELECT SALE_DATE, AMOUNT FROM SALES_ENTRIES";
$params = []; // SQLに渡すパラメータを格納する配列

// もし年と月が両方選択された場合のみ
if (!empty($selected_year) && !empty($selected_month)) {
    $sql_where = " WHERE YEAR(SALE_DATE) = ? AND MONTH(SALE_DATE) = ?";
    $sql_query = $sql_base . $sql_where . " ORDER BY SALE_DATE DESC";
    $params = [$selected_year, $selected_month];
} else {
    // 絞り込みがない場合は、全件取得
    $sql_query = $sql_base . " ORDER BY SALE_DATE DESC";
}

// 3. データベースからデータを取得
try {
    $stmt = $PDO->prepare($sql_query);
    $stmt->execute($params);
    $sales_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

?>
<!-- ページ部 -->
<div class="page-container">
    <?php require_once __DIR__ . "/../sidebar.php"; ?>
    <main class="main-content">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>

        <!-- 表示する期間選択フォーム -->
        <form action="" method="GET" class="border rounded p-3 my-4 bg-light">
            <div class="row align-items-end">
                <!-- 年の選択 -->
                <div class="col-md-4">
                    <label for="year" class="form-label">年を選択</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">-- 全て --</option>
                        <?php
                        // 今年から過去5年分をプルダウンに表示
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            // 送信された年と同じなら、selected属性を付ける
                            $selected_attr = ($y == $selected_year) ? 'selected' : '';
                            echo "<option value='{$y}' {$selected_attr}>{$y}年</option>";
                        }
                        ?>
                    </select>
                </div>
                <!-- 月の選択 -->
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
                <!-- 集計ボタン -->
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
                        <th>取引日 (SALE_DATE)</th>
                        <th class="text-end">売上高</th>
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

?>
