<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

try {
    // POSTパラメータ取得
    $productId   = $_POST['product_id'] ?? null;
    $quantity    = $_POST['order_quantity'] ?? null;
    $customerId  = $_POST['customer_id'] ?? null;
    $employeeId  = $_POST['employee_id'] ?? null;
    $notes       = $_POST['notes'] ?? null;  

    // バリデーション
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        echo json_encode([
            "success" => false,
            "message" => "必須項目が不足しています。"
        ]);
        exit;
    }

    // 商品の単価を取得
    $stmt = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :pid");
    $stmt->execute(['pid' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            "success" => false,
            "message" => "商品が存在しません。"
        ]);
        exit;
    }

    $unitPrice = (int)$product['UNIT_SELLING_PRICE'];
    $subtotal  = $unitPrice * (int)$quantity;

    // INSERT処理
    $stmtInsert = $PDO->prepare("
        INSERT INTO `ORDER`
            (PURCHASE_ORDER_DATE, PRODUCT_ID, QUANTITY, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID, NOTES)
        VALUES
            (NOW(), :pid, :qty, :cid, 1, :price, :eid, :notes)
    ");

    $stmtInsert->execute([
        'pid'   => $productId,
        'qty'   => $quantity,
        'cid'   => $customerId,
        'price' => $subtotal,
        'eid'   => $employeeId,
        'notes' => $notes     
    ]);

    echo json_encode([
        "success" => true,
        "message" => "登録完了"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "エラー: " . $e->getMessage()
    ]);
}
