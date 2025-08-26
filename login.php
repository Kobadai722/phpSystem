<?php
session_start();


unset($_SESSION['employee_id']);
require_once 'config.php';
$sql = $PDO->prepare('select*from EMPLOYEE where EMPLOYEE_ID=? and PASSWORD=?');
$sql->execute([$_POST['employeeId'], $_POST['pass']]);

foreach ($sql as $row) {
    $_SESSION['employee_id'] = $row['EMPLOYEE_ID'];
    $_SESSION['employee_name'] = $row['NAME'];
}
if (isset($_SESSION['employee_id'])) {
    header('Location: ./main.php');
    exit();
} else {
    header('Location: ./index.php');
    exit();
}

?>