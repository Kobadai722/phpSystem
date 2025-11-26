<?php
session_start();
require_once '../config.php';


if ($application_id && in_array($status, ['approved', 'rejected'])) {
    try {
        $PDO->beginTransaction(); 

        // 1. 申請内容を取得
        $stmt_app = $PDO->prepare("SELECT * FROM APPLICATIONS WHERE APPLICATION_ID = ?");
        $stmt_app->execute([$application_id]);
        $app = $stmt_app->fetch(PDO::FETCH_ASSOC);

        if (!$app) {
            throw new Exception("申請が見つかりません。");
        }

        // --- 有給休暇の承認時の処理 ---
        if ($status === 'approved' && $app['APPLICATION_TYPE'] === 'paid_leave') {
            $emp_id = $app['EMPLOYEE_ID'];
            $today = date('Y-m-d');

            // 有効期限が古い順に、残日数がある付与データを取得
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
                // 最も古い有効な付与データから1日消化
                $stmt_update_leave = $PDO->prepare("UPDATE PAID_LEAVES SET DAYS_USED = DAYS_USED + 1 WHERE PAID_LEAVE_ID = ?");
                $stmt_update_leave->execute([$grant['PAID_LEAVE_ID']]);
            } else {
                // 残日数がない場合
                throw new Exception("有給残日数が足りません。");
            }
        }

        // 2. ステータスを更新
        $stmt = $PDO->prepare("UPDATE APPLICATIONS SET STATUS = ? WHERE APPLICATION_ID = ?");
        $stmt->execute([$status, $application_id]);
        
        $PDO->commit();

        $msg = ($status === 'approved') ? '承認' : '却下';
        $_SESSION['success_message'] = "申請ID: {$application_id} を{$msg}しました。";
        
    } catch (Exception $e) { 
        $PDO->rollBack();
        error_log("Status update error: " . $e->getMessage());
        $_SESSION['error_message'] = "エラー: " . $e->getMessage();
    }
}

header('Location: application_list.php');
exit;
?>