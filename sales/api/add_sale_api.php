<?php

// データベース設定ファイルの読み込み
require_once '../../../config.php'; 

// JSONレスポンスを設定
header('Content-Type: application/json');

// POSTデータの取得
// フォームからの名前 'product_id' を使用
$productId = $_POST['product_id'] ?? 0; 
// フォームからの名前 'sale_quantity' を使用
$saleQuantity = $_POST['sale_quantity'] ?? 0; 
// フォームからの名前 'customer_id' を使用 (ORDER_TARGET_ID として使用)
$orderTargetId = $_POST['customer_id'] ?? 0; 
// フォームからの名前 'notes' を使用
$notes = $_POST['notes'] ?? ''; 

// 新規に追加するデータ（ここでは固定値またはログインユーザーIDを想定）
// 担当者ID (EMPLOYEE_ID) は、ここでは仮に 1 を設定します。
// 実際のシステムでは、ログインしているユーザーのIDを使用する必要があります。
$employeeId = 1; 

// 入力値の基本チェック
if (empty($productId) || empty($saleQuantity) || empty($orderTargetId) || $saleQuantity <= 0 || $orderTargetId <= 0) {
    echo json_encode(['success' => false, 'message' => '商品、数量、顧客IDが不正です。全て入力されているか、数値が正しく設定されているか確認してください。']);
    exit;
}

// トランザクション開始
$PDO->beginTransaction();

try {
    // 1. 商品の単価を取得 (UNIT_SELLING_PRICE: 売上単価)
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
    // 合計金額の計算（ORDERテーブルのPRICEカラムに対応）
    $totalAmount = $unitPrice * $saleQuantity; 
    $currentDateTime = date('Y-m-d H:i:s');

    // 2. 売り上げを ORDER テーブルに挿入
    // ORDER_FLAG=1 を「売上」として登録
    // ORDER_TARGET_ID には顧客ID ($orderTargetId) を使用
    // PRICE には合計金額 ($totalAmount) を使用
    $stmtOrder = $PDO->prepare(
        "INSERT INTO `ORDER` (
            PURCHASE_ORDER_DATE, 
            ORDER_TARGET_ID, 
            ORDER_FLAG, 
            PRICE, 
            EMPLOYEE_ID,
            NOTES
        ) 
        VALUES (
            :order_date, 
            :target_id, 
            1, 
            :price, 
            :employee_id,
            :notes
        )"
    );

    $stmtOrder->bindParam(':order_date', $currentDateTime);
    $stmtOrder->bindParam(':target_id', $orderTargetId, PDO::PARAM_INT); // 顧客ID
    $stmtOrder->bindParam(':price', $totalAmount, PDO::PARAM_INT);      // 合計金額
    $stmtOrder->bindParam(':employee_id', $employeeId, PDO::PARAM_INT); // 担当者ID
    $stmtOrder->bindParam(':notes', $notes);                            // 備考

    $stmtOrder->execute();
    
    // 挿入された ORDER_ID (売上ID) を取得
    $orderId = $PDO->lastInsertId();

    // 3. 注文明細 (S_ORDER_DETAIL) の挿入
    // ※ 提示されたスキーマにはS_ORDER_DETAILに相当するテーブルの構造がないため、
    // 前回の実装に基づいて S_ORDER_DETAIL に挿入するロジックを保持します。
    // スキーマが不明なため、このテーブル名は S_ORDER_DETAIL のままにしておきます。
    /*
    $stmtDetail = $PDO->prepare(
        "INSERT INTO S_ORDER_DETAIL (ORDER_ID, PRODUCT_ID, QUANTITY, UNIT_PRICE, CREATED_AT, UPDATED_AT)
        VALUES (:order_id, :product_id, :quantity, :unit_price, :created_at, :updated_at)"
    );

    $stmtDetail->bindParam(':order_id', $orderId, PDO::PARAM_INT);
    $stmtDetail->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtDetail->bindParam(':quantity', $saleQuantity, PDO::PARAM_INT);
    $stmtDetail->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtDetail->bindParam(':created_at', $currentDateTime);
    $stmtDetail->bindParam(':updated_at', $currentDateTime);
    
    $stmtDetail->execute();
    */

    // 在庫更新などの追加処理は省略（必要に応じて追加してください）
    
    // 全て成功したらコミット
    $PDO->commit();

    // メッセージを修正
    echo json_encode(['success' => true, 'message' => '売上データ（ORDER_ID: ' . $orderId . '）が正常に登録されました。（ORDERテーブルに格納）']);

} catch (PDOException $e) {
    // エラーが発生したらロールバック
    $PDO->rollBack();
    error_log("売上登録エラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '売上の登録中にデータベースエラーが発生しました。（詳細: ' . $e->getMessage() . '）']);
} catch (Exception $e) {
    $PDO->rollBack();
    error_log("システムエラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '予期せぬシステムエラーが発生しました。']);
}

?>