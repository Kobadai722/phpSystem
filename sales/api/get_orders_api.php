<?php

require_once '../../config.php'; // データベース接続設定ファイルを読み込む

header('Content-Type: application/json'); // JSON形式でレスポンスを返すことを指定

try {
    // 検索・フィルタリングパラメータを取得
    $orderId = $_GET['orderId'] ?? '';
    $customerName = $_GET['customerName'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';
    $deliveryStatus = $_GET['deliveryStatus'] ?? ''; // 現状、statusカラムを共有しているため、paymentStatusと同様に扱う

    // SQLクエリを構築
    $sql = "SELECT
                so.order_id,
                so.order_datetime,
                c.customer_name,
                so.total_amount,
                so.status
            FROM
                S_ORDER so
            LEFT JOIN
                CUSTOMER c ON so.customer_id = c.customer_id
            WHERE 1=1"; // 常にtrueとなる条件を最初に置くことで、WHERE句の追加を容易にする

    $params = [];

    // 注文IDでフィルタリング
    if (!empty($orderId)) {
        $sql .= " AND so.order_id LIKE :orderId";
        $params[':orderId'] = '%' . $orderId . '%';
    }

    // 顧客名でフィルタリング
    if (!empty($customerName)) {
        $sql .= " AND c.customer_name LIKE :customerName";
        $params[':customerName'] = '%' . $customerName . '%';
    }

    // 支払い状況でフィルタリング
    if (!empty($paymentStatus)) {
        $sql .= " AND so.status = :paymentStatus";
        $params[':paymentStatus'] = $paymentStatus;
    }

    // 配送状況でフィルタリング (現状、statusカラムを支払い状況と共有しているため同じ条件を適用)
    if (!empty($deliveryStatus)) {
        $sql .= " AND so.status = :deliveryStatus";
        $params[':deliveryStatus'] = $deliveryStatus;
    }

    $sql .= " ORDER BY so.order_datetime DESC"; // 最新の注文から表示

    $stmt = $PDO->prepare($sql);

    // パラメータをバインド
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (PDOException $e) {
    error_log("Database error in get_orders_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Unexpected error in get_orders_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました: ' . $e->getMessage()
    ]);
}

?>