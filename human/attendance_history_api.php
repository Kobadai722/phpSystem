<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

$employee_id = $_SESSION['employee_id'];

try {
    $stmt = $PDO->prepare("SELECT ATTENDANCE_DATE, STATUS, TIMESTAMP FROM ATTENDANCE WHERE EMPLOYEE_ID = ? ORDER BY TIMESTAMP DESC");
    $stmt->execute([$employee_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $history = [];
    foreach ($records as $record) {
        $history[] = [
            'date' => $record['ATTENDANCE_DATE'],
            'status' => $record['STATUS'],
            'timestamp' => $record['TIMESTAMP']
        ];
    }
    
    echo json_encode(['success' => true, 'history' => $history]);
} catch (PDOException $e) {
    error_log("Error fetching attendance history: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '履歴の取得中にデータベースエラーが発生しました。']);
}
