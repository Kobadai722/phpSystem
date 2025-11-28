<?php
// add_sale_api.php (修正版)
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
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

    // 1. 在庫確認と同時に、単価を取得 (FOR UPDATEでロックをかける)
    // PRODUCTテーブルからUNIT_PRICEを取得する
    $stmt = $PDO->prepare("
        SELECT S.STOCK_QUANTITY, P.UNIT_PRICE 
        FROM STOCK S
        JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID 
        WHERE S.PRODUCT_ID = ? FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); // STOCKとUNIT_PRICEの両方を含む

    if (!$dataRow) {
        throw new Exception("該当する商品または在庫が存在しません。");
    }

    $stockQty = (int)$dataRow['STOCK_QUANTITY'];
    $unitPrice = (int)$dataRow['UNIT_PRICE']; // 単価を取得
    
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }
    
    // 合計金額を計算する
    $totalPrice = $unitPrice * $quantity; 
    $orderFlag  = 1; // 通常注文

    // 2. ORDER登録
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` 
            (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES
            (NOW(), ?, ?, ?, ?)
    ");
    // ORDER_TARGET_ID (CUSTOMER_ID) と PRICE のバインド変数の順序を調整
    // ORDER_TARGET_ID (customerId) は O.PURCHASE_ORDER_DATE の次に移動
    // PRICE (totalPrice) は O.ORDER_FLAG の次に配置
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
        "total_price" => $totalPrice, // 新しく追加
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