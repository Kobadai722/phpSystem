<?php
// get_sales_trend_api.php (修正版)

// DB接続とデータ取得、エラー処理
require_once '../../config.php'; 

header('Content-Type: application/json');

// 1. パラメータの取得
// ダッシュボードからは月次('month')で固定取得を想定。レポートページからは期間指定可能。
// startDate: 12ヶ月前の1日を計算
// strtotime('-11 months first day of this month') = 12ヶ月前の月の1日を取得
$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-11 months first day of this month')); 
$endDate = $_POST['end_date'] ?? date('Y-m-d'); // デフォルト: 今日
$groupBy = $_POST['group_by'] ?? 'month'; // デフォルト: month (月次)

// 2. 集計単位に基づいた日付フォーマットの決定
$dateFormatSql = '';
$dateFormatJs = ''; 
$intervalType = ''; // 補完処理で使うインターバルタイプ
switch ($groupBy) {
    case 'month':
        $dateFormatSql = '%Y-%m';
        $dateFormatJs = 'Y年m月'; // PHPのdate()フォーマットに変更
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
        DATE_FORMAT(PURCHASE_ORDER_DATE, '{$dateFormatSql}') AS period,
        SUM(PRICE) AS total_sales
    FROM 
        `ORDER`
    WHERE 
        PURCHASE_ORDER_DATE >= :start_date 
        AND PURCHASE_ORDER_DATE <= :end_date_adjusted
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
    $stmt->bindParam(':end_date_adjusted', $adjustedEndDate); // パラメータ名も調整
    

    $stmt->execute();
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. データ補完ロジックの追加 (売上がない期間に0を挿入)
    $aggregatedData = [];
    foreach ($salesData as $row) {
        // SQL DATE_FORMATの結果をキーとして売上データを格納
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

    // 期間内のすべての集計単位（日、月、年）をループ
    while ($current < $end) {
        $periodKey = $current->format('Y-m-d'); // SQLの結果と比較するためのキー
        
        if ($groupBy === 'month') {
            $periodKey = $current->format('Y-m');
        } elseif ($groupBy === 'year') {
            $periodKey = $current->format('Y');
        }

        // JS表示用のラベルを作成 (例: 2025/11/22, 2025年11月)
        $label = $current->format($dateFormatJs);

        // データベースの結果にキーが存在するか確認し、存在しなければ 0 を設定
        $sales = $aggregatedData[$periodKey] ?? 0.0;
        
        $filledData[] = [
            'period' => $label, // フロントエンド表示用
            'total_sales' => round($sales) // 小数点以下を丸める
        ];
        
        $current->add($interval); // 次の期間へ
    }


    // 5. 結果をJSONで返す
    echo json_encode([
        'success' => true,
        'data' => $filledData,
        'dateFormat' => $dateFormatJs // フロントエンドで表示に使用するフォーマット情報
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}
?>