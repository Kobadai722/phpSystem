<?php

// DB接続設定
require_once __DIR__ . '/../../db_connect.php';

// クエリパラメータの確認
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red; text-align:center; margin-top:20px;'>注文IDが指定されていません。</p>";
    exit;
}

$order_id = intval($_GET['id']);

// 注文情報の取得
$sql_order = "
    SELECT 
        o.ORDER_ID,
        o.ORDER_DATETIME,
        o.TOTAL_AMOUNT,
        o.STATUS,
        c.CUSTOMER_NAME
    FROM S_ORDERS o
    INNER JOIN CUSTOMERS c ON o.CUSTOMER_ID = c.CUSTOMER_ID
    WHERE o.ORDER_ID = ?
";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result_order = $stmt->get_result();

if ($result_order->num_rows === 0) {
    echo "<p style='color:red; text-align:center; margin-top:20px;'>注文が見つかりません。</p>";
    exit;
}

$order = $result_order->fetch_assoc();

// 注文詳細（商品ごと）の取得
$sql_items = "
    SELECT 
        i.ORDER_ITEM_ID,
        p.PRODUCT_NAME,
        i.UNIT_PRICE,
        i.QUANTITY,
        i.SUBTOTAL
    FROM S_ORDER_ITEMS i
    INNER JOIN PRODUCT p ON i.PRODUCT_ID = p.PRODUCT_ID
    WHERE i.ORDER_ID = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文詳細 - 注文番号 <?php echo htmlspecialchars($order['ORDER_ID']); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">注文詳細</h2>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            注文情報
        </div>
        <div class="card-body">
            <p><strong>注文ID:</strong> <?php echo htmlspecialchars($order['ORDER_ID']); ?></p>
            <p><strong>顧客名:</strong> <?php echo htmlspecialchars($order['CUSTOMER_NAME']); ?></p>
            <p><strong>注文日時:</strong> <?php echo htmlspecialchars($order['ORDER_DATETIME']); ?></p>
            <p><strong>合計金額:</strong> ¥<?php echo number_format($order['TOTAL_AMOUNT']); ?></p>
            <p><strong>ステータス:</strong> <?php echo htmlspecialchars($order['STATUS']); ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            注文商品一覧
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_items->num_rows > 0): ?>
                    <?php while ($item = $result_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['PRODUCT_NAME']); ?></td>
                            <td>¥<?php echo number_format($item['UNIT_PRICE']); ?></td>
                            <td><?php echo htmlspecialchars($item['QUANTITY']); ?></td>
                            <td>¥<?php echo number_format($item['SUBTOTAL']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">商品が登録されていません。</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mt-3">
        <a href="orders.php" class="btn btn-outline-secondary">注文一覧に戻る</a>
    </div>
</div>
</body>
</html>
