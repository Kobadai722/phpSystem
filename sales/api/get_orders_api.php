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
    if (!empty($_GET['orderId'])) {
        $conditions[] = 'o.ORDER_ID = :orderId';
        $params[':orderId'] = $_GET['orderId'];
    }
    if (!empty($_GET['customerName'])) {
        $conditions[] = 'c.CUSTOMER_NAME LIKE :customerName';
        $params[':customerName'] = '%' . $_GET['customerName'] . '%';
    }
    // payment_statusとdelivery_statusを単一のSTATUSカラムで処理
    if (!empty($_GET['paymentStatus'])) {
        $conditions[] = 'o.STATUS = :paymentStatus';
        $params[':paymentStatus'] = $_GET['paymentStatus'];
    }
    if (!empty($_GET['deliveryStatus'])) {
        $conditions[] = 'o.STATUS = :deliveryStatus';
        $params[':deliveryStatus'] = $_GET['deliveryStatus'];
    }

    // クエリの構築
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

    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}