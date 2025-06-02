<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>人事管理表</title> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<?php include '../header.php'; ?>

<body>
    <?php
    $page_main_title = "社員詳細"; // デフォルトのH1タイトル
    $employee_data = null;    // 社員データを格納する変数
    $error_message = null;    // エラーメッセージを格納する変数

    // URLからIDパラメータを取得
    if (isset($_GET['ID']) && !empty(trim($_GET['ID']))) {
        $employee_id_to_fetch = trim($_GET['ID']);
        require_once '../config.php'; // DBサーバーと接続

        // config.php で $PDO が正しく初期化されているか確認してください。
        // 例: $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); が設定されているかなど。

        try {
            // 1. プリペアドステートメントを準備 (EMPLOYEE_IDで検索)
            $sql = "SELECT * FROM EMPLOYEE WHERE EMPLOYEE_ID = ?";
            $stmt = $PDO->prepare($sql);

            // 2. パラメータをバインドしてSQLを実行
            $stmt->execute([$employee_id_to_fetch]);

            // 3. 結果を1行取得 (ID検索なので通常1件のはず)
            $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee_data) {
                // データが見つかった場合、H1タイトルを社員名にする
                $page_main_title = htmlspecialchars($employee_data['NAME']) . "-詳細";
                // <title>タグも動的に変更したい場合はJavaScriptか、ここで再度<title>を出力
            } else {
                $error_message = '該当する社員情報が見つかりませんでした。(ID: ' . htmlspecialchars($employee_id_to_fetch) . ')';
                $page_main_title = "該当社員なし - 詳細";
            }
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage()); // エラーログに記録
            $error_message = '情報の取得中にエラーが発生しました。管理者に連絡してください。';
            $page_main_title = "データベースエラー - 詳細";
        }
    } else {
        // IDパラメータがない場合の処理
        $error_message = '社員IDが指定されていません。';
        $page_main_title = "社員ID未指定 - 詳細";
    }
    ?>

    <h1><?php echo $page_main_title; ?></h1>

    <table class="table table-hover">
        <thead> <tr>
                <th scope="col">社員番号</th>
                <th scope="col">氏名</th>
                <th scope="col">所属部署</th>
                <th scope="col">職位</th>
                <th scope="col">緊急連絡先</th>
                <th scope="col">入社日</th>
                <th scope="col">郵便番号</th>
                <th scope="col">住所</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($employee_data) {
                // 社員データが見つかった場合、表示
            ?>
                <tr>
                    <td scope="row"><?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?></td>
                    <td><?= htmlspecialchars($employee_data['NAME']) ?></td>
                    <td><?= htmlspecialchars($employee_data['DIVISION_NAME']) ?></td>
                    <td><?= htmlspecialchars($employee_data['JOB_POSITION_NAME']) ?></td>
                    <td><?= htmlspecialchars($employee_data['URGENCY_CELL_NUMBER']) ?></td>
                    <td><?= htmlspecialchars($employee_data['JOINING_DATE']) ?></td>
                    <td><?= htmlspecialchars($employee_data['POST_CODE']) ?></td>
                    <td><?= htmlspecialchars($employee_data['ADDRESS']) ?></td>
                </tr>
            <?php
            } else {
                // データがない、またはID未指定の場合、エラーメッセージを表示
                // $error_message は上で設定済み
                echo '<tr><td colspan="8">' . htmlspecialchars($error_message) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>