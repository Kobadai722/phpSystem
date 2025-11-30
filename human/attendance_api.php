<?php
// すべての出力をバッファリング（一時保存）開始
// これにより、config.php等の読み込み時に発生した空白やエラー出力を後で消去できます
ob_start();

// エラー設定
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// データベース接続
// output_siwakehyo.php等の階層を見ると、config.phpは一つ上の階層にあるようです
// ファイルの配置場所に応じてパスが正しいか確認してください
require_once '../config.php';

// JSONヘッダー
header('Content-Type: application/json; charset=utf-8');

// 出力用配列の初期化
$response = [];

try {
    // $PDOの確認
    global $PDO;
    if (!isset($PDO)) {
        throw new Exception('データベース接続エラー: config.phpを確認してください。');
    }

    // ログイン確認
    if (!isset($_SESSION['employee_id'])) {
        throw new Exception('ログインが必要です。');
    }

    $employee_id = $_SESSION['employee_id'];
    
    // アクションの取得 (GET/POST両対応)
    $action = $_REQUEST['action'] ?? '';

    $date = date("Y-m-d");
    $time = date("H:i:s");

    if ($action === 'clockIn') {
        // 出勤処理
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        if ($stmt->fetch()) {
            echo_json(['success' => false, 'message' => '本日はすでに出勤済みです。']);
            exit;
        }

        $stmt = $PDO->prepare("INSERT INTO ATTENDANCE (EMPLOYEE_ID, ATTENDANCE_DATE, ATTENDANCE_TIME) VALUES (?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time]);

        echo_json(['success' => true, 'message' => '出勤しました。', 'clockInTime' => $time]);

    } elseif ($action === 'clockOut') {
        // 退勤処理
        $stmt = $PDO->prepare("SELECT ATTENDANCE_ID, LEAVE_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$employee_id, $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            echo_json(['success' => false, 'message' => '出勤記録がありません。']);
            exit;
        }
        if ($record['LEAVE_TIME']) {
            echo_json(['success' => false, 'message' => '本日はすでに退勤済みです。']);
            exit;
        }

        $stmt = $PDO->prepare("UPDATE ATTENDANCE SET LEAVE_TIME = ? WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
        $stmt->execute([$time, $employee_id, $date]);

        echo_json(['success' => true, 'message' => '退勤しました。', 'clockOutTime' => $time]);

    } elseif ($action === 'getHistory') {
        // 履歴取得
        $stmt = $PDO->prepare("SELECT ATTENDANCE_DATE, ATTENDANCE_TIME AS CLOCK_IN_TIME, LEAVE_TIME AS CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? ORDER BY ATTENDANCE_DATE DESC");
        $stmt->execute([$employee_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo_json(['success' => true, 'history' => $history]);

    } else {
        throw new Exception('無効なアクションです。(' . htmlspecialchars($action) . ')');
    }

} catch (PDOException $e) {
    echo_json(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo_json(['success' => false, 'message' => 'システムエラー: ' . $e->getMessage()]);
}

/**
 * JSONを出力して終了する関数
 * 余計な出力をクリアしてからJSONのみを出力します
 */
function echo_json($data) {
    // バッファをクリア（これまでに出力されたHTMLや空白を消去）
    ob_clean(); 
    echo json_encode($data);
    exit;
}
?>