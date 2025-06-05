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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
    <main>
        <button type="button" class="btn btn-primary mb-4 pull-right" onclick="customer-register.php"><i class="bi bi-plus-lg"></i>追加</button>
        <div class="accordion mb-4" id="accordionExample">
        <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
             <i class="bi bi-search"></i>検索
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="gray-box">
                    <form action="customer-output.php" method="post" class="mb-4">
                        <div class="mb-2">
                            <label for="customerid" class="form-label">顧客ID</label>
                            <input type="text" name="customerid" id="customerid" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label for="name" class="form-label">氏名</label>
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label for="cell_number" class="form-label">電話番号</label>
                            <input type="text" name="cell_number" id="cell_number" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label for="mail" class="form-label">メールアドレス</label>
                            <input type="text" name="mail" id="mail" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label for="post_code" class="form-label">郵便番号</label>
                            <input type="text" name="post_code" id="post_code" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">住所</label>
                            <input type="text" name="address" id="address" class="form-control">
                        </div>
                            <input type="submit" value="検索" class="btn btn-primary">
                    </form> 
                </div>
            </div>
        </div>
        </div>

        </div>
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