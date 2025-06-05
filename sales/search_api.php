<?php
require_once '../config.php';

header('Content-Type: application/json');

$keyword = $_POST['keyword'] ?? '';

$sql = "SELECT P.PRODUCT_ID, P.PRODUCT_NAME, P.UNIT_SELLING_PRICE, S.STOCK_QUANTITY, K.PRODUCT_KUBUN_NAME
        FROM PRODUCT P
        LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
        LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID";

if (!empty($keyword)) {
    $sql .= " WHERE P.PRODUCT_ID LIKE :keyword OR P.PRODUCT_NAME LIKE :keyword";
}

$stmt = $PDO->prepare($sql);
if (!empty($keyword)) {
    $stmt->bindValue(':keyword', '%' . $keyword . '%');
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// JSONで返す
echo json_encode($results);

