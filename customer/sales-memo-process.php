<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: customer.php');
    exit;
}

$action = $_POST['action'] ?? '';
$customer_id = $_POST['customer_id'] ?? null;

// 空の値をNULLに変換
$trading_amount = !empty($_POST['trading_amount']) ? $_POST['trading_amount'] : null;
$order_accuracy = !empty($_POST['order_accuracy']) ? $_POST['order_accuracy'] : null;
$memo = !empty($_POST['memo']) ? $_POST['memo'] : null;
$negotiation_date = !empty($_POST['negotiation_date']) ? $_POST['negotiation_date'] : null;


try {
    switch ($action) {
        case 'register':
            $stmt = $PDO->prepare(
                "INSERT INTO NEGOTIATION_MANAGEMENT (CUSTOMER_ID, EMPLOYEE_ID, TRADING_AMOUNT, ORDER_ACCURACY, NEGOTIATION_PHASE, NEGOTIATION_DATE, MEMO) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $_POST['customer_id'], 
                $_POST['employee_id'], 
                $trading_amount, 
                $order_accuracy, 
                $_POST['negotiation_phase'],
                $negotiation_date,
                $memo
            ]);
            $_SESSION['success_message'] = '新しい商談を登録しました。';
            break;

        case 'edit':
            $stmt = $PDO->prepare(
                "UPDATE NEGOTIATION_MANAGEMENT SET EMPLOYEE_ID = ?, TRADING_AMOUNT = ?, ORDER_ACCURACY = ?, NEGOTIATION_PHASE = ?, NEGOTIATION_DATE = ?, MEMO = ?
                 WHERE NEGOTIATION_ID = ?"
            );
            $stmt->execute([
                $_POST['employee_id'], 
                $trading_amount, 
                $order_accuracy, 
                $_POST['negotiation_phase'], 
                $negotiation_date,
                $memo,
                $_POST['negotiation_id']
            ]);
            $_SESSION['success_message'] = '商談情報を更新しました。';
            break;
            
        case 'delete':
            $stmt = $PDO->prepare("DELETE FROM NEGOTIATION_MANAGEMENT WHERE NEGOTIATION_ID = ?");
            $stmt->execute([$_POST['negotiation_id']]);
            $_SESSION['success_message'] = '商談情報を削除しました。';
            break;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = '処理中にエラーが発生しました。' . $e->getMessage();
}

if ($customer_id) {
    header("Location: sales-memo.php?customer_id=" . $customer_id);
} else {
    header("Location: customer.php");
}
exit;