<?php

require_once '../../config.php'; 

header('Content-Type: application/json');

// --- データの取得と整形 ---
$productId      = $_POST['product_id'] ?? 0;
$orderQuantity  = $_POST['order_quantity'] ?? 0;
$customerId     = $_POST['customer_id'] ?? 0;
$employeeId     = $_POST['employee_id'] ?? 0;   // ← UI で選択するため修正
$notes          = $_POST['notes'] ?? null;      // ORDER テーブルに NOTES が無ければ無視される

// --- 入力チェック ---
$productId     = filter_var($productId, FILTER_VALIDATE_INT);
$orderQuantity = filter_var($orderQuantity, FILTER_VALIDATE_INT);
$customerId    = filter_var($customerId, FILTER_VALIDATE_INT);
$employeeId    = filter_var($employeeId, FILTER_VALIDATE_INT);

if ($productId <= 0 || $orderQuantity <= 0 || $customerId <= 0 || $employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => '入力値が不正です。すべて1以上の整数を指定してください。']);
    exit;
}

$PDO->beginTransaction();

try {

    // --- 1. 商品単価を取得 ---
    $stmtProduct = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :product_id");
    $stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '商品が見つかりません。']);
        exit;
    }

    $unitPrice = $product['UNIT_SELLING_PRICE'];
    $totalAmount = $unitPrice * $orderQuantity;
    $currentDateTime = date('Y-m-d H:i:s');

    // --- 2. ORDER に挿入（ORDER_FLAG=1 → 売上） ---
    $stmtOrder = $PDO->prepare(
        "INSERT INTO `ORDER` (
            PURCHASE_ORDER_DATE,
            ORDER_TARGET_ID,
            ORDER_FLAG,
            PRICE,
            EMPLOYEE_ID
        ) VALUES (
            :order_date,
            :customer_id,
            1,
            :price,
            :employee_id
        )"
    );

    $stmtOrder->bindParam(':order_date', $currentDateTime);
    $stmtOrder->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $stmtOrder->bindParam(':price', $totalAmount, PDO::PARAM_INT);
    $stmtOrder->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);

    $stmtOrder->execute();

    $orderId = $PDO->lastInsertId();

    $PDO->commit();

    echo json_encode([
        'success' => true,
        'message' => "売上データを登録しました (ORDER_ID: {$orderId})"
    ]);
} catch (Exception $e) {
    if ($PDO->inTransaction()) $PDO->rollBack();
    error_log("売上登録エラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'DBエラー: ' . $e->getMessage()]);
}
