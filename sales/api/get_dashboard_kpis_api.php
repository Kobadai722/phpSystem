<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php'; 

// 実行前にJSONヘッダーを出力
header('Content-Type: application/json');

try {
    // 現在の日付情報
    $currentDate = date('Y-m-d H:i:s');
    $currentMonthStart = date('Y-m-01 00:00:00');
    
    // 前月同期間の計算用
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
    $lastMonthSameDay = date('Y-m-d H:i:s', strtotime('-1 month'));
    
    $past30Days = date('Y-m-d 00:00:00', strtotime('-30 days'));
    
    // 1. 今月売上合計 (ORDERテーブルのPRICEを使用)
    // NOTE: ORDERテーブルのPRICEは注文全体の金額と仮定し、このKPIはそのまま残します。
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
    $stmtLastSales->bindParam(':end_date', $lastMonthSameDay);
    $stmtLastSales->execute();
    $lastSales = $stmtLastSales->fetch(PDO::FETCH_COLUMN) ?? 0;

    // 3. 前月比成長率の計算
    $lastMonthRatio = 0;
    if ($lastSales > 0) {
        $lastMonthRatio = (($currentSales - $lastSales) / $lastSales) * 100;
    }
    
    // 4. 平均顧客単価 (AOV) (直近30日間)
    // NOTE: S_ORDERテーブルを使用しているため、この部分はそのまま残します。
    $stmtAOV = $PDO->prepare("
        SELECT SUM(TOTAL_AMOUNT) / COUNT(ORDER_ID) AS aov
        FROM S_ORDER
        WHERE ORDER_DATETIME >= :start_date AND ORDER_DATETIME <= :end_date
    ");
    $stmtAOV->bindParam(':start_date', $past30Days);
    $stmtAOV->bindParam(':end_date', $currentDate);
    $stmtAOV->execute();
    $aov = round($stmtAOV->fetch(PDO::FETCH_COLUMN) ?? 0);

    // =======================================================
    // 🚨 5. 商品別貢献度ランキング - 修正箇所 🚨
    // ORDER_TARGET_ID ではなく ORDER_ITEMS.PRODUCT_ID を使用
    // =======================================================
    $stmtTopProducts = $PDO->prepare("
        SELECT 
            P.PRODUCT_NAME AS name,
            -- ORDER_ITEMSに商品ごとの金額(PRICE)があると仮定してSUM
            SUM(OITEM.PRICE) AS sales 
        FROM `ORDER` O
        JOIN ORDER_ITEMS OITEM ON O.ORDER_ID = OITEM.ORDER_ID 
        JOIN PRODUCT P ON OITEM.PRODUCT_ID = P.PRODUCT_ID
        WHERE O.PURCHASE_ORDER_DATE >= :start_date AND O.PURCHASE_ORDER_DATE <= :end_date
        GROUP BY P.PRODUCT_NAME
        ORDER BY sales DESC
        LIMIT 3
    ");
    $stmtTopProducts->bindParam(':start_date', $currentMonthStart);
    $stmtTopProducts->bindParam(':end_date', $currentDate);
    $stmtTopProducts->execute();
    $topProducts = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);

    // 6. 目標達成率の計算
    $salesTarget = 20000000;
    $targetRatio = ($currentSales > 0) ? ($currentSales / $salesTarget) * 100 : 0;
    
    
    // =======================================================
    // ⭐ 在庫アラートロジックの実装 - 一時テスト版 ⭐
    // 在庫が10個以下の商品をアラート対象とします
    // =======================================================
    $sql_alerts = "
        SELECT
            p.PRODUCT_NAME AS product_name, -- PRODUCT.NAME ではなく PRODUCT.PRODUCT_NAME を使用
            s.STOCK_QUANTITY AS current_stock
        FROM
            PRODUCT p
        JOIN
            STOCK s ON p.PRODUCT_ID = s.PRODUCT_ID
        WHERE
            s.STOCK_QUANTITY <= 10 -- 在庫が10個以下の商品をアラート
        ORDER BY
            s.STOCK_QUANTITY ASC 
        LIMIT 10
    ";

    $stmt_alerts = $PDO->prepare($sql_alerts);
    $stmt_alerts->execute();
    $alerts_raw = $stmt_alerts->fetchAll(PDO::FETCH_ASSOC);

    // アラートデータの構造化（不足数を計算するため、予測値をダミーで設定）
    $stock_alerts = [];
    $forecast_threshold = 15; // 予測販売数を15個と仮定
    
    foreach ($alerts_raw as $row) {
        $current_stock = (int)$row['current_stock'];
        $shortage = $forecast_threshold - $current_stock;
        
        $stock_alerts[] = [
            'product_name' => $row['product_name'],
            'current_stock' => $current_stock,
            'forecast' => $forecast_threshold, 
            'shortage' => $shortage, 
            'reason' => '在庫数しきい値以下',
        ];
    }
    
    // =======================================================
    // ⭐ JSONで結果を返す ⭐
    // =======================================================
    
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
        'stock_alerts' => $stock_alerts
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}