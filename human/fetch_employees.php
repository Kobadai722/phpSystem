<?php
require_once '../config.php'; // DBサーバーと接続

header('Content-Type: application/json; charset=UTF-8');

// 検索キーワードの受け取り
$name_keyword = $_GET['name_keyword'] ?? null;
$id_keyword = $_GET['id_keyword'] ?? null;
$division_id = $_GET['division_id'] ?? null;
$include_deleted = $_GET['include_deleted'] ?? 'false'; // 新しく追加

// ベースとなるSQLクエリ
$sql_query = "SELECT e.EMPLOYEE_ID, e.NAME, d.DIVISION_NAME, j.JOB_POSITION_NAME, e.JOINING_DATE, e.EMERGENCY_CELL_NUMBER, e.IS_DELETED
            FROM EMPLOYEE e
            LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
            LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID";

$conditions = [];
$params = [];

// 氏名での検索条件
if (!empty($name_keyword)) {
    $conditions[] = "e.NAME LIKE ?";
    $params[] = '%' . $name_keyword . '%';
}

// 従業員番号での検索条件
if (!empty($id_keyword)) {
    $conditions[] = "e.EMPLOYEE_ID LIKE ?";
    $params[] = '%' . $id_keyword . '%';
}

// 部署での絞り込み条件
if (!empty($division_id)) {
    $conditions[] = "e.DIVISION_ID = ?";
    $params[] = $division_id;
}

// 削除済み従業員の表示/非表示を制御
if ($include_deleted === 'false') {
    $conditions[] = "e.IS_DELETED = 0";
}

// 検索条件が存在する場合、WHERE句をSQLに追加
if (!empty($conditions)) {
    $sql_query .= " WHERE " . implode(" AND ", $conditions);
}

$sql_query .= " ORDER BY e.EMPLOYEE_ID ASC"; // 結果をソート

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