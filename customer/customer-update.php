<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $name = $_POST['name'];
    $cell_number = $_POST['cell_number'];
    $mail = $_POST['mail'];
    $post_code = $_POST['post_code'];
    $address = $_POST['address'];

    // 簡単なバリデーション
    if (empty($name) || empty($mail) || empty($post_code) || empty($address)) {
        $_SESSION['error_message'] = '必須項目が入力されていません。';
        header("Location: customer-edit.php?id=" . $customer_id);
        exit;
    }

    try {
        $stmt = $PDO->prepare(
            "UPDATE CUSTOMER SET NAME = ?, CELL_NUMBER = ?, MAIL = ?, POST_CODE = ?, ADDRESS = ? WHERE CUSTOMER_ID = ?"
        );
        $stmt->execute([$name, $cell_number, $mail, $post_code, $address, $customer_id]);
        $_SESSION['success_message'] = '顧客情報を更新しました。';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = '更新に失敗しました: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = '無効なリクエストです。';
}

header('Location: customer.php');
exit;
?>