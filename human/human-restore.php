<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    // ... (既存のバリデーションと処理ロジック) ...

    try {
        $PDO->beginTransaction();
        $sql = "UPDATE EMPLOYEE SET IS_DELETED = FALSE WHERE EMPLOYEE_ID = ?";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$employee_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " の情報を復元しました。";
        } else {
            $_SESSION['error_message'] = "社員情報の復元に失敗しました。該当する社員が見つからないか、既に復元されている可能性があります。";
        }
        $PDO->commit();
    } catch (PDOException $e) {
        $PDO->rollBack();
        error_log("Employee restoration error: " . $e->getMessage());
        $_SESSION['error_message'] = "データベースエラーにより、社員情報の復元に失敗しました。システム管理者にお問い合わせください。";
    }

    // リダイレクト先を detail_edit.php に変更
    header("Location: detail_edit.php?id=" . urlencode($employee_id));
    exit;
} else {
    $_SESSION['error_message'] = "不正なリクエストです。";
    header("Location: editer.php"); // 不正なリクエストの場合は編集者一覧に戻る
    exit;
}
?>