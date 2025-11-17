<?php

require_once '../../config.php'; 

header('Content-Type: application/json');

// フォームからのデータ取得
$productId = $_POST['product_id'] ?? 0;
$orderQuantity = $_POST['order_quantity'] ?? 0;
$customerId = $_POST['customer_id'] ?? 0;
$notes = $_POST['notes'] ?? '';
$employeeId = $_POST['employee_id'] ?? 0; // 担当者ID

// 必須入力チェック
if ($productId <= 0 || $orderQuantity <= 0 || $customerId <= 0 || $employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => '必要な項目が正しく入力されていません。（商品ID, 数量, 顧客ID, 担当者ID）']);
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();
    $currentDateTime = date('Y-m-d H:i:s');

    // 1. 商品情報の取得と単価・在庫の確認
    // FOR UPDATEでレコードをロックし、同時注文による在庫不足を防ぐ
    $stmtProduct = $PDO->prepare("SELECT UNIT_SELLING_PRICE, STOCK_QUANTITY FROM PRODUCT WHERE PRODUCT_ID = :product_id FOR UPDATE"); 
    $stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('指定された商品IDが見つかりませんでした。');
    }

    $unitPrice = $product['UNIT_SELLING_PRICE']; 
    $stockQuantity = $product['STOCK_QUANTITY'];
    $totalAmount = $unitPrice * $orderQuantity; 

    // 2. 在庫チェック
    if ($stockQuantity < $orderQuantity) {
        throw new Exception('在庫が不足しています。（現在在庫: ' . $stockQuantity . '個）');
    }

    // 3. 注文ヘッダーの登録 (法人/S_ORDER テーブルを使用。個人用ORDERテーブルも同様に処理可能)
    $stmtOrder = $PDO->prepare(
        "INSERT INTO S_ORDER (ORDER_DATETIME, CUSTOMER_ID, TOTAL_AMOUNT, EMPLOYEE_ID, STATUS, NOTES, CREATED_AT, UPDATED_AT) 
        VALUES (:order_datetime, :customer_id, :total_amount, :employee_id, '未払い', :notes, :created_at, :updated_at)"
    );

    $stmtOrder->bindParam(':order_datetime', $currentDateTime);
    $stmtOrder->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $stmtOrder->bindParam(':total_amount', $totalAmount);
    $stmtOrder->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
    $stmtOrder->bindParam(':notes', $notes);
    $stmtOrder->bindParam(':created_at', $currentDateTime);
    $stmtOrder->bindParam(':updated_at', $currentDateTime);

    $stmtOrder->execute();
    $newOrderId = $PDO->lastInsertId(); // 注文ヘッダーIDを取得

    // 4. 注文明細 (ORDER_ITEMS) の登録
    // ORDER_HEADER_IDにS_ORDERのIDを格納する
    $stmtItems = $PDO->prepare(
        "INSERT INTO ORDER_ITEMS (ORDER_HEADER_ID, PRODUCT_ID, UNIT_PRICE, QUANTITY, SUBTOTAL, CREATED_AT, UPDATED_AT) 
        VALUES (:order_id, :product_id, :unit_price, :quantity, :subtotal, :created_at, :updated_at)"
    );

    $stmtItems->bindParam(':order_id', $newOrderId, PDO::PARAM_INT);
    $stmtItems->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtItems->bindParam(':unit_price', $unitPrice);
    $stmtItems->bindParam(':quantity', $orderQuantity, PDO::PARAM_INT);
    $stmtItems->bindParam(':subtotal', $totalAmount);
    $stmtItems->bindParam(':created_at', $currentDateTime);
    $stmtItems->bindParam(':updated_at', $currentDateTime);

    $stmtItems->execute();

    // 5. 在庫の更新（数量の減算）
    $stmtStockUpdate = $PDO->prepare(
        "UPDATE PRODUCT SET STOCK_QUANTITY = STOCK_QUANTITY - :quantity WHERE PRODUCT_ID = :product_id"
    );
    $stmtStockUpdate->bindParam(':quantity', $orderQuantity, PDO::PARAM_INT);
    $stmtStockUpdate->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtStockUpdate->execute();


    // 6. トランザクション完了
    $PDO->commit();
    echo json_encode([
        'success' => true, 
        'message' => '注文が正常に登録され、在庫が更新されました。（ID: ' . $newOrderId . '）', 
        'order_id' => $newOrderId
    ]);

} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    echo json_encode(['success' => false, 'message' => '注文処理中にエラーが発生しました: ' . $e->getMessage()]);
}
?>