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

try {
    // 必須項目のバリデーション
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    // 数量の数値チェックと正の値であることの確認
    if (!is_numeric($quantity) || (int)$quantity <= 0) {
        throw new Exception("数量が不正です。正の数値を入力してください。");
    }

    $quantity = (int)$quantity;

    // トランザクション開始
    $PDO->beginTransaction();

    // 1. 商品の在庫と単価を取得（排他ロック）
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

    // 2. 在庫チェック
    if ($stockQty < $quantity) {
        throw new Exception("在庫が不足しています。注文数量: {$quantity}、現在の在庫: {$stockQty}");
    }

    // 3. 在庫更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK SET STOCK_QUANTITY = ? WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);

    // 4. 販売価格計算
    $totalPrice = $unitPrice * $quantity;

    // 5. ORDERテーブルへ登録
    $insertSale = $PDO->prepare("
        INSERT INTO `ORDER` (
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

    // コミット
    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "販売を登録しました。",
        "new_stock" => $newStock,
        "total_price" => $totalPrice
    ]);
    exit;

} catch (Exception $e) {

    // ロールバック
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }

    // 開発者向けエラーログ
    error_log("販売登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST, true));

    // ユーザーへのフィードバック
    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}
?>
