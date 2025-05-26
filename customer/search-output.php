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
    <div class="container mt-4">
        <h3>検索結果</h3>
        <table class="table table-hover">
            <tr><th>顧客ID</th><th>氏名</th><th>電話番号</th><th>メールアドレス</th><th>郵便番号</th><th>住所</th></tr>
            <?php
                $customerid = $_POST['customerid'] ?? '';

                if ($customerid === '') {
                    echo '<tr><td colspan="6">顧客IDを入力してください。</td></tr>';
                } else {
                    $stmt = $PDO->prepare('SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = ?');
                    $stmt->execute([$customerid]);
                    $row = $stmt->fetch();

                    if ($row) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['CUSTOMER_ID']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['NAME']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['CELL_NUMBER']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['MAIL']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['POST_CODE']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['ADDRESS']) . '</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr><td colspan="6">該当する顧客が見つかりませんでした。</td></tr>';
                    }
                }
            ?>
        </table>
        <a href="customer.php" class="btn btn-secondary">戻る</a>
    </div>
</body>
</html>
