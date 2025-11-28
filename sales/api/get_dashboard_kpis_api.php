<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php';

header('Content-Type: application/json');

try {
    // 現在の日付情報
    $currentDate = date('Y-m-d H:i:s');
    $currentMonthStart = date('Y-m-01 00:00:00');
    
    // 前月同期間の計算用
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
    // 修正点：先月全体ではなく、「先月同日」までの期間を設定
    $lastMonthSameDay = date('Y-m-d H:i:s', strtotime('-1 month'));
    
    $past30Days = date('Y-m-d 00:00:00', strtotime('-30 days'));
    
    // 1. 今月売上合計 (ORDERテーブルのPRICEを使用)
    $stmtCurrentSales = $PDO->prepare("
        SELECT SUM(PRICE) AS current_sales
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date
    ");
    $stmtCurrentSales->bindParam(':start_date', $currentMonthStart);
    $stmtCurrentSales->bindParam(':end_date', $currentDate);
    $stmtCurrentSales->execute();
    $currentSales = $stmtCurrentSales->fetch(PDO::FETCH_COLUMN) ?? 0;

    // 2. 先月売上合計 (前月同期間比較)
    $stmtLastSales = $PDO->prepare("
        SELECT SUM(PRICE) AS last_sales
        FROM `ORDER`
        WHERE PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date
    ");
    $stmtLastSales->bindParam(':start_date', $lastMonthStart);
    // 修正点：比較期間を先月同日までに変更
    $stmtLastSales->bindParam(':end_date', $lastMonthSameDay);
    $stmtLastSales->execute();
    $lastSales = $stmtLastSales->fetch(PDO::FETCH_COLUMN) ?? 0;

    // 3. 前月比成長率の計算
    $lastMonthRatio = 0;
    if ($lastSales > 0) {
        $lastMonthRatio = (($currentSales - $lastSales) / $lastSales) * 100;
    }
    
    // 4. 平均顧客単価 (AOV) (直近30日間)
    $stmtAOV = $PDO->prepare("
        SELECT SUM(TOTAL_AMOUNT) / COUNT(ORDER_ID) AS aov
        FROM S_ORDER
        WHERE ORDER_DATETIME >= :start_date AND ORDER_DATETIME <= :end_date
    ");
    $stmtAOV->bindParam(':start_date', $past30Days);
    $stmtAOV->bindParam(':end_date', $currentDate);
    $stmtAOV->execute();
    $aov = round($stmtAOV->fetch(PDO::FETCH_COLUMN) ?? 0);

    // 5. 商品別貢献度ランキング (今月)
    $stmtTopProducts = $PDO->prepare("
        SELECT 
            P.PRODUCT_NAME AS name,
            SUM(O.PRICE) AS sales
        FROM `ORDER` O
        JOIN PRODUCT P ON O.ORDER_TARGET_ID = P.PRODUCT_ID
        WHERE O.PURCHASE_ORDER_DATE >= :start_date AND O.PURCHASE_ORDER_DATE <= :end_date
        GROUP BY P.PRODUCT_NAME
        ORDER BY sales DESC
        LIMIT 3
    ");
    $stmtTopProducts->bindParam(':start_date', $currentMonthStart);
    $stmtTopProducts->bindParam(':end_date', $currentDate);
    $stmtTopProducts->execute();
    $topProducts = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);

    // 6. 仮のデータ（目標、在庫アラート）
    $salesTarget = 20000000;
    $targetRatio = ($currentSales > 0) ? round(($currentSales / $salesTarget) * 100, 2) : 0;
    
    $stockAlerts = [
        ['product_name' => '商品A (予測不足)', 'reason' => '予測販売数超過', 'current_stock' => 300, 'forecast' => 500],
        ['product_name' => '商品B (過剰在庫)', 'reason' => '在庫滞留リスク', 'current_stock' => 1200, 'forecast' => 50]
    ];
    
    // 7. 結果をJSONで返す
    echo json_encode([
        'success' => true,
        'kpis' => [
            'current_month_sales' => (int)$currentSales,
            'sales_target' => $salesTarget,
            'target_ratio' => $targetRatio,
            'last_month_ratio' => round($lastMonthRatio, 1),
            'aov' => (int)$aov,
            'next_month_forecast' => 18500000,
            'forecast_confidence' => '88%',
        ],
        'top_products' => $topProducts,
        'stock_alerts' => $stockAlerts
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
