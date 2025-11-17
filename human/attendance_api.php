<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

$employee_id = $_SESSION['employee_id'];
$action = $_POST['action'] ?? '';
$date = date("Y-m-d");
$time = date("H:i:s");

if ($action === 'clockIn') {
    try {
        // 今日の出勤記録をチェック
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => '本日はすでに出勤済みです。']);
            exit;
        }

        // 新しい出勤記録を挿入
        $stmt = $PDO->prepare("INSERT INTO ATTENDANCE (EMPLOYEE_ID, ATTENDANCE_DATE, CLOCK_IN_TIME) VALUES (?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time]);

        echo json_encode(['success' => true, 'message' => '出勤しました。', 'clockInTime' => $time]);
    } catch (PDOException $e) {
        error_log("出勤処理エラー: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '出勤処理中にエラーが発生しました。']);
    }

} elseif ($action === 'clockOut') {
    try {
        // 今日の出勤記録を取得
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID, CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            echo json_encode(['success' => false, 'message' => '出勤記録がありません。']);
            exit;
        }
        if ($record['CLOCK_OUT_TIME']) {
            echo json_encode(['success' => false, 'message' => '本日はすでに退勤済みです。']);
            exit;
        }

        // 退勤時刻を更新
        $stmt = $PDO->prepare("UPDATE ATTENDANCE SET CLOCK_OUT_TIME = ? WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$time, $employee_id, $date]);

        echo json_encode(['success' => true, 'message' => '退勤しました。', 'clockOutTime' => $time]);
    } catch (PDOException $e) {
        error_log("退勤処理エラー: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '退勤処理中にエラーが発生しました。']);
    }

} elseif ($action === 'getHistory') {
    try {
        // 勤怠履歴を取得
        $stmt = $PDO->prepare("SELECT ATTENDANCE_DATE, CLOCK_IN_TIME, CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? ORDER BY ATTENDANCE_DATE DESC");
        $stmt->execute([$employee_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'history' => $history]);
    } catch (PDOException $e) {
        error_log("履歴取得エラー: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '履歴の取得中にエラーが発生しました。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '無効なアクションです。']);
}
?>