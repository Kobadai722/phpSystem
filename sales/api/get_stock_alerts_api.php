<?php
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

$productId = $_POST['product_id'] ?? null;
$addQty = $_POST['add_qty'] ?? 0;

if (!$productId || $addQty <= 0) {
    echo json_encode(["success" => false, "message" => "invalid"]);
    exit;
}

// 在庫更新
$sql = "
    UPDATE STOCK
    SET stock_quantity = stock_quantity + :addQty
    WHERE PRODUCT_ID = :productId
";

$stmt = $PDO->prepare($sql);
$stmt->bindValue(":addQty", $addQty, PDO::PARAM_INT);
$stmt->bindValue(":productId", $productId, PDO::PARAM_INT);

$success = $stmt->execute();

echo json_encode(["success" => $success]);
