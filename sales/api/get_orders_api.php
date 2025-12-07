<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';
header('Content-Type: application/json');

try {
    // --- パラメータ受取 ---
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $orderId = $_GET['orderId'] ?? '';
    $customerName = $_GET['customerName'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';

    // --- ベースSQL ---
    $baseSql = "FROM S_ORDER o 
                LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID";

    // --- WHERE句 ---
    $where = [];
    $params = [];

    if ($orderId !== '') {
        $where[] = "o.ORDER_ID = :orderId";
        $params[':orderId'] = $orderId;
    }
    if ($customerName !== '') {
        $where[] = "c.NAME LIKE :customerName";
        $params[':customerName'] = "%{$customerName}%";
    }
    if ($paymentStatus !== '') {
        $where[] = "o.STATUS = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }

    $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    // --- 件数取得 ---
    $countSql = "SELECT COUNT(*) " . $baseSql . " " . $whereSql;
    $countStmt = $PDO->prepare($countSql);
    foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
    $countStmt->execute();
    $totalRows = (int)$countStmt->fetchColumn();

    // --- ページデータ取得 ---
    $dataSql = "SELECT 
                    o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, 
                    o.STATUS, c.NAME AS CUSTOMER_NAME 
                " . $baseSql . " 
                " . $whereSql . " 
                ORDER BY o.ORDER_DATETIME DESC
                LIMIT :limit OFFSET :offset";

    $stmt = $PDO->prepare($dataSql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- JSON返却 ---
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'total' => $totalRows,
        'page' => $page,
        'limit' => $limit,
        'totalPages' => ceil($totalRows / $limit)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error_message' => 'データ取得エラー: ' . $e->getMessage()
    ]);
}
?>
