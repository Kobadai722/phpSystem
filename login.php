<?php
session_start();


unset($_SESSION['id']);
$PDO = new PDO('mysql:host=chandou.ltt.jp; dbname=utiraku0428; charset=utf8', 'utiraku', '4sp3Yukt');
$sql = $PDO->prepare('select*from USERS where u_name=? and u_pass=?');
$sql->execute([$_POST['user'], $_POST['pass']]);
echo htmlspecialchars($_POST['user']);
echo htmlspecialchars($_POST['pass']);

foreach ($sql as $row) {
    $_SESSION['id'] = $row['u_id'];
    $_SESSION['dname'] = $row['u_dname'];
}
if (isset($_SESSION['id'])) {
    header('Location: ./main.php');
    exit();
} else {
    header('Location: ./index.php');
    exit();
}

?>
