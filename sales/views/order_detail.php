<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php';

// 注文ID取得とバリデーション
$order_id = $_GET['id'] ?? '';

if (empty($order_id)) {
    echo '<p class="text-danger text-center mt-5">注文IDが指定されていません。</p>';
    exit;
}

try {
    // 注文情報取得SQL (SQLインジェクション対策済み)
    $sql = "SELECT 
                o.ORDER_ID,
                o.ORDER_DATETIME,
                o.TOTAL_AMOUNT,
                o.STATUS,
                c.NAME AS CUSTOMER_NAME
            FROM S_ORDER o
            LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID
            WHERE o.ORDER_ID = :order_id";

    $stmt = $PDO->prepare($sql);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // 注文が見つからなかった場合の処理
    if (!$order) {
        echo '<p class="text-danger text-center mt-5">指定された注文が見つかりません。</p>';
        exit;
    }

} catch (PDOException $e) {
    // データベースエラーが発生した場合の処理
    echo '<p class="text-danger text-center mt-5">データベースエラーが発生しました。</p>';
    // デバッグ用: echo '<p class="text-danger text-center mt-5">データベースエラー: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細 - <?php echo htmlspecialchars($order['ORDER_ID']); ?></title>
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

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">基本情報</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tbody>
                                <tr>
                                    <th class="col-md-3">注文ID</th>
                                    <td><?php echo htmlspecialchars($order['ORDER_ID']); ?></td>
                                </tr>
                                <tr>
                                    <th>注文日時</th>
                                    <td><?php echo htmlspecialchars($order['ORDER_DATETIME']); ?></td>
                                </tr>
                                <tr>
                                    <th>顧客名</th>
                                    <td><?php echo htmlspecialchars($order['CUSTOMER_NAME']); ?></td>
                                </tr>
                                <tr>
                                    <th>合計金額</th>
                                    <td>¥<?php echo number_format($order['TOTAL_AMOUNT']); ?></td>
                                </tr>
                                <tr>
                                    <th>支払い状況</th>
                                    <td><?php echo htmlspecialchars($order['STATUS']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-start mt-4 mb-4">
                    <a href="purchase.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 注文一覧に戻る
                    </a>
                    <a href="order_edit.php?id=<?php echo urlencode($order['ORDER_ID']); ?>" class="btn btn-primary ms-2">
                        <i class="bi bi-pencil-square"></i> 編集
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </body>
</html>