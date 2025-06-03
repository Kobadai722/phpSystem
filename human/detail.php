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

    <!-- 検索欄 -->
    <form>
        <p>氏名：<input type="text" name="keyword">
           <input type="submit" value="検索"></p>
        <p>従業員番号：<input type="text" name="keyword">
           <input type="submit" value="検索"></p>
    </form>

    <!-- 詳細表示 -->
    <table class="table table-hover">
        <tr>
            <th scope="col">社員番号</th>
            <th scope="col">氏名</th>
            <th scope="col">所属部署</th>
            <th scope="col">職位</th>
            <th scope="col">メールアドレス</th>
            <th scope="col">緊急連絡先</th>
            <th scope="col">入社日</th>
            <th scope="col">郵便番号</th>
            <th scope="col">住所</th>
        </tr>
        <?php
            require_once '../config.php';

            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $sql = $PDO->prepare(
                    "SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
                    FROM EMPLOYEE e
                    LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                    LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID
                    WHERE e.EMPLOYEE_ID = ?"
                );
                $sql->execute([$_GET['id']]);
                $details = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach ($details as $row) {
                    ?>
                    <tr>
                        <td><?= $row['EMPLOYEE_ID'] ?></td>
                        <td><?= $row['NAME'] ?></td>
                        <td><?= $row['DIVISION_NAME'] ?? '未登録' ?></td>
                        <td><?= $row['JOB_POSITION_NAME'] ?? '未登録' ?></td>
                        <td><?= $row['EMAIL'] ?? '未入力' ?></td>
                        <td><?= $row['URGENCY_CELL_NUMBER'] ?? '未入力' ?></td>
                        <td><?= $row['JOINING_DATE'] ?? '未入力' ?></td>
                        <td><?= $row['POST_CODE'] ?? '未入力' ?></td>
                        <td><?= $row['ADDRESS'] ?? '未入力' ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='9'>無効なIDです。</td></tr>";
            }
        ?>
    </table>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>