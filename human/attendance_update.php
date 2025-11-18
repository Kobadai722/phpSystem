<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: editer.php');
    exit;
}

$action = $_POST['action'] ?? '';
$attendance_id = $_POST['attendance_id'] ?? '';

if (empty($attendance_id)) {
    $_SESSION['error_message'] = "不正なリクエストです。";
    header('Location: editer.php');
    exit;
}

try {
    if ($action === 'delete') {
        // 削除処理
        $stmt = $PDO->prepare("DELETE FROM ATTENDANCE WHERE ATTENDANCE_ID = ?");
        $stmt->execute([$attendance_id]);
        $_SESSION['success_message'] = "勤怠記録を削除しました。";

    } elseif ($action === 'update') {
        // 更新処理
        $attendance_time = !empty($_POST['attendance_time']) ? $_POST['attendance_time'] : null;
        $leave_time = !empty($_POST['leave_time']) ? $_POST['leave_time'] : null;

        // ステータスの自動判定（簡易的）
        $status = null;
        if ($attendance_time && !$leave_time) {
            $status = '勤務中'; // STATUSカラムがある場合
        } elseif ($attendance_time && $leave_time) {
            $status = '退勤済'; // STATUSカラムがある場合
        }

        // SQL: STATUSカラムがある前提で記述しています。なければSTATUS部分は削除してください。
        $sql = "UPDATE ATTENDANCE SET ATTENDANCE_TIME = ?, LEAVE_TIME = ?, STATUS = ? WHERE ATTENDANCE_ID = ?";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$attendance_time, $leave_time, $status, $attendance_id]);
        
        $_SESSION['success_message'] = "勤怠記録を修正しました。";
    }

} catch (PDOException $e) {
    error_log("Attendance update error: " . $e->getMessage());
    $_SESSION['error_message'] = "データベースエラーが発生しました。";
}

header('Location: editer.php');
exit;
?>