<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../../config.php'; // 設定ファイルを読み込む

header('Content-Type: application/json'); // JSON形式でレスポンスを返すことを指定

try {
    // 検索条件を格納する配列
    $searchConditions = [];
    // プレースホルダにバインドする値を格納する配列
    $params = [];

    // 注文IDでの検索
    if (!empty($_GET['order_id'])) {
        $searchConditions[] = 'o.order_id = :order_id';
        $params[':order_id'] = $_GET['order_id'];
    }

    // 顧客名での検索
    if (!empty($_GET['customer_name'])) {
        $searchConditions[] = 'o.customer_name LIKE :customer_name';
        $params[':customer_name'] = '%' . $_GET['customer_name'] . '%';
    }

    // 支払い状況での検索
    if (!empty($_GET['payment_status'])) {
        $searchConditions[] = 'o.payment_status = :payment_status';
        $params[':payment_status'] = $_GET['payment_status'];
    }

    // 配送状況での検索
    if (!empty($_GET['delivery_status'])) {
        $searchConditions[] = 'o.delivery_status = :delivery_status';
        $params[':delivery_status'] = $_GET['delivery_status'];
    }

    // クエリの組み立て
    $query = 'SELECT o.order_id, o.customer_name, o.order_date, o.total_amount, o.payment_status, o.delivery_status
                FROM orders o';

    if (count($searchConditions) > 0) {
        $query .= ' WHERE ' . implode(' AND ', $searchConditions);
    }

    $query .= ' ORDER BY o.order_date DESC';

    // プリペアドステートメントの準備
    $stmt = $pdo->prepare($query);

    // パラメータのバインド
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    // SQL実行
    $stmt->execute();

    // 結果を取得
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // JSONで出力
    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    // エラーハンドリング
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}
