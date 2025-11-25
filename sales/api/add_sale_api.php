<?php

require_once '../../config.php'; 
header('Content-Type: application/json');

// --- 入力値取得 ---
$productId = $_POST['product_id'] ?? 0;
$orderQuantity = $_POST['order_quantity'] ?? 0;
$customerId = $_POST['customer_id'] ?? 0;

// 担当者ID（ログインユーザー想定） → 仮で1
$employeeId = 1;

// --- バリデーション ---
$productId = filter_var($productId, FILTER_VALIDATE_INT);
$orderQuantity = filter_var($orderQuantity, FILTER_VALIDATE_INT);
$customerId = filter_var($customerId, FILTER_VALIDATE_INT);
$employeeId = filter_var($employeeId, FILTER_VALIDATE_INT);

if (!$productId || !$orderQuantity || !$customerId || !$employeeId ||
    $productId <= 0 || $orderQuantity <= 0 || $customerId <= 0) {
    echo json_encode(['success' => false, 'message' => '入力値が不正です。']);
    exit;
}

$PDO->beginTransaction();

try {
    // 単価取得
    $stmt = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :pid");
    $stmt->bindParam(':pid', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '商品が見つかりません。']);
        exit;
    }

    $unitPrice = $product['UNIT_SELLING_PRICE'];
    $total = $unitPrice * $orderQuantity;
    $now = date('Y-m-d H:i:s');

    // ORDER_FLAG = 1 → 売上
    $stmt2 = $PDO->prepare(
        "INSERT INTO `ORDER` 
        (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
         VALUES (:dt, :cid, 1, :price, :eid)"
    );

    $stmt2->bindParam(':dt', $now);
    $stmt2->bindParam(':cid', $customerId, PDO::PARAM_INT);
    $stmt2->bindParam(':price', $total, PDO::PARAM_INT);
    $stmt2->bindParam(':eid', $employeeId, PDO::PARAM_INT);
    $stmt2->execute();

    $orderId = $PDO->lastInsertId();
    $PDO->commit();

    echo json_encode(['success' => true, 'message' => "売上が登録されました（ID: {$orderId}）"]);

} catch (Exception $e) {
    if ($PDO->inTransaction()) $PDO->rollBack();
    echo json_encode(['success' => false, 'message' => 'エラー: ' . $e->getMessage()]);
}

?>
