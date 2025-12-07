<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';
header('Content-Type: application/json');

$keyword = $_POST['keyword'] ?? '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // ▼ WHERE 条件作成
    $where = "";
    $params = [];

    if ($keyword !== '') {
        $where = "WHERE P.PRODUCT_ID LIKE :keyword OR P.PRODUCT_NAME LIKE :keyword";
        $params[':keyword'] = "%{$keyword}%";
    }

    // ▼ 合計件数
    $countSql = "SELECT COUNT(*) FROM PRODUCT P $where";
    $countStmt = $PDO->prepare($countSql);
    foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();

    // ▼ データ取得（LIMIT付き）
    $sql = "
        SELECT P.PRODUCT_ID, P.PRODUCT_NAME, P.UNIT_SELLING_PRICE,
                S.STOCK_QUANTITY, K.PRODUCT_KUBUN_NAME
        FROM PRODUCT P
        LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
        LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID
        $where
        ORDER BY P.PRODUCT_ID
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $PDO->prepare($sql);

    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $results,
        "total" => $total,
        "page" => $page,
        "limit" => $limit
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
