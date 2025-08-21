<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];

    try {
        $stmt = $PDO->prepare("DELETE FROM CUSTOMER WHERE CUSTOMER_ID = ?");
        $stmt->execute([$customer_id]);
        $_SESSION['success_message'] = '顧客情報を削除しました。';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = '削除に失敗しました: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = '無効なリクエストです。';
}

header('Location: customer.php');
exit;
?>