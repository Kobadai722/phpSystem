<?php
// エラー表示を有効化（開発用。公開時はオフに）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// レスポンスを JSON として返す
header('Content-Type: application/json; charset=UTF-8');

// DB接続情報
$host = 'localhost';
$dbname = 'your_database';
$user = 'your_username';
$pass = 'your_password';

// パラメータ取得（フィルタリング）
$orderId = $_GET['orderId'] ?? '';
$customerName = $_GET['customerName'] ?? '';
$paymentStatus = $_GET['paymentStatus'] ?? '';
$deliveryStatus = $_GET['deliveryStatus'] ?? '';

try {
    // DB接続
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // クエリ組み立て
    $sql = "SELECT * FROM orders WHERE 1=1";
    $params = [];

    if ($orderId !== '') {
        $sql .= " AND order_id LIKE :orderId";
        $params[':orderId'] = "%$orderId%";
    }
    if ($customerName !== '') {
        $sql .= " AND customer_name LIKE :customerName";
        $params[':customerName'] = "%$customerName%";
    }
    if ($paymentStatus !== '') {
        $sql .= " AND payment_status = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }
    if ($deliveryStatus !== '') {
        $sql .= " AND delivery_status = :deliveryStatus";
        $params[':deliveryStatus'] = $deliveryStatus;
    }

    // 実行
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // エラーレスポンス
    echo json_encode([
        'success' => false,
        'message' => 'DB接続エラー: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'システムエラー: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
