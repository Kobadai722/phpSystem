<?php
session_start();
require_once '../config.php';

// 1. POSTリクエストか確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = '不正なアクセスです(GET)。';
    header('Location: application_list.php');
    exit;
}

// 2. データの受け取り
$application_id = $_POST['application_id'] ?? '';
$status = $_POST['status'] ?? '';

// 3. データの検証（ここが原因の可能性が高いです）
if (empty($application_id) || !in_array($status, ['approved', 'rejected'])) {
    // エラーの詳細をメッセージに入れる
    $_SESSION['error_message'] = "不正なリクエストです。(ID: {$application_id}, Status: {$status})";
    header('Location: application_list.php');
    exit;
}

try {
    $PDO->beginTransaction(); // トランザクション開始

    // 4. 申請データの取得（存在確認）
    $stmt_app = $PDO->prepare("SELECT * FROM APPLICATIONS WHERE APPLICATION_ID = ?");
    $stmt_app->execute([$application_id]);
    $app = $stmt_app->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        throw new Exception("指定された申請IDが見つかりません。");
    }

    // --- ▼▼▼ 有給休暇の承認時の処理 ▼▼▼ ---
    if ($status === 'approved' && $app['APPLICATION_TYPE'] === 'paid_leave') {
        $emp_id = $app['EMPLOYEE_ID'];
        $today = date('Y-m-d');

        // 残日数がある付与データを取得
        $stmt_grant = $PDO->prepare("
            SELECT PAID_LEAVE_ID, (DAYS_GRANTED - DAYS_USED) as REMAINING 
            FROM PAID_LEAVES 
            WHERE EMPLOYEE_ID = ? AND EXPIRATION_DATE >= ? AND (DAYS_GRANTED - DAYS_USED) > 0
            ORDER BY EXPIRATION_DATE ASC
            FOR UPDATE
        ");
        $stmt_grant->execute([$emp_id, $today]);
        $grant = $stmt_grant->fetch(PDO::FETCH_ASSOC);

        if ($grant) {
            // 1日消化
            $stmt_update_leave = $PDO->prepare("UPDATE PAID_LEAVES SET DAYS_USED = DAYS_USED + 1 WHERE PAID_LEAVE_ID = ?");
            $stmt_update_leave->execute([$grant['PAID_LEAVE_ID']]);
        } else {
            throw new Exception("有効な有給休暇の残日数がありません。");
        }
    }
    // --- ▲▲▲ 追加ここまで ▲▲▲ ---

    // 5. ステータスの更新
    $stmt = $PDO->prepare("UPDATE APPLICATIONS SET STATUS = ? WHERE APPLICATION_ID = ?");
    $stmt->execute([$status, $application_id]);
    
    $PDO->commit(); // コミット

    $msg = ($status === 'approved') ? '承認' : '却下';
    $_SESSION['success_message'] = "申請ID: {$application_id} を{$msg}しました。";
    
} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("Status update error: " . $e->getMessage());
    $_SESSION['error_message'] = "処理エラー: " . $e->getMessage();
}

header('Location: application_list.php');
exit;
?>