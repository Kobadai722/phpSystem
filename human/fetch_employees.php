<?php
require_once '../config.php';

header('Content-Type: application/json; charset=UTF-8');

$name_keyword = $_GET['name_keyword'] ?? null;
$id_keyword = $_GET['id_keyword'] ?? null;
$division_id = $_GET['division_id'] ?? null;

// ▼▼▼ 修正: IS_DELETED を取得カラムに追加 ▼▼▼
$sql_query = "SELECT e.EMPLOYEE_ID, e.NAME, d.DIVISION_NAME, j.JOB_POSITION_NAME, e.JOINING_DATE, e.EMERGENCY_CELL_NUMBER, e.IS_DELETED
            FROM EMPLOYEE e
            LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
            LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID";
// ▲▲▲ 修正ここまで ▲▲▲

$conditions = [];
$params = [];

if (!empty($name_keyword)) {
    $conditions[] = "e.NAME LIKE ?";
    $params[] = '%' . $name_keyword . '%';
}
if (!empty($id_keyword)) {
    $conditions[] = "e.EMPLOYEE_ID LIKE ?";
    $params[] = '%' . $id_keyword . '%';
}
if (!empty($division_id)) {
    $conditions[] = "e.DIVISION_ID = ?";
    $params[] = $division_id;
}

if (!empty($conditions)) {
    $sql_query .= " WHERE " . implode(" AND ", $conditions);
}

$sql_query .= " ORDER BY e.EMPLOYEE_ID ASC";

try {
    $stmt = $PDO->prepare($sql_query);
    $stmt->execute($params);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $employees]);
} catch (PDOException $e) {
    error_log("Error fetching employees: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'データベースエラーが発生しました。']);
}
?>