<?php

require_once '../../config.php'; // データベース接続設定ファイルを読み込む (パスは環境に合わせて調整してください)

header('Content-Type: application/json'); // JSON形式でレスポンスを返すことを指定

// order_id が指定されているか確認
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => '注文IDが指定されていません。']);
    exit;
}

$order_id = $_GET['id']; // GETパラメータから注文IDを取得

try {
    $PDO->beginTransaction(); // トランザクションを開始

    // 1. 注文基本情報と顧客情報を取得
    $orderSql = "SELECT
                    so.order_id,
                    so.order_datetime,
                    so.customer_id,
                    c.customer_name,
                    c.phone_number AS customer_phone,
                    c.email_address AS customer_email,
                    so.total_amount,
                    so.status,
                    so.notes,
                    so.employee_id
                 FROM
                    S_ORDER so
                 LEFT JOIN
                    CUSTOMER c ON so.customer_id = c.customer_id
                 WHERE
                    so.order_id = :order_id";
    $orderStmt = $PDO->prepare($orderSql);
    $orderStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT); // order_idはINT型
    $orderStmt->execute();
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => '指定された注文が見つかりません。']);
        $PDO->rollBack(); // 念のためロールバック
        exit;
    }

    // 2. 注文商品情報を取得
    $itemsSql = "SELECT
                    soi.order_item_id,
                    soi.product_id,
                    p.product_name,
                    soi.unit_price,
                    soi.quantity,
                    soi.subtotal
                 FROM
                    S_ORDER_ITEM soi
                 LEFT JOIN
                    PRODUCT p ON soi.product_id = p.product_id
                 WHERE
                    soi.order_id = :order_id
                 ORDER BY
                    soi.order_item_id ASC";
    $itemsStmt = $PDO->prepare($itemsSql);
    $itemsStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $itemsStmt->execute();
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. 顧客の配送先情報は、現状CUSTOMERテーブルから直接取得するか、
    // あるいは注文時に記録された配送先情報（ER図にはないが、通常はORDERテーブルに持つか、別の配送先テーブルを持つ）
    // 今回のスキーマではCUSTOMERSテーブルの住所・電話番号をそのまま配送先情報と仮定します。
    // もし配送先が顧客情報と異なる場合があるなら、S_ORDERテーブルに配送先住所などのカラムを追加する必要があります。
    // ここでは、顧客情報を配送先情報として重複して出力します。
    $shippingInfo = [
        'shipping_name' => $order['customer_name'],
        'shipping_address' => '〒XXX-XXXX 〇〇県〇〇市〇〇区... (顧客情報から仮定)', // CUSTOMERテーブルに住所カラムがあればそれを使用
        'shipping_phone' => $order['customer_phone']
    ];

    // 4. マスタデータ（支払い状況、配送状況）
    // 現状S_ORDERテーブルのstatusカラムがVARCHAR(50)型で直接ステータス名を持っているため、
    // ドロップダウンリスト用に想定される選択肢をハードコードで提供します。
    // 実際には別途マスタテーブルから取得するのが望ましいです。
    $paymentStatuses = [
        ['id' => '未払い', 'name' => '未払い'],
        ['id' => '支払い済み', 'name' => '支払い済み'],
        ['id' => '一部支払い', 'name' => '一部支払い'],
        ['id' => '返金済み', 'name' => '返金済み']
    ];
    $deliveryStatuses = [
        ['id' => '未発送', 'name' => '未発送'],
        ['id' => '発送済み', 'name' => '発送済み'],
        ['id' => '配達完了', 'name' => '配達完了'],
        ['id' => 'キャンセル済み', 'name' => 'キャンセル済み']
    ];
    
    // PRODUCTマスタデータも、商品IDから商品名を取得するためにクライアントサイドで利用する可能性があるので提供
    $productsSql = "SELECT product_id, product_name, unit_selling_price FROM PRODUCT"; //
    $productsStmt = $PDO->prepare($productsSql);
    $productsStmt->execute();
    $productsRaw = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];
    foreach ($productsRaw as $p) {
        $products[$p['product_id']] = [
            'product_name' => $p['product_name'],
            'unit_selling_price' => $p['unit_selling_price']
        ];
    }


    $PDO->commit(); // トランザクションをコミット

    // 全ての情報をまとめる
    $response = [
        'order' => $order,
        'shipping_info' => $shippingInfo,
        'order_items' => $orderItems,
        'master_data' => [
            'payment_statuses' => $paymentStatuses,
            'delivery_statuses' => $deliveryStatuses,
            'products' => $products
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    $PDO->rollBack(); // エラー時はロールバック
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    $PDO->rollBack(); // その他のエラーもロールバック
    http_response_code(500);
    echo json_encode(['error' => 'サーバーエラー: ' . $e->getMessage()]);
}

?>