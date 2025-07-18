<?php
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
error_reporting(E_ALL);

session_start();
require_once '../config.php';

// デバッグメッセージを格納するセッション配列を初期化
if (!isset($_SESSION['debug_messages'])) {
    $_SESSION['debug_messages'] = [];
}
$_SESSION['debug_messages'][] = "human-delete.php accessed.";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $_SESSION['debug_messages'][] = "Received employee_id: " . $employee_id;

    try {
        $PDO->beginTransaction();

        // 削除対象の社員が存在するかどうかを確認するSQL
        $check_existence_sql = "SELECT EMPLOYEE_ID FROM EMPLOYEE WHERE EMPLOYEE_ID = ?";
        $check_existence_stmt = $PDO->prepare($check_existence_sql);
        $check_existence_stmt->execute([$employee_id]);
        $employee_exists = $check_existence_stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['debug_messages'][] = "Employee existence check from DB: " . print_r($employee_exists, true);

        if (!$employee_exists) {
            // 社員が存在しない場合
            $_SESSION['error_message'] = "社員情報の削除に失敗しました。社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " が見つかりません。";
        } else {
            // 物理削除を行うSQL
            $delete_sql = "DELETE FROM EMPLOYEE WHERE EMPLOYEE_ID = ?";
            $delete_stmt = $PDO->prepare($delete_sql);
            $delete_stmt->execute([$employee_id]);

            if ($delete_stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "社員ID: " . htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') . " を物理削除しました。";
            } else {
                $_SESSION['error_message'] = "社員情報の削除に失敗しました。データベースの更新に問題が発生しました。";
            }
        }
        $PDO->commit();
    } catch (PDOException $e) {
        $PDO->rollBack();
        $_SESSION['debug_messages'][] = "Employee physical deletion error: " . $e->getMessage();
        $_SESSION['error_message'] = "データベースエラーにより、社員情報の削除に失敗しました。システム管理者にお問い合わせください。";
    }

    header("Location: editer.php");
    exit;
} else {
    $_SESSION['error_message'] = "不正なリクエストです。";
    header("Location: editer.php");
    exit;
}
?>