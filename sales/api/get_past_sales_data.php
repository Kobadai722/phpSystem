<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php';

header('Content-Type: application/json');

try {
    // 過去のデータ取得期間を設定 (例: 過去3年間)
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime('-3 year')); 

    // 1. MySQLから日次で売上データを集計
    // ORDERテーブルから注文日と価格の合計を取得
    $sql = "
        SELECT 
            DATE(PURCHASE_ORDER_DATE) AS ds,
            SUM(PRICE) AS y
        FROM 
            `ORDER`
        WHERE 
            PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date_adjusted
        GROUP BY 
            ds
        ORDER BY 
            ds ASC
    ";

    $stmt = $PDO->prepare($sql);
    
    // 終了日はその日の終わりまで含める
    $adjustedEndDate = $endDate . ' 23:59:59';
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date_adjusted', $adjustedEndDate);
    

    $stmt->execute();
    $salesDataRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. 取得したデータを連想配列に変換 (日付をキーに)
    $aggregatedData = [];
    foreach ($salesDataRaw as $row) {
        $aggregatedData[$row['ds']] = (float)$row['y'];
    }

    // 3. データ補完 (売上がない日に '0' を挿入)
    $filledData = [];
    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day'); // 終了日も含めるため

    $interval = new DateInterval("P1D"); // 1日ごとのインターバル

    while ($current < $end) {
        $dateKey = $current->format('Y-m-d'); 

        // 売上があればその値、なければ 0 を設定
        $sales = $aggregatedData[$dateKey] ?? 0.0;
        
        $filledData[] = [
            'ds' => $dateKey, 
            'y' => round($sales) // Prophetに渡す前に整数に丸める
        ];
        
        $current->add($interval);
    }


    // 4. 結果をJSONで返す
    echo json_encode([
        'success' => true,
        'sales_data' => $filledData, // dsとyの形式で格納
        'data_count' => count($filledData)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // データベースエラー
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage(), 'sales_data' => []]);
} catch (Exception $e) {
    // その他のシステムエラー
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage(), 'sales_data' => []]);
}
?>