<?php

require_once '../../config.php'; 

header('Content-Type: application/json');

$productId = $_POST['product_id'] ?? 0;
$orderQuantity = $_POST['order_quantity'] ?? 0;
$customerId = $_POST['customer_id'] ?? 0;
$notes = $_POST['notes'] ?? '';

$PDO->beginTransaction();

$stmtProduct = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :product_id");
$stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
$stmtProduct->execute();
$product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $PDO->rollBack();
    echo json_encode(['success' => false, 'message' => '商品が見つかりませんでした。']);
    exit;
}

$unitPrice = $product['UNIT_SELLING_PRICE']; 
$totalAmount = $unitPrice * $orderQuantity; 
$currentDateTime = date('Y-m-d H:i:s');

$stmtOrder = $PDO->prepare(
    "INSERT INTO S_ORDER (ORDER_DATETIME, CUSTOMER_ID, TOTAL_AMOUNT, STATUS, NOTES, CREATED_AT, UPDATED_AT) 
    VALUES (:order_datetime, :customer_id, :total_amount, '注文受付', :notes, :created_at, :updated_at)"
);

$stmtOrder->bindParam(':order_datetime', $currentDateTime);
$stmtOrder->bindParam(':customer_id', $customerId);
$stmtOrder->bindParam(':total_amount', $totalAmount);
$stmtOrder->bindParam(':notes', $notes);
$stmtOrder->bindParam(':created_at', $currentDateTime);
$stmtOrder->bindParam(':updated_at', $currentDateTime);

$stmtOrder->execute();

$newOrderId = $PDO->lastInsertId();

$PDO->commit();
echo json_encode([
    'success' => true, 
    'message' => '注文が正常に登録されました。（ID: ' . $newOrderId . '）', 
    'order_id' => $newOrderId
]);

?>