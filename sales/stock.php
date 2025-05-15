<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理</title>
</head>
<body>
    <table border="2">
    <tr><th>商品番号</th><th>商品名</th><th>価格</th></tr>
    
    <?php
    require_once 'config.php';
     foreach ($pdo->query('select * from products')as $row){
        ?>
    <tr>
    <td><?= $row['p_id']?></td>
    <td><?="<a href=\"detail.php?id=",$row['p_id'],"\">",$row['p_name']?></a></td>
    <?php
    //上記の"はHTMLのダブルクォーテーションにしたいため[\]を使用
    ?>
    <td><?= number_format($row['p_price']),"円"?></td>
    </tr>
    <?php
    }
    ?>
    </table>
</body>
</html>