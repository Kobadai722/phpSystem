<?php
header('Content-Type: application/json');

require_once '../../config.php'; // データベース接続設定ファイルを読み込む

try {
    $pdo = new PDO(DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 検索パラメータの取得
    $orderId = $_GET['orderId'] ?? '';
    $customerName = $_GET['customerName'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';
    $deliveryStatus = $_GET['deliveryStatus'] ?? '';

    // SQLクエリの構築
    $sql = "SELECT * FROM orders WHERE 1=1";
    $params = [];

    if (!empty($orderId)) {
        $sql .= " AND order_id LIKE :orderId";
        $params[':orderId'] = '%' . $orderId . '%';
    }
    if (!empty($customerName)) {
        $sql .= " AND customer_name LIKE :customerName";
        $params[':customerName'] = '%' . $customerName . '%';
    }
    if (!empty($paymentStatus)) {
        $sql .= " AND payment_status = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }
    if (!empty($deliveryStatus)) {
        $sql .= " AND delivery_status = :deliveryStatus";
        $params[':deliveryStatus'] = $deliveryStatus;
    }

    $sql .= " ORDER BY order_datetime DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'message' => '注文データを正常に取得しました。'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'orders' => [],
        'message' => 'データベースエラー: ' . $e->getMessage()
    ]);
    exit;
}
?>