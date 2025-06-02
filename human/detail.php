<?php session_start();?>
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
        <h1><?php echo htmlspecialchars($_GET['NAME']); ?>-詳細</h1>

<!-- 所属社員の表示欄 -->
<table class="table table-hover">
            <tr>
                <th scope="col">社員番号</th>
                <th scope="col">氏名</th>
                <th scope="col">所属部署</th>
                <th scope="col">職位</th>
                <th scope="col">緊急連絡先</th> <th scope="col">入社日</th>
                <th scope="col">郵便番号</th>
                <th scope="col">住所</th>
            </tr>
            <?php
                require_once '../config.php'; //DBサーバーと接続

                // $_GET['NAME'] がセットされているか確認
                if (isset($_GET['NAME'])) {
                    // 1. プリペアドステートメントを準備
                    $sql = "SELECT * FROM EMPLOYEE WHERE NAME = ?";
                    $stmt = $PDO->prepare($sql);

                    // 2. パラメータをバインドしてSQLを実行
                    // $_GET['NAME']の値をプレースホルダーに安全にバインドします。
                    $stmt->execute([$_GET['NAME']]);

                    // 3. 結果を1行ずつ取得して表示
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            ?>
                <tr>
                    <td scope="row"><?= htmlspecialchars($row['EMPLOYEE_ID'])?></td>
                    <td><?= htmlspecialchars($row['NAME'])?></td>
                    <td><?= htmlspecialchars($row['DIVISION_NAME'])?></td><td><?= htmlspecialchars($row['JOB_POSITION_NAME'])?></td><td><?= htmlspecialchars($row['URGENCY_CELL_NUMBER'])?></td>
                    <td><?= htmlspecialchars($row['JOINING_DATE'])?></td>
                    <td><?= htmlspecialchars($row['POST_CODE'])?></td>
                    <td><?= htmlspecialchars($row['ADDRESS'])?></td>
                </tr>
            <?php
                    }
                } else {
                    // NAMEパラメータがない場合の処理（例: エラーメッセージ表示）
                    echo '<tr><td colspan="8">社員名が指定されていません。</td></tr>';
                }
            ?>

        </table>
    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
