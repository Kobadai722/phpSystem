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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ATTENDANCE WHERE UID = :uid AND DATE(ATTENDANCE_DATE) = CURDATE() AND LEAVE_TIME IS NULL");
    $stmt->execute(['uid' => $uid]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $stmt = $pdo->prepare("UPDATE ATTENDANCE SET LEAVE_TIME = NOW() WHERE UID = :uid AND DATE(ATTENDANCE_DATE) = CURDATE()");
        $stmt->execute(['uid' => $uid]);
        $_SESSION['message'] = '退勤を記録しました。';
    } else {
        $_SESSION['message'] = '出勤が記録されていないか、既に退勤が記録されています。';
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'エラーが発生しました: ' . $e->getMessage();
}
header('Location: ../main.php');
exit();
?>