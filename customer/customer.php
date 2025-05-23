<!--!+tab-->
<?php session_start(); ?> 
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客管理</title>
    <link>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
    <main>
        <div class="gray-box">
            <h3>検索</h3>
        </div>
        <p>商品検索</p>
        <form action="search-output.php" method="post">
            <p><input type="text" name="keyword"></p>
            <p><input type="submit" value="検索"></p>
        </form>
        <table class="table table-hover">
            <tr><th scope="col">顧客ID</th><th scope="col">氏名</th><th scope="col">電話番号</th><th scope="col">メールアドレス</th><th scope="col">郵便番号</th><th scope="col">住所</th></tr>
            <?php
                require_once '../config.php';
                foreach($PDO->query('select * from CUSTOMER') as $row){
            ?>
                <tr>
                <td scope="row"><?=$row['CUSTOMER_ID']?></td>
                <td><?=$row['NAME']?></td>
                <td><?=$row['CELL_NUMBER']?></td>
                <td><?=$row['MAIL']?></td>
                <td><?=$row['POST_CODE']?></td>
                <td><?=$row['ADDRESS']?></td>
                </tr>
            <?php
                }
            ?>
            </table>
    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>