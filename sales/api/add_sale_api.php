<?php

require_once '../../config.php'; 

header('Content-Type: application/json');

// --- データの取得と整形 ---
$productId = $_POST['product_id'] ?? 0;         // どの商品か
$orderQuantity = $_POST['order_quantity'] ?? 0; // 数量
$customerId = $_POST['customer_id'] ?? 0;       // 顧客ID (ORDER_TARGET_ID として使用)
// $notes = $_POST['notes'] ?? '';            // ★ スキーマにNOTESカラムがないため削除

// 担当者ID (EMPLOYEE_ID) - ログインユーザーのIDを想定し、ここでは仮に 1 を設定
$employeeId = 1; 

// --- 入力値の基本チェックと数値型への変換 ---
$productId = filter_var($productId, FILTER_VALIDATE_INT);
$orderQuantity = filter_var($orderQuantity, FILTER_VALIDATE_INT);
$customerId = filter_var($customerId, FILTER_VALIDATE_INT);
$employeeId = filter_var($employeeId, FILTER_VALIDATE_INT); 

if ($productId === false || $orderQuantity === false || $customerId === false || $employeeId === false || 
    $productId <= 0 || $orderQuantity <= 0 || $customerId <= 0 || $employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => '商品ID、数量、顧客ID、担当者IDのいずれかが不正です。全て1以上の整数を入力してください。']);
    exit;
}

$PDO->beginTransaction();

try {
    // 1. 商品の単価を取得 (合計金額算出のため)
    $stmtProduct = $PDO->prepare("SELECT UNIT_SELLING_PRICE FROM PRODUCT WHERE PRODUCT_ID = :product_id");
    $stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品IDが見つかりませんでした。']);
        exit;
    }

    $unitPrice = $product['UNIT_SELLING_PRICE']; 
    // 合計金額の計算
    $totalAmount = $unitPrice * $orderQuantity; 
    $currentDateTime = date('Y-m-d H:i:s');

    // 2. 売り上げを ORDER テーブルに挿入 (ORDER_FLAG=1)
    // ★ ORDERテーブルのスキーマに合わせて、NOTES、CREATED_AT, UPDATED_ATを削除
    $stmtOrder = $PDO->prepare(
        "INSERT INTO `ORDER` (
            PURCHASE_ORDER_DATE, 
            ORDER_TARGET_ID, 
            ORDER_FLAG, 
            PRICE, 
            EMPLOYEE_ID
        ) 
        VALUES (
            :order_date, 
            :target_id, 
            1, 
            :price, 
            :employee_id
        )"
    );

    // バインド処理: データ型を明示的に指定
    $stmtOrder->bindParam(':order_date', $currentDateTime);
    $stmtOrder->bindParam(':target_id', $customerId, PDO::PARAM_INT);       // ORDER_TARGET_ID (int(11))
    $stmtOrder->bindParam(':price', $totalAmount, PDO::PARAM_INT);          // PRICE (int(11))
    $stmtOrder->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);     // EMPLOYEE_ID (bigint(20))

    $stmtOrder->execute();
    
    $orderId = $PDO->lastInsertId();
    
    // コミット
    $PDO->commit();

    // 成功レスポンスを返す
    echo json_encode(['success' => true, 'message' => '注文データ（ORDER_ID: ' . $orderId . '）が正常に登録されました。']);

} catch (PDOException $e) {
    // エラーが発生したらロールバック
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("注文登録エラー: " . $e->getMessage());
    // データベースエラーが発生した場合、詳細を返す
    echo json_encode(['success' => false, 'message' => 'データベースエラーが発生しました。SQLエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("システムエラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '予期せぬシステムエラーが発生しました。']);
}

?>