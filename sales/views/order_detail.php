<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文管理システム - 注文詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include '../../header.php'; ?>
<main>
    <?php include '../includes/localNavigation.php'; ?>

    <section class="content">
        <div class="container-fluid">
            <h1 class="mb-4">注文詳細</h1>

            <?php
            require_once '../../config.php';

            if (!isset($_GET['id']) || empty($_GET['id'])) {
                echo '<div class="alert alert-danger">注文IDが指定されていません。</div>';
                exit;
            }

            $order_id = intval($_GET['id']);

            try {
                $stmt = $pdo->prepare("
                    SELECT o.id, o.order_date, c.name AS customer_name, o.total_amount, o.payment_status
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.id
                    WHERE o.id = :id
                ");
                $stmt->bindValue(':id', $order_id, PDO::PARAM_INT);
                $stmt->execute();
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$order) {
                    echo '<div class="alert alert-warning">指定された注文が見つかりません。</div>';
                    exit;
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">データベースエラー: ' . htmlspecialchars($e->getMessage()) . '</div>';
                exit;
            }
            ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>注文ID</th>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                        </tr>
                        <tr>
                            <th>注文日時</th>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                        </tr>
                        <tr>
                            <th>顧客名</th>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        </tr>
                        <tr>
                            <th>合計金額</th>
                            <td>¥<?= number_format($order['total_amount']) ?></td>
                        </tr>
                        <tr>
                            <th>支払い状況</th>
                            <td><?= htmlspecialchars($order['payment_status']) ?></td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="order_list.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 注文一覧に戻る
                        </a>
                        <a href="order_edit.php?id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
