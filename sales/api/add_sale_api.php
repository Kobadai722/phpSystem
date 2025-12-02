<?php
header("Content-Type: application/json; charset=utf-8");
require_once '../../config.php';

try {
    // パラメータ取得
    $productId   = $_POST['product_id']        ?? null;
    $quantity    = $_POST['order_quantity']    ?? null;
    $customerId  = $_POST['customer_id']       ?? null;
    $employeeId  = $_POST['employee_id']       ?? null;
    $orderFlag   = $_POST['order_flag']        ?? null;
    $totalPrice  = $_POST['total_price']       ?? null;
    $notes       = $_POST['notes']             ?? null;

    // 入力チェック
    if (!$productId || !$quantity || !$customerId || !$employeeId || !$orderFlag) {
        echo json_encode([
            "success" => false,
            "message" => "必須項目が入力されていません。"
        ]);
        exit;
    }

    // INSERT SQL
    $sql = "
        INSERT INTO `ORDER`
        (PURCHASE_ORDER_DATE, PRODUCT_ID, QUANTITY, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID, NOTES)
        VALUES
        (NOW(), ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([
        $productId,
        $quantity,
        $customerId,
        $orderFlag,
        $totalPrice,
        $employeeId,
        $notes
    ]);

    if (!$result) {
        $error = $stmt->errorInfo();
        echo json_encode([
            "success" => false,
            "message" => "SQLエラー: " . $error[2]
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "登録成功"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "例外エラー: " . $e->getMessage()
    ]);
    exit;
}
