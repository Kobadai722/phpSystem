<?php

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

    // 数量チェック
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        throw new Exception("数量が不正です。");
    }

    // トランザクション開始
    $PDO->beginTransaction();

    // 1. 在庫確認と単価取得 (FOR UPDATEで排他ロック)
    $stmt = $PDO->prepare("
        SELECT S.STOCK_QUANTITY, P.UNIT_SELLING_PRICE 
        FROM STOCK S
        JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID 
        WHERE S.PRODUCT_ID = ? FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 

    if (!$dataRow) {
        throw new Exception("該当する商品または在庫が存在しません。");
    }

    $stockQty = (int)$dataRow['STOCK_QUANTITY'];
    $unitPrice = $dataRow['UNIT_SELLING_PRICE']; // BIGINT対応のためintキャストなし
    
    // 在庫不足チェック
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }
    
    // 2. 合計金額の計算
    $totalPrice = $unitPrice * $quantity; 
    $orderFlag  = 1; // 注文ステータス

    // 3. ORDERテーブルに売上情報を登録
    // 注意: ORDERテーブルのPRICEカラムはBIGINTである必要があります
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` 
            (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES
            (NOW(), ?, ?, ?, ?)
    ");
    
    // バインド: $customerId, $orderFlag, $totalPrice, $employeeId
    if (!$insertOrder->execute([$customerId, $orderFlag, $totalPrice, $employeeId])) {
        $error = $insertOrder->errorInfo();
        error_log("ORDER INSERT FAILED: " . $error[2]);
        throw new Exception("ORDER登録失敗: データベースエラーが発生しました。");
    }
    
    // 4. STOCKテーブルの在庫数を更新（減少させる）
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("UPDATE STOCK SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() WHERE PRODUCT_ID = ?");
    if (!$updateStock->execute([$newStock, $productId])) {
        $error = $updateStock->errorInfo();
        throw new Exception("在庫更新失敗: " . implode(", ", $error));
    }

    // 全て成功したらコミット
    $PDO->commit();

    // 成功レスポンス（JSON）
    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "total_price" => $totalPrice, 
        "new_stock" => $newStock
    ]);
    exit;

} catch (Exception $e) {
    // エラー時はロールバック
    if (isset($PDO) && $PDO->inTransaction()) {
        $PDO->rollBack();
    }
    
    $logMessage = "注文登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST,true);
    error_log($logMessage);

    // 失敗レスポンス（JSON）
    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}
// ファイルの末尾に余計な改行やタグがないことを確認