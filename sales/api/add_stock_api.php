<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

$productId = $_POST['product_id'] ?? null;
$addQty = $_POST['add_qty'] ?? 0;

if (!$productId || $addQty <= 0) {
    echo json_encode(["success" => false, "message" => "invalid"]);
    exit;
}

$sql = "
    INSERT INTO STOCK (PRODUCT_ID, stock_quantity, LAST_UPDATING_TIME)
    VALUES (:productId, :addQty, NOW())
    ON DUPLICATE KEY UPDATE
        stock_quantity = stock_quantity + VALUES(stock_quantity),
        LAST_UPDATING_TIME = NOW()
";

$stmt = $PDO->prepare($sql);
$stmt->bindValue(":productId", $productId, PDO::PARAM_INT);
$stmt->bindValue(":addQty", $addQty, PDO::PARAM_INT);

$success = $stmt->execute();
$rows = $stmt->rowCount();

echo json_encode(["success" => $success, "rows" => $rows]);
