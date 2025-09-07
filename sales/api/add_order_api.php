<?php

require_once '../../config.php'; // DB接続設定などを読み込みます。このファイルでPDOインスタンスが$PDOとして利用可能と仮定します。

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから必要な情報を取得
$productId = $_POST['product_id'] ?? ''; // 商品ID
$orderQuantity = $_POST['order_quantity'] ?? ''; // 注文数量
// 必要に応じて、顧客IDや従業員IDもフォームから受け取ります
// $customerId = $_POST['customer_id'] ?? '';
// $employeeId = $_POST['employee_id'] ?? '';

// 入力値のバリデーション
$errors = [];

// 商品IDが正しく指定されているかチェック
if (filter_var($productId, FILTER_VALIDATE_INT) === false || $productId <= 0) {
    $errors[] = '商品が正しく指定されていません。';
}

// 注文数量が1以上の整数かチェック
if (filter_var($orderQuantity, FILTER_VALIDATE_INT) === false || $orderQuantity < 1) {
    $errors[] = '注文数量は1以上の整数で入力してください。';
}

// バリデーションエラーがある場合は、エラーメッセージをまとめて返す
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();

    // 1. 商品IDがPRODUCTテーブルに実在するか確認
    $stmtProductCheck = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :product_id");
    $stmtProductCheck->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtProductCheck->execute();
    $product = $stmtProductCheck->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品は存在しません。']);
        exit;
    }

    $unitPrice = $product['UNIT_SELLING_PRICE']; // 商品の単価を取得
    $totalAmount = $unitPrice * $orderQuantity; // 合計金額を計算

    // 2. S_ORDERテーブルに注文ヘッダー情報を挿入
    $stmtOrder = $PDO->prepare(
        "INSERT INTO S_ORDER (ORDER_DATETIME, TOTAL_AMOUNT, STATUS) 
        VALUES (NOW(), :total_amount, '注文受付')"
    );
    $stmtOrder->bindParam(':total_amount', $totalAmount, PDO::PARAM_INT);
    $stmtOrder->execute();
    
    // 挿入された注文のORDER_IDを取得
    $newOrderId = $PDO->lastInsertId();

    // 3. S_ORDER_DETAILテーブルに注文明細情報を挿入 (S_ORDER_DETAILテーブルが存在すると仮定)
    // S_ORDER_DETAIL (ORDER_ID, PRODUCT_ID, ORDER_QUANTITY, UNIT_PRICE)
    $stmtOrderDetail = $PDO->prepare(
        "INSERT INTO S_ORDER_DETAIL (ORDER_ID, PRODUCT_ID, ORDER_QUANTITY, UNIT_PRICE)
        VALUES (:order_id, :product_id, :order_quantity, :unit_price)"
    );
    $stmtOrderDetail->bindParam(':order_id', $newOrderId, PDO::PARAM_INT);
    $stmtOrderDetail->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtOrderDetail->bindParam(':order_quantity', $orderQuantity, PDO::PARAM_INT);
    $stmtOrderDetail->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT); // 明細にも単価を記録
    $stmtOrderDetail->execute();

    // 全ての操作が成功したらコミット
    $PDO->commit();
    echo json_encode([
        'success' => true, 
        'message' => '注文が正常に追加されました。', 
        'order_id' => $newOrderId
    ]);

} catch (PDOException $e) {
    $PDO->rollBack();
    error_log("Database error in add_order_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。時間をおいて再度お試しください。'
    ]);
} catch (Exception $e) {
    $PDO->rollBack();
    error_log("Unexpected error in add_order_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました。システム管理者にお問い合わせください。'
    ]);
}

?>