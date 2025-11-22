<?php
// DB接続とデータ取得、エラー処理
require_once '../../../config.php'; 

header('Content-Type: application/json');

// 1. パラメータの取得
// ダッシュボードからは月次('month')で固定取得を想定。レポートページからは期間指定可能。
$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-11 months first day of this month')); // デフォルト: 12ヶ月前
$endDate = $_POST['end_date'] ?? date('Y-m-d'); // デフォルト: 今日
$groupBy = $_POST['group_by'] ?? 'month'; // デフォルト: month (月次)

// 2. 集計単位に基づいた日付フォーマットの決定
$dateFormatSql = '';
$dateFormatJs = ''; 
switch ($groupBy) {
    case 'month':
        // SQL: 2025-11
        $dateFormatSql = '%Y-%m';
        // JS表示用: 2025年11月
        $dateFormatJs = '%Y年%m月';
        break;
    case 'year':
        // SQL: 2025
        $dateFormatSql = '%Y';
        // JS表示用: 2025年
        $dateFormatJs = '%Y年';
        break;
    case 'day':
    default:
        // SQL: 2025-11-22
        $dateFormatSql = '%Y-%m-%d';
        // JS表示用: 2025/11/22
        $dateFormatJs = '%Y/%m/%d';
        break;
}

try {
    // 3. SQLクエリの構築
    // DATE_FORMAT関数を使用して、PURCHASE_ORDER_DATEを集計単位に変換
    $sql = "
        SELECT 
            DATE_FORMAT(PURCHASE_ORDER_DATE, :date_format_sql) AS period,
            SUM(PRICE) AS total_sales
        FROM 
            `ORDER`
        WHERE 
            PURCHASE_ORDER_DATE >= :start_date AND PURCHASE_ORDER_DATE <= :end_date
        GROUP BY 
            period
        ORDER BY 
            period ASC
    ";

    $stmt = $PDO->prepare($sql);
    
    // パラメータのバインド
    $stmt->bindParam(':date_format_sql', $dateFormatSql);
    // 日付はDATE型で比較できるように調整（末尾に' 23:59:59'を追加して当日を含むようにする）
    $adjustedEndDate = $endDate . ' 23:59:59';
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $adjustedEndDate);

    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. 結果をJSONで返す
    echo json_encode([
        'success' => true,
        'data' => $salesData,
        'dateFormat' => $dateFormatJs // フロントエンドで表示に使用するフォーマット情報
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}
?>