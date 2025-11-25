<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;

try {
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    if (!is_numeric($quantity) || (int)$quantity <= 0) {
        throw new Exception("数量が不正です。");
    }
    $quantity = (int)$quantity;

    $PDO->beginTransaction();

    // 在庫取得（排他ロック）
    $stmt = $PDO->prepare("
        SELECT s.STOCK_QUANTITY, p.UNIT_SELLING_PRICE
        FROM STOCK s
        JOIN PRODUCT p ON s.PRODUCT_ID = p.PRODUCT_ID
        WHERE s.PRODUCT_ID = ?
        FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("該当商品が存在しません。");

    $stockQty  = (int)$row['STOCK_QUANTITY'];
    $unitPrice = (int)$row['UNIT_SELLING_PRICE'];

    if ($stockQty < $quantity) {
        throw new Exception("在庫不足。現在の在庫: {$stockQty}");
    }

    // 在庫減算
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("UPDATE STOCK SET STOCK_QUANTITY = ? WHERE PRODUCT_ID = ?");
    if (!$updateStock->execute([$newStock, $productId])) {
        throw new Exception("在庫更新失敗");
    }

    // ORDER登録（予約語対策）
    $totalPrice = $unitPrice * $quantity;
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` 
        (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID) 
        VALUES (NOW(), ?, 0, ?, ?)
    ");
    if (!$insertOrder->execute([$customerId, $totalPrice, $employeeId])) {
        throw new Exception("ORDER登録失敗");
    }

    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "new_stock" => $newStock,
        "total_price" => $totalPrice
    ]);

} catch (Exception $e) {
    if ($PDO->inTransaction()) $PDO->rollBack();
    error_log("ORDER登録エラー: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
}
