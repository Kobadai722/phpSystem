<?php
session_start();


unset($_SESSION['id']);
require_once 'config.php';
$sql = $PDO->prepare('select*from EMPLOYEE where EMPLOYEE_ID=? and PASSWORD=?');
$sql->execute([$_POST['employeeId'], $_POST['pass']]);

foreach ($sql as $row) {
    $_SESSION['id'] = $row['EMPLOYEE_ID'];
    $_SESSION['dname'] = $row['NAME'];
}
if (isset($_SESSION['id'])) {
    header('Location: ./main.php');
    exit();
} else {
    header('Location: ./index.php');
    exit();
}

?>
