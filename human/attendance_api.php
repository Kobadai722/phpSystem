<?php
// エラーが出ても画面にHTMLを出力せず、JSON形式を保つための設定
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

// $PDOがconfig.phpで定義されているか確認
global $PDO;
if (!isset($PDO)) {
    echo json_encode(['success' => false, 'message' => 'データベース接続エラー: config.phpを確認してください。']);
    exit;
}

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

$employee_id = $_SESSION['employee_id'];

// ▼▼▼ 修正: $_POST から $_REQUEST に変更 (GETリクエストに対応) ▼▼▼
$action = $_REQUEST['action'] ?? '';
// ▲▲▲ 修正ここまで ▲▲▲

$date = date("Y-m-d");
$time = date("H:i:s");

try {
    if ($action === 'clockIn') {
        // 今日の出勤記録をチェック
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => '本日はすでに出勤済みです。']);
            exit;
        }

        // 新しい出勤記録を挿入
        $stmt = $PDO->prepare("INSERT INTO ATTENDANCE (EMPLOYEE_ID, ATTENDANCE_DATE, ATTENDANCE_TIME) VALUES (?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time]);

        echo json_encode(['success' => true, 'message' => '出勤しました。', 'clockInTime' => $time]);

    } elseif ($action === 'clockOut') {
        // 今日の出勤記録を取得
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID, LEAVE_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            echo json_encode(['success' => false, 'message' => '出勤記録がありません。']);
            exit;
        }
        // 取得したレコードでLEAVE_TIMEがNULLではない場合
        if ($record['LEAVE_TIME']) {
            echo json_encode(['success' => false, 'message' => '本日はすでに退勤済みです。']);
            exit;
        }

        // 退勤時刻を更新
        $stmt = $PDO->prepare("UPDATE ATTENDANCE SET LEAVE_TIME = ? WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$time, $employee_id, $date]);

        echo json_encode(['success' => true, 'message' => '退勤しました。', 'clockOutTime' => $time]);

    } elseif ($action === 'getHistory') {
        // 勤怠履歴を取得
        $stmt = $PDO->prepare("SELECT ATTENDANCE_DATE, ATTENDANCE_TIME AS CLOCK_IN_TIME, LEAVE_TIME AS CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? ORDER BY ATTENDANCE_DATE DESC");
        $stmt->execute([$employee_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'history' => $history]);

    } else {
        echo json_encode(['success' => false, 'message' => '無効なアクションです。']);
    }

} catch (PDOException $e) {
    // JSON形式でエラーを返す
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}
?>