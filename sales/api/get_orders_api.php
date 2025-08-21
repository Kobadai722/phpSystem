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
        $conditions[] = 'o.order_id = :order_id';
        $params[':order_id'] = $_GET['order_id'];
    }
    if (!empty($_GET['customer_name'])) {
        $conditions[] = 'o.customer_name LIKE :customer_name';
        $params[':customer_name'] = '%' . $_GET['customer_name'] . '%';
    }
    if (!empty($_GET['payment_status'])) {
        $conditions[] = 'o.payment_status = :payment_status';
        $params[':payment_status'] = $_GET['payment_status'];
    }
    if (!empty($_GET['delivery_status'])) {
        $conditions[] = 'o.delivery_status = :delivery_status';
        $params[':delivery_status'] = $_GET['delivery_status'];
    }

    // クエリの構築
    $query = 'SELECT o.order_id, o.customer_name, o.order_date, o.total_amount, o.payment_status, o.delivery_status FROM orders o';
    if (count($conditions) > 0) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $query .= ' ORDER BY o.order_date DESC';

    $stmt = $pdo->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}