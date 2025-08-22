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
    $stmt = $PDO->prepare("SELECT ATTENDANCE_ID FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
    $stmt->execute([$employee_id, $date]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "本日はすでに出勤済みです。";
        header('Location: ../main.php');
        exit;
    }

    $stmt = $PDO->prepare("INSERT INTO ATTENDANCE (EMPLOYEE_ID, ATTENDANCE_DATE, CLOCK_IN_TIME) VALUES (?, ?, ?)");
    $stmt->execute([$employee_id, $date, $time]);

    $_SESSION['success_message'] = "出勤しました。";
    header('Location: ../main.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['error_message'] = "データベースエラーにより出勤に失敗しました。";
    header('Location: ../main.php');
    exit;
}
?>