<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>人事管理表</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>

    <body>
        <h1>人事管理表</h1>
<!-- 所属社員の検索欄 -->
        <form>
        氏名：  <input type="text"  name="keyword">
                <input type="submit" value="検索">
        従業員番号： <input type="text"  name="keyword">
                    <input type="submit" value="検索">
        </form>
<!-- 所属社員の表示欄 -->

        
        <table class="table table-hover">
            <tr><th scope="col">社員番号</th><th scope="col">所属部署</th><th scope="col">職位</th><th scope="col">勤怠管理</th><th scope="col">勤怠状況</th></tr>
            <?php require_once '../config.php'; //DBサーバーと接続
            /*foreach($PDO->query(
                    'SELECT e.*, 
                        d.DIVISION_ID, 
                        j.JOB_POSITION
                    FROM 
                        EMPLOYEE e
                    LEFT JOIN 
                        DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                    LEFT JOIN 
                        JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID'
                        ) as $row)
            */
            foreach ($pdo->query(
                'SELECT e.*,
                        d.DIVISION_ID,
                        j.JOB_POSITION
                 FROM EMPLOYEE e
                 LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                 LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID'
            ) as $row) 
            { ?> 
                <tr>
                    <td scope="row"><?=$row['EMPLOYEE_ID']?></td>
                    <td><?=$row['NAME']?></td>
                    <td><?=$row['CELL_NUMBER']?></td>
                    <td><?=$row['DIVISION_ID'] //部署 ?></td>
                    <td><?=$row['JOB_POSITION_ID'] //部署 ?></td>
                    <td><?=$row['ADDRESS']?></td> 
                    <td><?=$row['URGENCY_CELL_NUMBER']?></td>
        </tr>
            <?php   }
            ?>
        </table>
    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
