<?php
require_once '../../config.php';

// --- GETパラメータから注文IDを取得 ---
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "注文IDが指定されていません。";
    exit;
}

// --- 注文情報と注文商品一覧を取得 ---
try {
    // 注文情報（顧客名など含む場合はJOIN）
    $stmtOrder = $PDO->prepare("
        SELECT 
            o.ORDER_ID,
            o.CUSTOMER_ID,
            c.CUSTOMER_NAME,
            o.ORDER_DATE,
            o.NOTES
        FROM `ORDER` o
        LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID
        WHERE o.ORDER_ID = :order_id
    ");
    $stmtOrder->execute([':order_id' => $order_id]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "指定された注文は存在しません。";
        exit;
    }

    // 注文詳細（商品一覧）
    $stmtItems = $PDO->prepare("
        SELECT 
            i.ORDER_ITEM_ID,
            i.PRODUCT_ID,
            p.PRODUCT_NAME,
            i.UNIT_PRICE,
            i.QUANTITY,
            i.SUBTOTAL
        FROM S_ORDER_ITEMS i
        JOIN PRODUCT p ON i.PRODUCT_ID = p.PRODUCT_ID
        WHERE i.ORDER_ID = :order_id
    ");
    $stmtItems->execute([':order_id' => $order_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データ取得エラー: " . htmlspecialchars($e->getMessage());
    exit;
}

// --- 合計金額を計算 ---
$totalAmount = array_sum(array_column($items, 'SUBTOTAL'));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文詳細（注文ID: <?= htmlspecialchars($order_id) ?>）</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">
                    <i class="bi bi-receipt me-2"></i>注文詳細（注文ID: <?= htmlspecialchars($order['ORDER_ID']) ?>）
                </h2>

                <!-- 注文情報 -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">注文情報</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>顧客ID：</strong><?= htmlspecialchars($order['CUSTOMER_ID']) ?></p>
                                <p><strong>顧客名：</strong><?= htmlspecialchars($order['CUSTOMER_NAME'] ?? '不明') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>注文日：</strong><?= htmlspecialchars($order['ORDER_DATE']) ?></p>
                                <p><strong>備考：</strong><?= nl2br(htmlspecialchars($order['NOTES'] ?? '（なし）')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 注文詳細テーブル -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">注文商品一覧</h5>
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>商品ID</th>
                                    <th>商品名</th>
                                    <th class="text-end">単価（円）</th>
                                    <th class="text-end">数量</th>
                                    <th class="text-end">小計（円）</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['PRODUCT_ID']) ?></td>
                                        <td><?= htmlspecialchars($item['PRODUCT_NAME']) ?></td>
                                        <td class="text-end"><?= number_format($item['UNIT_PRICE'], 2) ?></td>
                                        <td class="text-end"><?= htmlspecialchars($item['QUANTITY']) ?></td>
                                        <td class="text-end"><?= number_format($item['SUBTOTAL'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">合計金額</th>
                                    <th class="text-end"><?= number_format($totalAmount, 2) ?> 円</th>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="text-end mt-3">
                            <a href="order_management.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>戻る
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
