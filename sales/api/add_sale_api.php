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

    // 商品単価取得
    $stmt = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = ?");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception("該当する商品が存在しません。");
    }
    $unitPrice = (int)$row['UNIT_SELLING_PRICE'];
    $totalPrice = $unitPrice * $quantity;

    // トランザクション開始
    $PDO->beginTransaction();

    // 1. ORDER表に登録
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

    // 2. SALE表に登録（必要であれば）
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
