<?php
// order_detail.php
header("Content-Type: text/html; charset=UTF-8");
mb_internal_encoding("UTF-8");

// DB接続（共通化ファイルを使用している場合はそれを include）
require_once '../../db_connect.php'; // ← 既存構成に合わせて変更してください

// GETパラメータで注文ID取得
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    die('注文IDが指定されていません。');
}

// 注文情報取得
$stmt = $pdo->prepare("
    SELECT 
        o.ORDER_ID,
        o.CUSTOMER_ID,
        c.CUSTOMER_NAME,
        o.ORDER_DATE,
        o.TOTAL_AMOUNT,
        o.PAYMENT_STATUS,
        o.DELIVERY_STATUS
    FROM S_ORDER o
    JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID
    WHERE o.ORDER_ID = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('指定された注文は存在しません。');
}

// 注文明細取得
$stmtItems = $pdo->prepare("
    SELECT 
        i.ORDER_ITEM_ID,
        p.PRODUCT_NAME,
        i.UNIT_PRICE,
        i.QUANTITY,
        i.SUBTOTAL
    FROM S_ORDER_ITEMS i
    JOIN PRODUCT p ON i.PRODUCT_ID = p.PRODUCT_ID
    WHERE i.ORDER_ID = ?
");
$stmtItems->execute([$order_id]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細 - 注文管理システム</title>
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
                <h1 class="mb-4">注文詳細（注文ID：<?= htmlspecialchars($order['ORDER_ID']) ?>）</h1>

                <!-- 注文情報カード -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold">注文情報</div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>顧客名：</strong> <?= htmlspecialchars($order['CUSTOMER_NAME']) ?></div>
                            <div class="col-md-6"><strong>注文日：</strong> <?= htmlspecialchars($order['ORDER_DATE']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>支払い状況：</strong> <?= htmlspecialchars($order['PAYMENT_STATUS'] ?? '未設定') ?></div>
                            <div class="col-md-6"><strong>配送状況：</strong> <?= htmlspecialchars($order['DELIVERY_STATUS'] ?? '未設定') ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><strong>合計金額：</strong> ¥<?= number_format($order['TOTAL_AMOUNT']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- 注文明細カード -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">注文明細</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>商品名</th>
                                        <th>単価</th>
                                        <th>数量</th>
                                        <th>小計</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($orderItems) > 0): ?>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['PRODUCT_NAME']) ?></td>
                                                <td>¥<?= number_format($item['UNIT_PRICE']) ?></td>
                                                <td><?= htmlspecialchars($item['QUANTITY']) ?></td>
                                                <td>¥<?= number_format($item['SUBTOTAL']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-muted">この注文には明細がありません。</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="order_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> 注文一覧へ戻る
                    </a>

                    <a href="order_item_add.php?order_id=<?= $order_id ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> 明細を追加
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
