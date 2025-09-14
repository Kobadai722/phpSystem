<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

try {
    // 全従業員の勤怠履歴を結合して取得
    $stmt = $PDO->prepare("
        SELECT 
            E.EMPLOYEE_ID, 
            E.EMPLOYEE_NAME, 
            A.ATTENDANCE_DATE AS date, 
            A.CLOCK_IN_TIME AS clock_in_time, 
            A.CLOCK_OUT_TIME AS clock_out_time
        FROM 
            EMPLOYEE AS E
        LEFT JOIN 
            ATTENDANCE AS A ON E.EMPLOYEE_ID = A.EMPLOYEE_ID
        ORDER BY 
            E.EMPLOYEE_ID, A.ATTENDANCE_DATE DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'history' => $records]);
} catch (PDOException $e) {
    error_log("Error fetching all attendance history: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '全勤怠履歴の取得中にデータベースエラーが発生しました。']);
}