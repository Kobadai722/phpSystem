<?php
// add_sale_api.php

require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;
$notes      = $_POST['notes'] ?? "";

// 以下、既存の処理はそのまま
try {
    // 必須項目のバリデーション
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    if (!is_numeric($quantity) || (int)$quantity <= 0) {
        throw new Exception("数量が不正です。正の数値を入力してください。");
    }
    
    $quantity = (int)$quantity;

    $PDO->beginTransaction();

    $stmt = $PDO->prepare("
        SELECT s.STOCK_QTY, p.UNIT_SELLING_PRICE
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

    $stockQty = (int)$row['STOCK_QTY'];
    $unitPrice = (int)$row['UNIT_SELLING_PRICE'];

    if ($stockQty < $quantity) {
        throw new Exception("在庫が不足しています。注文数量: {$quantity}、現在の在庫: {$stockQty}");
    }

    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK SET STOCK_QTY = ? WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);

    $totalPrice = $unitPrice * $quantity;

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
        "message" => "販売を登録しました。",
        "new_stock" => $newStock,
        "total_price" => $totalPrice
    ]);
    exit;

} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("販売登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST, true));
    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}
