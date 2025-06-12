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
    <h1>人事管理表-編集者モード</h1>
    <?php
    require_once '../config.php'; //DBサーバーと接続

    // 社員が所属する部署のみを重複なく取得する
    $stmt_divisions = $PDO->query(
        "SELECT DISTINCT d.DIVISION_ID, d.DIVISION_NAME
        FROM EMPLOYEE e
        INNER JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        WHERE d.DIVISION_NAME IS NOT NULL AND d.DIVISION_NAME != ''
        ORDER BY d.DIVISION_ID"
    );
    $divisions = $stmt_divisions->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="mb-3 p-3 border rounded">
        <form>
            <div class="row g-3 aligh-items-center">
                <div class="col-auto">
                    <label for="display_mode_select" class="col-form-label">表示モード：</label>
                </div>
                <div class="col-auto">
                <select id="display_mode_select" name="edit" class="form-select" onchange="location = this.value;">
                        <option value="main.php">一般画面</option>
                        <option value="editer.php" selected>編集者画面</option>
                </select>
                </div>
            </div>
        </form>
    </div>

    <div>
        <form action="editer.php" method="get" class="mb-3 p-3 border rounded">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="name_keyword" class="col-form-label">氏名：</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="name_keyword" name="name_keyword" class="form-control" value="<?= htmlspecialchars($_GET['name_keyword'] ?? '', ENT_QUOTES) ?>">
                </div>

                <div class="col-auto">
                    <label for="id_keyword" class="col-form-label">従業員番号：</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="id_keyword" name="id_keyword" class="form-control" value="<?= htmlspecialchars($_GET['id_keyword'] ?? '', ENT_QUOTES) ?>">
                </div>

                <div class="col-auto">
                    <label for="division_id" class="col-form-label">所属部署：</label>
                </div>
                <div class="col-auto">
                    <select id="division_id" name="division_id" class="form-select" onchange="this.form.submit()">
                        <option value="">全ての部署</option>
                        <?php foreach ($divisions as $division) : ?>
                            <option value="<?= htmlspecialchars($division['DIVISION_ID']) ?>" <?= (($_GET['division_id'] ?? '') == $division['DIVISION_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($division['DIVISION_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <input type="submit" value="検索" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">社員番号</th>
                <th scope="col">氏名</th>
                <th scope="col">所属部署</th>
                <th scope="col">職位</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 検索キーワードの受け取り
            $name_keyword = $_GET['name_keyword'] ?? null;
            $id_keyword = $_GET['id_keyword'] ?? null;
            $division_id = $_GET['division_id'] ?? null; 

            // ベースとなるSQLクエリ
            $sql_query = "SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
                            FROM EMPLOYEE e
                            LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                            LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID";

            $conditions = [];
            $params = [];

            // 氏名での検索条件
            if (!empty($name_keyword)) {
                $conditions[] = "e.NAME LIKE ?";
                $params[] = '%' . $name_keyword . '%';
            }

            // 従業員番号での検索条件
            if (!empty($id_keyword)) {
                $conditions[] = "e.EMPLOYEE_ID LIKE ?";
                $params[] = '%' . $id_keyword . '%';
            }

            // 部署での絞り込み条件
            if (!empty($division_id)) {
                $conditions[] = "e.DIVISION_ID = ?";
                $params[] = $division_id;
            }

            // 検索条件が存在する場合、WHERE句をSQLに追加
            if (!empty($conditions)) {
                $sql_query .= " WHERE " . implode(" AND ", $conditions);
            }

            // SQLを準備して実行
            $sql = $PDO->prepare($sql_query);
            $sql->execute($params);

            foreach ($sql as $row) { ?>
                <tr>
                    <td scope="row"><?= htmlspecialchars($row['EMPLOYEE_ID']) ?></td>
                    <td><a href="detail.php?id=<?= htmlspecialchars($row['EMPLOYEE_ID']) ?>"><?= htmlspecialchars($row['NAME']) ?></a></td>
                    <td><?= htmlspecialchars($row['DIVISION_NAME']) ?></td>
                    <td><?= htmlspecialchars($row['JOB_POSITION_NAME']) ?></td>
                </tr>
            <?php
            };
            ?>
        </tbody>
    </table>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>