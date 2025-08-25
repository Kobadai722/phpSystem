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
    $conditions = [];
    $params = [];

    // 検索条件をGETリクエストから取得し、動的にSQLクエリを構築
    if (!empty($_GET['orderId'])) {
        $conditions[] = 'o.ORDER_ID = :orderId';
        $params[':orderId'] = $_GET['orderId'];
    }
    if (!empty($_GET['customerName'])) {
        $conditions[] = 'c.NAME LIKE :customerName';
        $params[':customerName'] = '%' . $_GET['customerName'] . '%';
    }
    if (!empty($_GET['paymentStatus'])) {
        $conditions[] = 'o.STATUS = :paymentStatus';
        $params[':paymentStatus'] = $_GET['paymentStatus'];
    }
    // ★ここを削除またはコメントアウト★
    // if (!empty($_GET['deliveryStatus'])) {
    //     $conditions[] = 'o.STATUS = :deliveryStatus';
    //     $params[':deliveryStatus'] = $_GET['deliveryStatus'];
    // }

    // ★テーブル名をS_ORDERに修正、顧客テーブルをS_CUSTOMERと仮定して結合★
    $query = 'SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS, c.NAME AS CUSTOMER_NAME FROM S_ORDER o JOIN S_CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID';
    
    // 検索条件があればWHERE句を追加
    if (count($conditions) > 0) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }
    
    // 注文日時が新しい順に並べ替え
    $query .= ' ORDER BY o.ORDER_DATETIME DESC';

    // プリペアドステートメントの準備と実行
    $stmt = $PDO->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
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