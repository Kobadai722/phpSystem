<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: application_list.php');
    exit;
}

$application_id = $_POST['application_id'] ?? null;
$status = $_POST['status'] ?? null;

if ($application_id && in_array($status, ['approved', 'rejected'])) {
    try {
        $stmt = $PDO->prepare("UPDATE APPLICATIONS SET STATUS = ? WHERE APPLICATION_ID = ?");
        $stmt->execute([$status, $application_id]);
        
        $msg = ($status === 'approved') ? '承認' : '却下';
        $_SESSION['success_message'] = "申請ID: {$application_id} を{$msg}しました。";
        
    } catch (PDOException $e) {
        error_log("Status update error: " . $e->getMessage());
        $_SESSION['error_message'] = "更新中にエラーが発生しました。";
    }
}

header('Location: application_list.php');
exit;
?>