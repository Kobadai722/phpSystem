<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    // ... (既存のバリデーションと処理ロジック) ...

    try {
        $PDO->beginTransaction();
        $sql = "UPDATE EMPLOYEE SET IS_DELETED = TRUE WHERE EMPLOYEE_ID = ?";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$employee_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " を論理削除しました。(復元可能です)";
        } else {
            $_SESSION['error_message'] = "社員情報の削除に失敗しました。該当する社員が見つからないか、既に削除されている可能性があります。";
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