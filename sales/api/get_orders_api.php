<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';
header('Content-Type: application/json');

try {
    // 受け取り
    $orderId = $_GET['orderId'] ?? '';
    $customerName = $_GET['customerName'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';

    // ★ページネーション追加
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // ベースクエリ
    $sql = "SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS,
                    c.NAME AS CUSTOMER_NAME
            FROM S_ORDER o
            LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID";

    $where = [];
    $params = [];

    if ($orderId !== '') {
        $where[] = "o.ORDER_ID = :orderId";
        $params[':orderId'] = $orderId;
    }

    if ($customerName !== '') {
        $where[] = "c.NAME LIKE :customerName";
        $params[':customerName'] = "%$customerName%";
    }

    if ($paymentStatus !== '') {
        $where[] = "o.STATUS = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // 並び順
    $sql .= " ORDER BY o.ORDER_DATETIME DESC";

    // 総件数取得用
    $countSql = "SELECT COUNT(*) FROM ($sql) AS total_table";
    $stmtCount = $PDO->prepare($countSql);

    foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
    $stmtCount->execute();
    $totalRows = (int)$stmtCount->fetchColumn();

    // ページネーション付きの本クエリ
    $sql .= " LIMIT :limit OFFSET :offset";
    $stmt = $PDO->prepare($sql);

    // 検索パラメータ
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);

    // ページネーションパラメータ
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
    'success' => true,
    'data' => $orders,
    'total' => $totalRows,
    'page' => $page,
    'limit' => $limit,
    'totalPages' => ceil($totalRows / $limit)
]);


} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
