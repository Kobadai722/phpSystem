<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: main.php');
    exit;
}

// 入力値の取得
$employee_id = $_SESSION['employee_id'] ?? null; // セッションから取得（改ざん防止）
$type = $_POST['type'] ?? '';
$target_date = $_POST['target_date'] ?? '';
$reason = $_POST['reason'] ?? '';

// バリデーション
if (!$employee_id || empty($type) || empty($target_date)) {
    $_SESSION['error_message'] = "必須項目が不足しています。";
    header('Location: main.php');
    exit;
}

try {
    $stmt = $PDO->prepare("INSERT INTO APPLICATIONS (EMPLOYEE_ID, APPLICATION_TYPE, TARGET_DATE, REASON, STATUS) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$employee_id, $type, $target_date, $reason]);

    $_SESSION['success_message'] = "申請が完了しました。管理者による承認をお待ちください。";

} catch (PDOException $e) {
    error_log("Application error: " . $e->getMessage());
    $_SESSION['error_message'] = "申請処理中にデータベースエラーが発生しました。";
}

header('Location: main.php');
exit;
?>