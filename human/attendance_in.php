<?php
session_start();
$db_host = 'localhost';
$db_name = 'your_database';
$db_user = 'your_username';
$db_pass = 'your_password';
if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}
$uid = $_SESSION['uid'];
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ATTENDANCE WHERE UID = :uid AND DATE(ATTENDANCE_DATE) = CURDATE()");
    $stmt->execute(['uid' => $uid]);
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO ATTENDANCE (UID, ATTENDANCE_DATE, ATTENDANCE_TIME, STATUS_FLAG) VALUES (:uid, CURDATE(), NOW(), 1)");
        $stmt->execute(['uid' => $uid]);
        $_SESSION['message'] = '出勤を記録しました。';
    } else {
        $_SESSION['message'] = '既に出勤が記録されています。';
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'エラーが発生しました: ' . $e->getMessage();
}
header('Location: ../main.php');
exit();