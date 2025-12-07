<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// 必須パラメータ確認
$productId = $_POST['product_id'] ?? null;
$addQty = $_POST['add_qty'] ?? 0;

if (!$productId || $addQty <= 0) {
    echo json_encode(["success" => false, "message" => "invalid params"]);
    exit;
}

try {
    // 在庫更新SQL
    $sql = "
        UPDATE STOCK
        SET STOCK_QUANTITY = STOCK_QUANTITY + :addQty,
            LAST_UPDATING_TIME = NOW()
        WHERE PRODUCT_ID = :productId
    ";

    $stmt = $PDO->prepare($sql);
    $stmt->bindValue(":addQty", $addQty, PDO::PARAM_INT);
    $stmt->bindValue(":productId", $productId, PDO::PARAM_INT);

    $success = $stmt->execute();

    echo json_encode(["success" => $success]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
