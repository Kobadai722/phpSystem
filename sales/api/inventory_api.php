<?php

require_once '../../config.php';

header('Content-Type: application/json');

$sql = "SELECT P.PRODUCT_ID, P.PRODUCT_NAME, P.UNIT_SELLING_PRICE, S.STOCK_QUANTITY, K.PRODUCT_KUBUN_NAME, P.DESCRIPTION 
        FROM PRODUCT P
        LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
        LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID"; // 変更：P.DESCRIPTIONを追加

$stmt = $PDO->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);

?>