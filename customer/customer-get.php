<?php
require_once '../config.php'; // DBサーバーと接続 (パスを修正)

header('Content-Type: application/json; charset=UTF-8');

// 検索キーワードの受け取り
$search_id = $_GET['customer_id'] ?? '';
$search_name = $_GET['name'] ?? '';
$search_tel = $_GET['cell_number'] ?? '';
$search_mail = $_GET['mail'] ?? '';

// ベースとなるSQL
$sql = "SELECT * FROM CUSTOMER WHERE 1=1";
$params = [];

// 検索条件の組み立て
if (!empty($search_id)) {
    // 顧客IDは完全一致で検索
    $sql .= " AND CUSTOMER_ID = ?";
    $params[] = $search_id;
}
if (!empty($search_name)) {
    $sql .= " AND NAME LIKE ?";
    $params[] = '%' . $search_name . '%';
}
if (!empty($search_tel)) {
    $sql .= " AND CELL_NUMBER LIKE ?";
    $params[] = '%' . $search_tel . '%';
}
if (!empty($search_mail)) {
    $sql .= " AND MAIL LIKE ?";
    $params[] = '%' . $search_mail . '%';
}

try {
    $stmt = $PDO->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $customers]);
} catch (PDOException $e) {
    error_log("Error fetching customers: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'データベースエラーが発生しました。']);
}
?>