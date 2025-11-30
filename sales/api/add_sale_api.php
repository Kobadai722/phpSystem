<?php
require_once '../../config.php'; 
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;
$notes      = $_POST['notes'] ?? null;

try {

    // 必須チェック
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        throw new Exception("数量が不正です。");
    }

    // トランザクション開始
    $PDO->beginTransaction();

    // 商品の在庫 & 単価取得（FOR UPDATE でロック）
    $stmt = $PDO->prepare("
        SELECT S.STOCK_QUANTITY, P.UNIT_SELLING_PRICE
        FROM STOCK S
        JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID
        WHERE S.PRODUCT_ID = ?
        FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("商品または在庫が存在しません。");
    }

    $stockQty = (int)$row['STOCK_QUANTITY'];
    $unitPrice = $row['UNIT_SELLING_PRICE'];

    // 在庫不足チェック
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }

    // 合計金額
    $totalPrice = $unitPrice * $quantity;
    $orderFlag = 1;

    // ORDER登録
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER`
        (PURCHASE_ORDER_DATE, PRODUCT_ID, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID, NOTES)
        VALUES
        (NOW(), ?, ?, ?, ?, ?, ?)
    ");

    if (!$insertOrder->execute([
        $productId,     // ← 商品IDを正しく登録
        $customerId,
        $orderFlag,
        $totalPrice,
        $employeeId,
        $notes
    ])) {
        throw new Exception("ORDER登録失敗");
    }

    // 在庫更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK 
        SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() 
        WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);

    // コミット
    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "total_price" => $totalPrice,
        "new_stock" => $newStock
    ]);

} catch (Exception $e) {

    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }

    error_log("注文登録エラー: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}