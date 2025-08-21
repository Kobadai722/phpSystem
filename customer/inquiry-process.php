<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: customer.php');
    exit;
}

$action = $_POST['action'] ?? '';
$customer_id = $_POST['customer_id'] ?? 0;

try {
    switch ($action) {
        case 'register':
            $stmt = $PDO->prepare("INSERT INTO INQUIRY_DETAIL (CUSTOMER_ID, INQUIRY_DATETIME, INQUIRY_DETAIL) VALUES (?, ?, ?)");
            $stmt->execute([$customer_id, $_POST['inquiry_datetime'], $_POST['inquiry_detail']]);
            $_SESSION['success_message'] = '新しい問い合わせを登録しました。';
            break;

        case 'edit':
            $stmt = $PDO->prepare("UPDATE INQUIRY_DETAIL SET INQUIRY_DATETIME = ?, INQUIRY_DETAIL = ?, STATUS = ? WHERE INQUIRY_DETAIL_ID = ?");
            $stmt->execute([$_POST['inquiry_datetime'], $_POST['inquiry_detail'], $_POST['status'], $_POST['inquiry_detail_id']]);
            $_SESSION['success_message'] = '問い合わせを更新しました。';
            break;
            
        case 'delete':
            $stmt = $PDO->prepare("DELETE FROM INQUIRY_DETAIL WHERE INQUIRY_DETAIL_ID = ?");
            $stmt->execute([$_POST['inquiry_detail_id']]);
            $_SESSION['success_message'] = '問い合わせを削除しました。';
            break;

        case 'update_status':
            $stmt = $PDO->prepare("UPDATE INQUIRY_DETAIL SET STATUS = ? WHERE INQUIRY_DETAIL_ID = ?");
            $stmt->execute([$_POST['status'], $_POST['inquiry_detail_id']]);
            $_SESSION['success_message'] = '対応状況を更新しました。';
            break;
    }
} catch (PDOException $e) {
    // 実際には、より詳細なエラーハンドリングが望ましい
    $_SESSION['error_message'] = '処理中にエラーが発生しました。';
}

header("Location: inquiry.php?customer_id=" . $customer_id);
exit;