<?php
// add_sale_api.php

require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId   = $_POST['product_id'] ?? null;
$quantity    = $_POST['order_quantity'] ?? null;
$customerId  = $_POST['customer_id'] ?? null;
$employeeId  = $_POST['employee_id'] ?? null;
$notes       = $_POST['notes'] ?? "";

try {
    // 必須項目チェック
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    if (!is_numeric($quantity) || (int)$quantity <= 0) {
        throw new Exception("数量が不正です。");
    }
    $quantity = (int)$quantity;

    // トランザクション開始
    $PDO->beginTransaction();

    // 1. 商品単価と在庫取得（排他ロック）
    $stmt = $PDO->prepare("
        SELECT s.STOCK_QUANTITY, p.UNIT_SELLING_PRICE
        FROM STOCK s
        JOIN PRODUCT p ON s.PRODUCT_ID = p.PRODUCT_ID
        WHERE s.PRODUCT_ID = ?
        FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception("該当する商品が存在しません。");
    }

    $stockQty = (int)$row['STOCK_QUANTITY'];
    $unitPrice = (int)$row['UNIT_SELLING_PRICE'];
    $totalPrice = $unitPrice * $quantity;

    // 在庫チェック
    if ($stockQty < $quantity) {
        throw new Exception("在庫が不足しています。注文数量: {$quantity}、現在の在庫: {$stockQty}");
    }

    // 2. STOCK表更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK SET STOCK_QUANTITY = ? WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);

    // 3. ORDER表登録
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` (
            PURCHASE_ORDER_DATE,
            ORDER_TARGET_ID,
            ORDER_FLAG,
            PRICE,
            EMPLOYEE_ID
        ) VALUES (NOW(), ?, 1, ?, ?)
    ");
    $insertOrder->execute([
        $customerId,
        $totalPrice,
        $employeeId
    ]);

    // 4. SALE表登録
    $insertSale = $PDO->prepare("
        INSERT INTO SALE (
            PRODUCT_ID,
            SALE_QTY,
            CUSTOMER_ID,
            EMPLOYEE_ID,
            SALE_PRICE,
            SALE_DATE,
            NOTES
        ) VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $insertSale->execute([
        $productId,
        $quantity,
        $customerId,
        $employeeId,
        $totalPrice,
        $notes
    ]);

    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "new_stock" => $newStock,
        "total_price" => $totalPrice
    ]);
    exit;

} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }

    error_log("注文登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST, true));

    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}
