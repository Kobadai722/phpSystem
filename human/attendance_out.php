<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['employee_id'])) {
    $_SESSION['error_message'] = "ログインが必要です。";
    header('Location: ../login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$date = date("Y-m-d");
$time = date("H:i:s");

try {
    $stmt = $PDO->prepare("SELECT CLOCK_OUT_TIME FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
    $stmt->execute([$employee_id, $date]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $_SESSION['error_message'] = "出勤記録がありません。";
        header('Location: ../main.php');
        exit;
    }
    if ($record['CLOCK_OUT_TIME']) {
        $_SESSION['error_message'] = "本日はすでに退勤済みです。";
        header('Location: ../main.php');
        exit;
    }

    $stmt = $PDO->prepare("UPDATE ATTENDANCE SET CLOCK_OUT_TIME = ? WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
    $stmt->execute([$time, $employee_id, $date]);

    $_SESSION['success_message'] = "退勤しました。";
    header('Location: ../main.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['error_message'] = "データベースエラーにより退勤に失敗しました。";
    header('Location: ../main.php');
    exit;
}
?>