<?php
// get_sales_trend_api.php

// DB接続とデータ取得、エラー処理
require_once '../../config.php'; 

header('Content-Type: application/json');

// 1. パラメータの取得
$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-11 months first day of this month')); 
$endDate = $_POST['end_date'] ?? date('Y-m-d'); 
$groupBy = $_POST['group_by'] ?? 'month'; 

// 2. 集計単位に基づいた日付フォーマットの決定
$dateFormatSql = '';
$dateFormatJs = ''; 
$intervalType = ''; 
switch ($groupBy) {
    case 'month':
        $dateFormatSql = '%Y-%m';
        $dateFormatJs = 'Y年m月'; 
        $intervalType = 'month';
        break;
    case 'year':
        $dateFormatSql = '%Y';
        $dateFormatJs = 'Y年';
        $intervalType = 'year';
        break;
    case 'day':
    default:
        $dateFormatSql = '%Y-%m-%d';
        $dateFormatJs = 'Y/m/d';
        $intervalType = 'day';
        break;
}

try {
    // 3. SQLクエリの構築と実行
    $sql = "
        SELECT 
            DATE_FORMAT(PURCHASE_ORDER_DATE, :date_format_sql) AS period,
            SUM(PRICE) AS total_sales
        FROM 
            `ORDER`
        WHERE 
            PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date_adjusted
        GROUP BY 
            period
        ORDER BY 
            period ASC
    ";

    $stmt = $PDO->prepare($sql);
    
    $stmt->bindParam(':date_format_sql', $dateFormatSql);
    $adjustedEndDate = $endDate . ' 23:59:59';
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date_adjusted', $adjustedEndDate);
    

    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. データ補完ロジックの追加 (売上がない期間に0を挿入)
    $aggregatedData = [];
    foreach ($salesData as $row) {
        $aggregatedData[$row['period']] = (float)$row['total_sales'];
    }

    $filledData = [];
    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    // 終了日を含めるために、インターバルを1つ増やす
    if ($groupBy === 'day') {
        $end->modify('+1 day');
    } elseif ($groupBy === 'month') {
        $end->modify('+1 month');
    } elseif ($groupBy === 'year') {
        $end->modify('+1 year');
    }

    $interval = new DateInterval("P1" . strtoupper(substr($intervalType, 0, 1))); // P1D, P1M, P1Y

    while ($current < $end) {
        $periodKey = $current->format('Y-m-d'); 
        
        if ($groupBy === 'month') {
            $periodKey = $current->format('Y-m');
        } elseif ($groupBy === 'year') {
            $periodKey = $current->format('Y');
        }

        $label = $current->format($dateFormatJs);

        $sales = $aggregatedData[$periodKey] ?? 0.0;
        
        $filledData[] = [
            'period' => $label, 
            'total_sales' => round($sales) 
        ];
        
        $current->add($interval);
    }


    // 5. 結果をJSONで返す
    echo json_encode([
        'success' => true,
        'data' => $filledData,
        'dateFormat' => $dateFormatJs
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}
?>