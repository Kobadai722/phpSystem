<?php
// add_sale_api.php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;

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

    // 1. 在庫確認
    $stmt = $PDO->prepare("SELECT STOCK_QUANTITY FROM STOCK WHERE PRODUCT_ID = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$stockRow) {
        throw new Exception("該当する商品在庫が存在しません。");
    }

    $stockQty = (int)$stockRow['STOCK_QUANTITY'];
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }

    // 2. ORDER登録
    $totalPrice = 0; // 今回は単価がわからなければ0でもOK
    $orderFlag  = 1; // 通常注文

    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` 
            (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES
            (NOW(), ?, ?, ?, ?)
    ");
    if (!$insertOrder->execute([$customerId, $orderFlag, $totalPrice, $employeeId])) {
        $error = $insertOrder->errorInfo();
        throw new Exception("ORDER登録失敗: " . implode(", ", $error));
    }

    // 3. STOCK更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("UPDATE STOCK SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() WHERE PRODUCT_ID = ?");
    if (!$updateStock->execute([$newStock, $productId])) {
        $error = $updateStock->errorInfo();
        throw new Exception("在庫更新失敗: " . implode(", ", $error));
    }

    // コミット
    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "new_stock" => $newStock
    ]);
    exit;

} catch (Exception $e) {
    if ($PDO->inTransaction()) $PDO->rollBack();
    error_log("注文登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST,true));

    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}
