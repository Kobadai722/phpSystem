<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

try {
    $stmt = $PDO->prepare("SELECT ATTENDANCE_DATE, ATTENDANCE_TIME AS CLOCK_IN_TIME, LEAVE_TIME AS CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? ORDER BY ATTENDANCE_DATE DESC");
    $stmt->execute([$employee_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'history' => $history]);
} catch (PDOException $e) {
    // デバッグ情報として、より詳細なエラーを返すように変更
    error_log("Error fetching all attendance history: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。システム管理者に連絡してください。',
        'debug' => $e->getMessage()
    ]);
}
?>