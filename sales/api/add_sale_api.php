<?php

// データベース設定ファイルの読み込み
require_once '../../config.php'; 

// JSONレスポンスを設定
header('Content-Type: application/json');

// POSTデータの取得
$productId = $_POST['product_id'] ?? 0;
// フォームからの新しい名前 'sale_quantity' を使用
$saleQuantity = $_POST['sale_quantity'] ?? 0; 
$customerId = $_POST['customer_id'] ?? 0; // 純粋な顧客IDとして扱う
$notes = $_POST['notes'] ?? '';

// 入力値の基本チェック
if (empty($productId) || empty($saleQuantity) || empty($customerId) || $saleQuantity <= 0 || $customerId <= 0) {
    echo json_encode(['success' => false, 'message' => '商品、数量、または顧客IDが不正です。']);
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
    // 合計金額の計算
    $totalAmount = $unitPrice * $saleQuantity; 
    $currentDateTime = date('Y-m-d H:i:s');

    // 2. 売り上げを S_ORDER テーブルに挿入 (B2Cの売り上げとして処理)
    // 注意: 元のテーブル名 S_ORDER を使用していますが、論理的には 'SALE' の役割を果たす
    $stmtSale = $PDO->prepare(
        "INSERT INTO S_ORDER (ORDER_DATETIME, CUSTOMER_ID, TOTAL_AMOUNT, STATUS, NOTES, CREATED_AT, UPDATED_AT) 
        VALUES (:sale_datetime, :customer_id, :total_amount, '未払い', :notes, :created_at, :updated_at)"
    );

    $stmtSale->bindParam(':sale_datetime', $currentDateTime); // 売り上げ日時
    $stmtSale->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $stmtSale->bindParam(':total_amount', $totalAmount, PDO::PARAM_INT);
    $stmtSale->bindParam(':notes', $notes);
    $stmtSale->bindParam(':created_at', $currentDateTime);
    $stmtSale->bindParam(':updated_at', $currentDateTime);

    $stmtSale->execute();
    
    // 挿入された売り上げIDを取得（必要に応じて）
    $saleId = $PDO->lastInsertId();

    // 3. 売り上げ明細 (S_ORDER_DETAIL) の挿入
    $stmtDetail = $PDO->prepare(
        "INSERT INTO S_ORDER_DETAIL (ORDER_ID, PRODUCT_ID, QUANTITY, UNIT_PRICE, CREATED_AT, UPDATED_AT)
        VALUES (:order_id, :product_id, :quantity, :unit_price, :created_at, :updated_at)"
    );

    $stmtDetail->bindParam(':order_id', $saleId, PDO::PARAM_INT); // 挿入された売り上げID
    $stmtDetail->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtDetail->bindParam(':quantity', $saleQuantity, PDO::PARAM_INT); // 売り上げ数量
    $stmtDetail->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT); // 売上単価
    $stmtDetail->bindParam(':created_at', $currentDateTime);
    $stmtDetail->bindParam(':updated_at', $currentDateTime);
    
    $stmtDetail->execute();

    // 全て成功したらコミット
    $PDO->commit();

    echo json_encode(['success' => true, 'message' => '売り上げID: ' . $saleId . ' が正常に登録されました。']);

} catch (PDOException $e) {
    // エラーが発生したらロールバック
    $PDO->rollBack();
    error_log("売り上げ登録エラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '売り上げの登録中にエラーが発生しました。（詳細: ' . $e->getMessage() . '）']);
} catch (Exception $e) {
    $PDO->rollBack();
    error_log("システムエラー: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '予期せぬエラーが発生しました。']);
}

?>