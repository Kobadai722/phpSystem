<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    try {
        $PDO->beginTransaction();

        // 1. 社員が存在するか、および削除済みかどうかを確認
        $check_sql = "SELECT IS_DELETED FROM EMPLOYEE WHERE EMPLOYEE_ID = ?";
        $check_stmt = $PDO->prepare($check_sql);
        $check_stmt->execute([$employee_id]);
        $employee_status = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee_status) {
            // 社員が見つからない場合
            $_SESSION['error_message'] = "社員情報の削除に失敗しました。社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " が見つかりません。";
        } elseif ($employee_status['IS_DELETED'] == TRUE) {
            // 既に削除済みの場合
            $_SESSION['error_message'] = "社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " は既に削除済みです。";
        } else {
            // 社員が存在し、まだ削除されていない場合のみ論理削除を実行
            $update_sql = "UPDATE EMPLOYEE SET IS_DELETED = TRUE WHERE EMPLOYEE_ID = ?";
            $update_stmt = $PDO->prepare($update_sql);
            $update_stmt->execute([$employee_id]);

            if ($update_stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " を論理削除しました。(復元可能です)";
            } else {
                // ここに到達することはほとんどないはずですが、念のため
                $_SESSION['error_message'] = "社員情報の削除に失敗しました。データベースの更新に問題が発生しました。";
            }
        }
        $PDO->commit();
    } catch (PDOException $e) {
        $PDO->rollBack();
        error_log("Employee logical deletion error: " . $e->getMessage());
        $_SESSION['error_message'] = "データベースエラーにより、社員情報の削除に失敗しました。システム管理者にお問い合わせください。";
    }

    // リダイレクト先を editer.php に変更
    header("Location: editer.php");
    exit;
} else {
    $_SESSION['error_message'] = "不正なリクエストです。";
    header("Location: editer.php");
    exit;
}
?>