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
    // ORDER BY句で日付と従業員IDをソートすることで、見やすい表になります
    $stmt = $PDO->prepare("
        SELECT 
            E.EMPLOYEE_ID AS employee_id, 
            E.NAME AS employee_name, 
            A.ATTENDANCE_DATE AS date, 
            A.CLOCK_IN_TIME AS clock_in_time, 
            A.CLOCK_OUT_TIME AS clock_out_time
        FROM 
            EMPLOYEE AS E
        LEFT JOIN 
            ATTENDANCE AS A ON E.EMPLOYEE_ID = A.EMPLOYEE_ID
        ORDER BY 
            A.ATTENDANCE_DATE DESC, E.EMPLOYEE_ID
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'history' => $records]);
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