<?php session_start();?>
<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>人事管理表</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>

    <body>
        <h1><?php echo $_GET['NAME']; ?>-詳細</h1>

<!-- 所属社員の表示欄 -->
        <table class="table table-hover">
            <tr><th scope="col">社員番号</th><th scope="col">氏名</th><th scope="col">所属部署</th><th scope="col">職位</th><th scope="col">メールアドレス</th><th scope="col">緊急連絡先</th></tr>
            <?php require_once '../config.php'; //DBサーバーと接続
                $sql = "SELECT * FROM EMPLOYEE where NAME=?";

                //
                foreach($PDO->query($sql) as $row){ ?>
                <tr>
                    <td scope="row"><?=$row['EMPLOYEE_ID']?></td>
                    <td><?= $row['NAME']?></td>
                    <td><?= $row['DIVISION_NAME']?></td><!--部署-->
                    <td><?= $row['JOB_POSITION_NAME']?></td><!--職位-->
                    <td><?= $row['URGENCY_CELL_NUMBER']?></td>
                    <td><?= $row['JOINING_DATE']?></td>
                    <td><?= $row['POST_CODE']?></td>
                    <td><?= $row['ADDRESS']?></td>
                </tr>
                <?php
                }
            ?>

        </table>
    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
