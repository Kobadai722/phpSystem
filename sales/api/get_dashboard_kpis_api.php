<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php'; 

// JSONヘッダー
header('Content-Type: application/json');

try {
    // 現在の日付
    $currentDate = date('Y-m-d H:i:s');
    $currentMonthStart = date('Y-m-01 00:00:00');

    // 前月同期間
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
    $lastMonthSameDay = date('Y-m-d H:i:s', strtotime('-1 month'));

    // 過去30日
    $past30Days = date('Y-m-d 00:00:00', strtotime('-30 days'));

    // 1. 今月売上合計
    $stmtCurrentSales = $PDO->prepare("
        SELECT SUM(PRICE) AS current_sales
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date
    ");
    $stmtCurrentSales->execute([
        ':start_date' => $currentMonthStart,
        ':end_date'   => $currentDate
    ]);
    $currentSales = $stmtCurrentSales->fetch(PDO::FETCH_COLUMN) ?? 0;

    // 2. 先月売上合計
    $stmtLastSales = $PDO->prepare("
        SELECT SUM(PRICE) AS last_sales
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date
    ");
    $stmtLastSales->execute([
        ':start_date' => $lastMonthStart,
        ':end_date'   => $lastMonthSameDay
    ]);
    $lastSales = $stmtLastSales->fetch(PDO::FETCH_COLUMN) ?? 0;

    // 3. 前月比成長率
    $lastMonthRatio = ($lastSales > 0)
        ? (($currentSales - $lastSales) / $lastSales) * 100
        : 0;

    // 4. AOV（直近30日）
    $stmtAOV = $PDO->prepare("
        SELECT SUM(TOTAL_AMOUNT) / COUNT(ORDER_ID) AS aov
        FROM S_ORDER
        WHERE ORDER_DATETIME >= :start_date AND ORDER_DATETIME <= :end_date
    ");
    $stmtAOV->execute([
        ':start_date' => $past30Days,
        ':end_date'   => $currentDate
    ]);
    $aov = round($stmtAOV->fetch(PDO::FETCH_COLUMN) ?? 0);

    // 5. 商品別売上ランキング（今月）
    $stmtTopProducts = $PDO->prepare("
        SELECT 
            P.PRODUCT_NAME AS name,
            SUM(O.PRICE) AS sales
        FROM `ORDER` O
        JOIN PRODUCT P ON O.PRODUCT_ID = P.PRODUCT_ID
        WHERE O.PURCHASE_ORDER_DATE >= :start_date AND O.PURCHASE_ORDER_DATE <= :end_date
        GROUP BY P.PRODUCT_ID, P.PRODUCT_NAME
        ORDER BY sales DESC
        LIMIT 3
    ");
    $stmtTopProducts->execute([
        ':start_date' => $currentMonthStart,
        ':end_date'   => $currentDate
    ]);
    $topProducts = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);

    // 6. 在庫アラート（product_id）
    $sql_alerts = "
        SELECT
            p.PRODUCT_ID AS product_id,
            p.NAME AS product_name,
            s.STOCK_QUANTITY AS current_stock,
            (
                SELECT COALESCE(SUM(o2.QUANTITY), 0) / 6
                FROM `ORDER` o2
                WHERE o2.PRODUCT_ID = p.PRODUCT_ID
                AND o2.PURCHASE_ORDER_DATE >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ) AS monthly_avg_sales
        FROM PRODUCT p
        JOIN STOCK s ON p.PRODUCT_ID = s.PRODUCT_ID
        HAVING
            current_stock < monthly_avg_sales
            AND monthly_avg_sales > 0
        ORDER BY (monthly_avg_sales - current_stock) DESC
        LIMIT 10
    ";

    $stmt_alerts = $PDO->query($sql_alerts);
    $alerts_raw = $stmt_alerts->fetchAll(PDO::FETCH_ASSOC);

    $stock_alerts = [];
    foreach ($alerts_raw as $row) {
        $forecast = round($row['monthly_avg_sales']);

        $stock_alerts[] = [
            'product_id'    => (int)$row['product_id'],
            'product_name'  => $row['product_name'],
            'current_stock' => (int)$row['current_stock'],
            'forecast'      => $forecast,
            'shortage'      => $forecast - (int)$row['current_stock'],
            'reason'        => '平均販売数超過予測',
        ];
    }

    // JSON返却
    echo json_encode([
        'success' => true,
        'kpis' => [
            'current_month_sales' => (int)$currentSales,
            'sales_target'        => 20000000,
            'target_ratio'        => ($currentSales / 20000000) * 100,
            'last_month_ratio'    => round($lastMonthRatio, 1),
            'aov'                 => (int)$aov,
            'next_month_forecast' => 18500000,
            'forecast_confidence' => '88%',
        ],
        'top_products' => $topProducts,
        'stock_alerts' => $stock_alerts
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'エラー: ' . $e->getMessage()]);
}
