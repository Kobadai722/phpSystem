<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    try {
        $PDO->beginTransaction();

        // 削除対象の存在確認
        $check_stmt = $PDO->prepare("SELECT EMPLOYEE_ID FROM EMPLOYEE WHERE EMPLOYEE_ID = ?");
        $check_stmt->execute([$employee_id]);
        
        if (!$check_stmt->fetch()) {
            $_SESSION['error_message'] = "エラー: 指定された社員データが見つかりません。";
        } else {
            // ▼▼▼ 修正箇所: 物理削除(DELETE) から 論理削除(UPDATE) に変更 ▼▼▼
            $sql = "UPDATE EMPLOYEE SET IS_DELETED = TRUE WHERE EMPLOYEE_ID = ?";
            $stmt = $PDO->prepare($sql);
            $stmt->execute([$employee_id]);
            
            $_SESSION['success_message'] = "社員ID: {$employee_id} を削除しました。（論理削除）";
            // ▲▲▲ 修正ここまで ▲▲▲
        }
        
        $PDO->commit();
    } catch (PDOException $e) {
        $PDO->rollBack();
        error_log("Logical delete error: " . $e->getMessage());
        $_SESSION['error_message'] = "データベースエラーが発生しました。";
    }

    header("Location: editer.php");
    exit;
} else {
    header("Location: editer.php");
    exit;
}
?>