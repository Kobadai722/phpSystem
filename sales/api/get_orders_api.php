<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';

header('Content-Type: application/json');

try {
    $conditions = [];
    $params = [];

    // 検索条件の追加
    if (!empty($_GET['order_id'])) {
        $conditions[] = 'o.ORDER_ID = :order_id';
        $params[':order_id'] = $_GET['order_id'];
    }
    if (!empty($_GET['customer_name'])) {
        $conditions[] = 'c.CUSTOMER_NAME LIKE :customer_name';
        $params[':customer_name'] = '%' . $_GET['customer_name'] . '%';
    }
    // payment_statusとdelivery_statusを単一のSTATUSカラムで処理
    if (!empty($_GET['payment_status'])) {
        $conditions[] = 'o.STATUS = :payment_status';
        $params[':payment_status'] = $_GET['payment_status'];
    }
    if (!empty($_GET['delivery_status'])) {
        $conditions[] = 'o.STATUS = :delivery_status';
        $params[':delivery_status'] = $_GET['delivery_status'];
    }

    // クエリの構築
    // customersテーブルをJOINして顧客名を取得
    $query = 'SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS, c.CUSTOMER_NAME FROM orders o JOIN customers c ON o.CUSTOMER_ID = c.CUSTOMER_ID';
    if (count($conditions) > 0) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $query .= ' ORDER BY o.ORDER_DATETIME DESC';

    $stmt = $PDO->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 顧客名を取得するためのサンプルテーブルスキーマ
    // 
    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}