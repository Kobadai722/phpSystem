<?php
// DB接続
require_once '../../config.php'; 

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    //    日付計算
    

    $currentDate = date('Y-m-d H:i:s');
    $currentMonthStart = date('Y-m-01 00:00:00');

    // 今日の日
    $currentDay = date('d');

    // 先月1日
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));

    // 先月の「今日と同日」(例：12/10 → 11/10)
    $lastMonthSameDay = date('Y-m-d 23:59:59', strtotime("first day of last month +{$currentDay} day -1 day"));

    // 過去30日のAOV計算用
    $past30Days = date('Y-m-d 00:00:00', strtotime('-30 days'));

    //    1. 今月売上
    

    $stmtCurrentSales = $PDO->prepare("
        SELECT COALESCE(SUM(PRICE), 0)
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtCurrentSales->execute([
        ':start' => $currentMonthStart,
        ':end'   => $currentDate
    ]);
    $currentSales = (int)$stmtCurrentSales->fetch(PDO::FETCH_COLUMN);

    //    2. 先月同期間売上
    

    $stmtLastSales = $PDO->prepare("
        SELECT COALESCE(SUM(PRICE), 0)
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtLastSales->execute([
        ':start' => $lastMonthStart,
        ':end'   => $lastMonthSameDay
    ]);
    $lastSales = (int)$stmtLastSales->fetch(PDO::FETCH_COLUMN);

    //    3. 前月比成長率

    $lastMonthRatio = ($lastSales > 0)
        ? round((($currentSales - $lastSales) / $lastSales) * 100, 1)
        : 0;

    //    4. AOV（平均購入額：ORDER表使用）

    $stmtAOV = $PDO->prepare("
        SELECT 
            SUM(PRICE) / COUNT(ORDER_ID)
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtAOV->execute([
        ':start' => $past30Days,
        ':end'   => $currentDate
    ]);
    $aov = round($stmtAOV->fetch(PDO::FETCH_COLUMN) ?? 0);

    //  5. トップ商品（今月）

    $stmtTopProducts = $PDO->prepare("
        SELECT 
            P.PRODUCT_NAME AS name,
            SUM(O.PRICE) AS sales
        FROM `ORDER` O
        JOIN PRODUCT P ON O.PRODUCT_ID = P.PRODUCT_ID
        WHERE O.PURCHASE_ORDER_DATE BETWEEN :start AND :end
        GROUP BY P.PRODUCT_NAME
        ORDER BY sales DESC
        LIMIT 3
    ");
    $stmtTopProducts->execute([
        ':start' => $currentMonthStart,
        ':end'   => $currentDate
    ]);
    $topProducts = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);

    //6. 目標達成率

    $salesTarget = 20000000;
    $targetRatio = ($salesTarget > 0) ? round(($currentSales / $salesTarget) * 100, 1) : 0;

    //7. 在庫アラート（平均販売数 < 在庫）


    $sqlAlerts = "
        SELECT
            p.PRODUCT_NAME AS product_name,
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
        ORDER BY
            (monthly_avg_sales - current_stock) DESC
        LIMIT 10
    ";

    $stmtAlerts = $PDO->prepare($sqlAlerts);
    $stmtAlerts->execute();
    $alertRows = $stmtAlerts->fetchAll(PDO::FETCH_ASSOC);

    $stockAlerts = [];
    foreach ($alertRows as $row) {
        $forecast = round($row['monthly_avg_sales']);
        $stockAlerts[] = [
            'product_name'  => $row['product_name'],
            'current_stock' => (int)$row['current_stock'],
            'forecast'      => $forecast,
            'shortage'      => $forecast - (int)$row['current_stock'],
            'reason'        => '平均販売数超過予測'
        ];
    }
    //JSON返却

    echo json_encode([
        'success' => true,
        'kpis' => [
            'current_month_sales' => $currentSales,
            'sales_target'        => $salesTarget,
            'target_ratio'        => $targetRatio,
            'last_month_ratio'    => $lastMonthRatio,
            'aov'                 => $aov,
            'next_month_forecast' => 18500000,
            'forecast_confidence' => '88%',
        ],
        'top_products' => $topProducts,
        'stock_alerts' => $stockAlerts
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'エラー: ' . $e->getMessage()
    ]);
}
