<?php

require_once '../../config.php';

header('Content-Type: application/json');

$keyword = $_POST['keyword'] ?? '';

$sql = "SELECT P.PRODUCT_ID, P.PRODUCT_NAME, P.UNIT_SELLING_PRICE, S.STOCK_QUANTITY, K.PRODUCT_KUBUN_NAME, P.DESCRIPTION
        FROM PRODUCT P
        LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
        LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID"; // ★変更：P.DESCRIPTIONを追加

// キーワードが入力されている場合、WHERE句を追加して絞り込み
if (!empty($keyword)) {
    $sql .= " WHERE P.PRODUCT_ID LIKE :keyword OR P.PRODUCT_NAME LIKE :keyword OR P.DESCRIPTION LIKE :keyword_desc"; // ★変更：P.DESCRIPTIONを検索対象に追加
}

$stmt = $PDO->prepare($sql);

if (!empty($keyword)) {
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->bindValue(':keyword_desc', '%' . $keyword . '%', PDO::PARAM_STR); // ★追加：description用バインド
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);

?>