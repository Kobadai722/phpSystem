<?php

require_once '../../config.php'; 

header('Content-Type: application/json');

// フォームからのデータ取得
$productId = $_POST['product_id'] ?? 0;
$orderQuantity = $_POST['order_quantity'] ?? 0;
$customerId = $_POST['customer_id'] ?? 0;
// $notesはORDERテーブルにカラムがないため、ここでは使用しないが、変数として取得しておく
// $notes = $_POST['notes'] ?? ''; 
$employeeId = $_POST['employee_id'] ?? 0; // EMPLOYEE_IDを取得

// 必須入力チェック
if ($productId <= 0 || $orderQuantity <= 0 || $customerId <= 0 || $employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => '必要な項目が正しく入力されていません。（商品ID, 数量, 顧客ID, 担当者ID）']);
    exit;
}

try {
    // トランザクション開始: 注文ヘッダー、明細、在庫更新は一連の処理として実行
    $PDO->beginTransaction();
    $currentDateTime = date('Y-m-d H:i:s');

    // 1. 商品情報の取得と単価・在庫の確認 (FOR UPDATEでロック)
    $stmtProduct = $PDO->prepare("SELECT UNIT_SELLING_PRICE, STOCK_QUANTITY FROM PRODUCT WHERE PRODUCT_ID = :product_id FOR UPDATE"); 
    $stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('指定された商品IDが見つかりませんでした。');
    }

    $unitPrice = $product['UNIT_SELLING_PRICE']; 
    $stockQuantity = $product['STOCK_QUANTITY'];
    $totalAmount = $unitPrice * $orderQuantity; // 注文の合計金額

    // 2. 在庫チェック
    if ($stockQuantity < $orderQuantity) {
        throw new Exception('在庫が不足しています。（現在在庫: ' . $stockQuantity . '個）');
    }
    
    // 3. 担当者IDの存在チェック (FOREIGN KEY制約をより安全にするため)
    $stmtEmployeeCheck = $PDO->prepare("SELECT EMPLOYEE_ID FROM EMPLOYEE WHERE EMPLOYEE_ID = :employee_id");
    $stmtEmployeeCheck->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);
    $stmtEmployeeCheck->execute();
    if ($stmtEmployeeCheck->rowCount() === 0) {
        throw new Exception('指定された担当者IDが見つかりませんでした。');
    }


    //  4. 注文ヘッダーの登録 (ORDER テーブルを使用) 
    $stmtOrder = $PDO->prepare(
        "INSERT INTO `ORDER` (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES (:order_date, :target_id, 1, :price, :employee_id)"
    );

    $stmtOrder->bindParam(':order_date', $currentDateTime);
    $stmtOrder->bindParam(':target_id', $customerId, PDO::PARAM_INT); // ORDER_TARGET_ID = 顧客ID
    $stmtOrder->bindParam(':price', $totalAmount); // PRICE = 合計金額
    $stmtOrder->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);

    $stmtOrder->execute();
    $newOrderId = $PDO->lastInsertId(); // 注文ヘッダーID (ORDER_ID) を取得

    //  5. 注文明細 (ORDER_ITEMS) の登録 
    // ORDER_HEADER_IDにORDER_IDを格納する
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

    // 6. 在庫の更新（数量の減算）
    $stmtStockUpdate = $PDO->prepare(
        "UPDATE PRODUCT SET STOCK_QUANTITY = STOCK_QUANTITY - :quantity WHERE PRODUCT_ID = :product_id"
    );
    $stmtStockUpdate->bindParam(':quantity', $orderQuantity, PDO::PARAM_INT);
    $stmtStockUpdate->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtStockUpdate->execute();


    // 7. トランザクション完了
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