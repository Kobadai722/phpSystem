<?php session_start(); ?>
<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <main>
        <div class="container mt-4">
        <h3>検索結果</h3>
        <table class="table table-hover">
            <tr><th>顧客ID</th><th>氏名</th><th>電話番号</th><th>メールアドレス</th><th>郵便番号</th><th>住所</th></tr>
            <?php
                $customerid = $_POST['customerid'] ?? '';
                $name = $_POST['name'] ?? '';
                $cell_number = $_POST['cell_number'] ?? '';
                $mail = $_POST['mail'] ?? '';
                $post_code = $_POST['post_code'] ?? '';
                $address = $_POST['address'] ?? '';

                $sql = 'SELECT * FROM CUSTOMER WHERE 1=1';
                $params = [];

                if (!empty($customerid)) {
                    $sql .= ' AND CUSTOMER_ID = ?';
                    $params[] = $customerid;
                }
                if (!empty($name)) {
                    $sql .= ' AND NAME LIKE ?';
                    $params[] = '%' . $name . '%';
                }
                if (!empty($cell_number)) {
                    $sql .= ' AND CELL_NUMBER LIKE ?';
                    $params[] = '%' . $cell_number . '%';
                }
                if (!empty($mail)) {
                    $sql .= ' AND MAIL LIKE ?';
                    $params[] = '%' . $mail . '%';
                }
                if (!empty($post_code)) {
                    $sql .= ' AND POST_CODE LIKE ?';
                    $params[] = '%' . $post_code . '%';
                }
                if (!empty($address)) {
                    $sql .= ' AND ADDRESS LIKE ?';
                    $params[] = '%' . $address . '%';
                }

                if (empty($params)) {
                    echo '<tr><td colspan="6">検索条件を入力してください。</td></tr>';
                } else {
                    $stmt = $PDO->prepare($sql);
                    $stmt->execute($params);
                    $results = $stmt->fetchAll();

                    if ($results) {
                        foreach ($results as $row) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['CUSTOMER_ID']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['NAME']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['CELL_NUMBER']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['MAIL']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['POST_CODE']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['ADDRESS']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6">該当する顧客が見つかりませんでした。</td></tr>';
                    }
                }
            ?>
        </table>
        <a href="customer.php" class="btn btn-secondary">戻る</a>
        </div>
    </main>
</body>
</html>