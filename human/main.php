<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>人事管理表</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>
    <?php include '../header.php'; ?>
    <body>
        <h1>人事管理表</h1>
<!-- 所属社員の検索欄 -->
        <form>
        <p>氏名：  <input type="text"  name="keyword">
                <input type="submit" value="検索"></p>
        <p>従業員番号： <input type="text"  name="keyword">
                    <input type="submit" value="検索"></p>
        </form>

<!-- 編集者ページの切り替え 後々CSSで右寄せ予定 -->
    <select name="edit">
        <option value="edit"><a href="editer.php">編集者画面に切り替える</a></option>
        <option value="nomal"><a href="main.php">一般画面に切り替える</a></option>
    </select>

<!-- 所属社員の表示欄 -->        
        <table class="table table-hover">
            <tr>
                <th scope="col">社員番号</th>
                <th scope="col">氏名</th>
                <th scope="col">所属部署</th>
                <th scope="col">職位</th>
                <th scope="col">入社日</th>
                <th scope="col">緊急連絡先</th>
            </tr>
            <?php require_once '../config.php'; //DBサーバーと接続
                $sql = $PDO ->prepare("SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
                        FROM EMPLOYEE e
                        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                        LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID");
                $sql -> execute();
                foreach($sql as $row){ ?>
                <tr>
                    <td scope="row"><?= htmlspecialchars($row['EMPLOYEE_ID'])?></td>
                    <td><a href="detail.php?id=<?= htmlspecialchars($row['EMPLOYEE_ID'])?>"><?= htmlspecialchars($row['NAME'])?></a></td>
                    <td><?= htmlspecialchars($row['DIVISION_NAME'])?></td><!--部署-->
                    <td><?= htmlspecialchars($row['JOB_POSITION_NAME'])?></td><!--職位-->
                    <td><?= htmlspecialchars($row['JOINING_DATE'])?></td><!--入社日-->
                    <td><?= htmlspecialchars($row['URGENCY_CELL_NUMBER'])?></td><!--緊急連絡先-->
                </tr>
                <?php
                };
            ?>

        </table>



    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
