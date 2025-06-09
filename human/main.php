<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>人事管理表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="human.css">
</head>
<?php include '../header.php'; ?>

<body>
    <h1>人事管理表</h1>
    <div class="editer">
        <form method="get" action="">
            <select name="edit" onchange="location = this.value;">
                <option value="main.php">一般画面</option>
                <option value="editer.php">編集者画面に切り替える</option>
            </select>
        </form>
    </div>

    <div>
        <form action="main.php" method="get" class="mb-3">
            <p>
                氏名： <input type="text" name="name_keyword" value="<?= htmlspecialchars($_GET['name_keyword'] ?? '', ENT_QUOTES) ?>">
                従業員番号： <input type="text" name="id_keyword" value="<?= htmlspecialchars($_GET['id_keyword'] ?? '', ENT_QUOTES) ?>">
                <input type="submit" value="検索" class="btn btn-primary">
            </p>
        </form>
    </div>

    <table class="table table-hover">
        <tr>
            <th scope="col">社員番号</th>
            <th scope="col">氏名</th>
            <th scope="col">所属部署</th>
            <th scope="col">職位</th>
            <th scope="col">入社日</th>
            <th scope="col">緊急連絡先</th>
        </tr>
        <?php
        require_once '../config.php'; //DBサーバーと接続

        // 検索キーワードの受け取り
        $name_keyword = $_GET['name_keyword'] ?? null;
        $id_keyword = $_GET['id_keyword'] ?? null;

        // ベースとなるSQLクエリ
        $sql_query = "SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
                      FROM EMPLOYEE e
                      LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                      LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID";

        $conditions = [];
        $params = [];

        // 氏名での検索条件を追加
        if (!empty($name_keyword)) {
            $conditions[] = "e.NAME LIKE ?";
            $params[] = '%' . $name_keyword . '%';
        }

        // 従業員番号での検索条件を追加
        if (!empty($id_keyword)) {
            $conditions[] = "e.EMPLOYEE_ID LIKE ?";
            $params[] = '%' . $id_keyword . '%';
        }

        // 検索条件が存在する場合、WHERE句をSQLに追加
        if (!empty($conditions)) {
            // AND検索にする場合は " AND "、OR検索にする場合は " OR " を指定
            $sql_query .= " WHERE " . implode(" AND ", $conditions);
        }

        // SQLを準備して実行
        $sql = $PDO->prepare($sql_query);
        $sql->execute($params);

        foreach ($sql as $row) { ?>
            <tr>
                <td scope="row"><?= htmlspecialchars($row['EMPLOYEE_ID']) ?></td>
                <td><a href="detail.php?id=<?= htmlspecialchars($row['EMPLOYEE_ID']) ?>"><?= htmlspecialchars($row['NAME']) ?></a></td>
                <td><?= htmlspecialchars($row['DIVISION_NAME']) ?></td><td><?= htmlspecialchars($row['JOB_POSITION_NAME']) ?></td><td><?= htmlspecialchars($row['JOINING_DATE']) ?></td><td><?= htmlspecialchars($row['URGENCY_CELL_NUMBER']) ?></td></tr>
        <?php
        };
        ?>

    </table>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>