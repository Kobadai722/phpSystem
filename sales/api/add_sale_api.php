<?php
// add_sale_api.php (最終決定版 - BIGINT対応済み)
require_once '../../config.php';
header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;

try {
    // 必須チェック
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    // 数量は必ず整数として扱う
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        throw new Exception("数量が不正です。");
    }

    // トランザクション開始
    $PDO->beginTransaction();

    // 1. 在庫確認と同時に、単価を取得 (FOR UPDATEでロックをかける)
    // P.UNIT_SELLING_PRICEがint型の場合も、計算のため文字列として取得
    $stmt = $PDO->prepare("
        SELECT S.STOCK_QUANTITY, P.UNIT_SELLING_PRICE 
        FROM STOCK S
        JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID 
        WHERE S.PRODUCT_ID = ? FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 

    if (!$dataRow) {
        throw new Exception("該当する商品または在庫が存在しません。");
    }

    $stockQty = (int)$dataRow['STOCK_QUANTITY'];
    
    // 単価を文字列/数値で取得し、巨大な数値でもPHP内で正確に扱う
    // ※ PHP 7.1以降のint型は64bit環境でBIGINTに対応していますが、
    // 確実な計算のため、必要に応じてBC Mathライブラリを使用するのが理想です。
    // ここでは、int型にキャストせずに取得し、PHPの内部処理に任せます。
    $unitPrice = $dataRow['UNIT_SELLING_PRICE']; 
    
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }
    
    // 2. 合計金額を計算する
    // PHPは大きな数値も自動でfloat/stringとして処理しようとしますが、
    // データベースのカラムをBIGINTにすることで、数値の整合性を高めます。
    $totalPrice = $unitPrice * $quantity; 
    
    $orderFlag  = 1; // 通常注文

    // 3. ORDER登録
    // ORDERテーブルのPRICEカラムはBIGINT(20)に修正済みを前提
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER` 
            (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES
            (NOW(), ?, ?, ?, ?)
    ");
    
    // バインド変数: $customerId, $orderFlag, $totalPrice, $employeeId
    if (!$insertOrder->execute([$customerId, $orderFlag, $totalPrice, $employeeId])) {
        $error = $insertOrder->errorInfo();
        // 外部キー制約、データ型不一致など、より詳細なエラーをログに残す
        error_log("ORDER INSERT FAILED SQLSTATE: " . $error[0] . ", ERROR CODE: " . $error[1] . ", MESSAGE: " . $error[2]);
        throw new Exception("ORDER登録失敗: データベースエラーが発生しました。");
    }
    
    // 4. STOCK更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("UPDATE STOCK SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() WHERE PRODUCT_ID = ?");
    if (!$updateStock->execute([$newStock, $productId])) {
        $error = $updateStock->errorInfo();
        throw new Exception("在庫更新失敗: " . implode(", ", $error));
    }

    // コミット
    $PDO->commit();

    echo json_encode([
        "success" => true,
        "message" => "注文を登録しました。",
        "total_price" => $totalPrice, 
        "new_stock" => $newStock
    ]);
    exit;

} catch (Exception $e) {
    if ($PDO->inTransaction()) $PDO->rollBack();
    // エラー発生時のPOSTデータは既にerror_logで記録されているため、ここではメッセージのみ
    $logMessage = "注文登録エラー: " . $e->getMessage() . " / POSTデータ: " . print_r($_POST,true);
    error_log($logMessage);

    echo json_encode([
        "success" => false,
        "message" => "登録に失敗しました: " . $e->getMessage()
    ]);
    exit;
}