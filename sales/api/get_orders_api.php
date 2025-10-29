<?php
// PHPのエラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース設定ファイルを読み込む
require_once '../../config.php';

// JSON形式のレスポンスヘッダーを設定
header('Content-Type: application/json');

try {
    // orders.jsから送信される個別の検索パラメータを取得
    $orderId = $_GET['orderId'] ?? '';
    $customerName = $_GET['customerName'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';

    // SQLクエリを構築
    $sql = "SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS, c.NAME AS CUSTOMER_NAME 
            FROM S_ORDER o 
            LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID";
            
    // WHERE句とパラメータを格納する配列
    $whereClauses = [];
    $params = [];

    // 検索条件を動的に追加
    if (!empty($orderId)) {
        $whereClauses[] = "o.ORDER_ID = :orderId";
        $params[':orderId'] = $orderId;
    }
    if (!empty($customerName)) {
        $whereClauses[] = "c.NAME LIKE :customerName";
        $params[':customerName'] = '%' . $customerName . '%';
    }
    if (!empty($paymentStatus)) {
        $whereClauses[] = "o.STATUS = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }

    // 複数の検索条件がある場合はANDで結合してWHERE句を構築
    if (count($whereClauses) > 0) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    // 注文日時が新しい順に並べ替え
    $sql .= " ORDER BY o.ORDER_DATETIME ";

    // プリペアドステートメントの準備
    $stmt = $PDO->prepare($sql);

    // パラメータをバインド
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // SQLクエリを実行
    $stmt->execute();
    
    // 結果を連想配列としてすべて取得
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 成功レスポンスとしてJSONデータを返す
    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    // データベース接続またはクエリ実行エラーが発生した場合
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}
?>