<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId   = $_POST['product_id'] ?? null;
$quantity    = $_POST['order_quantity'] ?? null;  // sale_add.php の name に合わせる
$customerId  = $_POST['customer_id'] ?? null;
$employeeId  = $_POST['employee_id'] ?? null;
$notes       = $_POST['notes'] ?? "";

// バリデーション
if (!$productId || !$quantity || !$customerId || !$employeeId) {
    echo json_encode([
        "success" => false,
        "message" => "必要な項目が入力されていません。"
    ]);
    exit;
}

if (!is_numeric($quantity) || $quantity <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "数量が不正です。"
    ]);
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();

    // 商品の在庫確認
    $stmt = $PDO->prepare("
        SELECT s.STOCK_QTY, p.UNIT_SELLING_PRICE
        FROM STOCK s
        JOIN PRODUCT p ON s.PRODUCT_ID = p.PRODUCT_ID
        WHERE s.PRODUCT_ID = ?
    ");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("該当する商品が存在しません。");
    }

    $stockQty = (int)$row['STOCK_QTY'];
    $unitPrice = (int)$row['UNIT_SELLING_PRICE'];

    if ($stockQty < $quantity) {
        throw new Exception("在庫が不足しています。");
    }

    // 在庫更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK SET STOCK_QTY = ? WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);

    // 販売価格計算
    $totalPrice = $unitPrice * $quantity;

    // SALEテーブルへ登録
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

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}
