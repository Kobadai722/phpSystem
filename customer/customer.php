<!--!+tab-->
<?php session_start(); ?> 
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客管理</title>
</head>
<body>
    <h1>顧客管理</h1>
    <h2>以下に一覧が表示される予定</h2>
    <table  border="1">
    <tr><th>商品番号</th><th>商品名</th><th>価格</th></tr>
    <?php
        require_once '../config.php';
        foreach($pdo->query('select * from CUSTOMER') as $row){
    ?>
        <tr>
        <td><?=$row['CUSTOMER_ID']?></td>
        <td><a href="detail.php?id=<?=$row['p_id']?>"><?=$row['p_name']?></a></td>
        <td><?=$row['p_price']?></td>
        </tr>
    <?php
        }
    ?>
    </table>
</body>
</html>