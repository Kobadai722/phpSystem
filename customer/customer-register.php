<?php session_start(); 
require_once '../config.php'; 
$sql=$PDO->prepare('select adress from products where p_id = ?');
$sql->execute([$_SESSION['id']]);
?> 
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>配送先入力：藤江商店</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <script src="js/ajaxzip3.js" charset="UTF-8"></script>
</head>
<?php include '../header.php'; ?>
<body>
    <main>
        <h2>配送先を入力してください</h2>
        <form action="check.php" method="post">
        <table border="1">
            <tr><th>郵便番号</th><td><input type="text" name="u_adnum" value="<?=$_SESSION['u_adnum']?>" maxlength="7" onKeyUp="AjaxZip3.zip2addr(this,'','address','address');"/><br>※郵便番号のハイフンは不要</td></tr>
            <tr><th>住所</th><td><input type="text" name="address" value="<?=$_SESSION['u_adress']?>"/></td></tr>
            <tr><th>氏名</th><td><input type="text" name="name" /></td></tr>
            <tr><th>電話番号</th><td><input type="tel" name="tel"/></td></tr>
            <tr><th>支払方法</th>
                <td>
                    <select name="pay">
                    <option value="1">クレジットカード</option>
                    <option value="2">代金引換</option>
                    <option value="3">コンビニ支払い</option>
                    <option value="4">コード決済</option>
                    </select>
                </td>
            </tr>
        </table>
        <p><input type="submit" value="注文内容の確認に進む"></p>
        </form>
        <table  border="1">
        <tr><th>商品番号</th><th>商品名</th><th>価格</th><th>購入数</th><th>小計</th></tr>
        <?php
        $sum=0;
        //$_SESSION['cart'][$_POST['pid']] = $_POST['count'];
        require_once '../config.php';
        $sql=$PDO->prepare('select * from products where p_id = ?');
        foreach($_SESSION['cart'] as $key=>$value){
            $sql->execute([$key]);
            foreach($sql as $row){
                $sum=$sum+$row['p_price']*$value;
        ?>
            <tr>
                <td><?=$key?></td>
                <td><?=$row['p_name']?></td>
                <td><?=$row['p_price']?></td>
                <td><?=$value?></td>
                <td><?=$row['p_price']*$value?></td>
            </tr>
        <?php
            }
        }
        ?>
            <tr>
                <th>合計</th>
                <td><?=$sum?></td>
            </tr>
        </table>
        <p><a href="./cart.php">カートに戻る</a></p>
    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>