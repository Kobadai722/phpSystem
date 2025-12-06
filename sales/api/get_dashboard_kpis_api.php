<?php
require_once '../../config.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $currentDate = date('Y-m-d H:i:s');
    $currentMonthStart = date('Y-m-01 00:00:00');
    $currentDay = date('d');

    // 先月の期間（先月1日 〜 先月の同日）
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
    $lastMonthSameDay = date('Y-m-d 23:59:59', strtotime("first day of last month +{$currentDay} day -1 day"));

    $past30Days = date('Y-m-d 00:00:00', strtotime('-30 days'));

    // 今月売上
    $stmtCurrentSales = $PDO->prepare("
        SELECT COALESCE(SUM(PRICE),0) FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtCurrentSales->execute([':start' => $currentMonthStart, ':end' => $currentDate]);
    $currentSales = (int)$stmtCurrentSales->fetch(PDO::FETCH_COLUMN);

    // 先月同期間売上
    $stmtLastSales = $PDO->prepare("
        SELECT COALESCE(SUM(PRICE),0) FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtLastSales->execute([':start' => $lastMonthStart, ':end' => $lastMonthSameDay]);
    $lastSales = (int)$stmtLastSales->fetch(PDO::FETCH_COLUMN);

    // 前月比（安全処理）
    if ($lastSales === 0) {
        // 比較不能（先月が0）→ null を返しフロントで「—」表示にする
        $lastMonthRatio = null;
    } else {
        $lastMonthRatio = round((($currentSales - $lastSales) / $lastSales) * 100, 1);
    }

    // AOV（過去30日、ORDER表で計算）
    $stmtAOV = $PDO->prepare("
        SELECT 
            CASE WHEN COUNT(ORDER_ID)=0 THEN 0 ELSE SUM(PRICE)/COUNT(ORDER_ID) END
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE BETWEEN :start AND :end
    ");
    $stmtAOV->execute([':start' => $past30Days, ':end' => $currentDate]);
    $aov = round($stmtAOV->fetch(PDO::FETCH_COLUMN) ?? 0);

    // トップ商品（今月）
    $stmtTopProducts = $PDO->prepare("
        SELECT P.PRODUCT_NAME AS name, SUM(O.PRICE) AS sales
        FROM `ORDER` O
        JOIN PRODUCT P ON O.PRODUCT_ID = P.PRODUCT_ID
        WHERE O.PURCHASE_ORDER_DATE BETWEEN :start AND :end
        GROUP BY P.PRODUCT_ID, P.PRODUCT_NAME
        ORDER BY sales DESC
        LIMIT 3
    ");
    $stmtTopProducts->execute([':start' => $currentMonthStart, ':end' => $currentDate]);
    $topProducts = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);

    // 在庫アラート（既存ロジック）
    $sqlAlerts = "
        SELECT
            p.PRODUCT_ID AS product_id,
            p.PRODUCT_NAME AS product_name,
            s.STOCK_QUANTITY AS current_stock,
            (
                SELECT COALESCE(SUM(o2.QUANTITY),0)/6
                FROM `ORDER` o2
                WHERE o2.PRODUCT_ID = p.PRODUCT_ID
                AND o2.PURCHASE_ORDER_DATE >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ) AS monthly_avg_sales
        FROM PRODUCT p
        JOIN STOCK s ON p.PRODUCT_ID = s.PRODUCT_ID
        HAVING current_stock < monthly_avg_sales AND monthly_avg_sales > 0
        ORDER BY (monthly_avg_sales - current_stock) DESC
        LIMIT 10
    ";
    $stmtAlerts = $PDO->prepare($sqlAlerts);
    $stmtAlerts->execute();
    $alerts_raw = $stmtAlerts->fetchAll(PDO::FETCH_ASSOC);
    $stockAlerts = [];
    foreach ($alerts_raw as $r) {
        $forecast = round($r['monthly_avg_sales']);
        $stockAlerts[] = [
            'product_id' => (int)$r['product_id'],
            'product_name' => $r['product_name'],
            'current_stock' => (int)$r['current_stock'],
            'forecast' => $forecast,
            'shortage' => $forecast - (int)$r['current_stock'],
            'reason' => '平均販売数超過予測'
        ];
    }

    // 目標
    $salesTarget = 20000000;
    $targetRatio = $salesTarget > 0 ? round(($currentSales / $salesTarget) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'kpis' => [
            'current_month_sales' => $currentSales,
            'last_month_sales' => $lastSales,        // デバッグ/確認用
            'last_month_ratio' => $lastMonthRatio,   // null あるいは数値
            'sales_target' => $salesTarget,
            'target_ratio' => $targetRatio,
            'aov' => $aov,
            'next_month_forecast' => 18500000,
            'forecast_confidence' => '88%'
        ],
        'top_products' => $topProducts,
        'stock_alerts' => $stockAlerts
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
