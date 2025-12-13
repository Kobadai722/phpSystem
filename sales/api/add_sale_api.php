<?php
require_once '../../config.php';
// ★追加: 会計システムの自動仕訳機能を読み込み
require_once '../../accounting/includes/auto_journal_sales.php'; 

header("Content-Type: application/json; charset=utf-8");

// POSTパラメータ取得
$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;
$notes      = $_POST['notes'] ?? null;

// レスポンス用の変数を初期化
$totalPrice = 0;
$newStock = 0;
$message = "";

try {

    // 必須チェック
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }

    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        throw new Exception("数量が不正です。");
    }

    $PDO->beginTransaction(); // 1つ目のトランザクション開始

    // 商品の在庫 & 単価取得（FOR UPDATE でロック）
    $stmt = $PDO->prepare("
        SELECT S.STOCK_QUANTITY, P.UNIT_SELLING_PRICE
        FROM STOCK S
        JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID
        WHERE S.PRODUCT_ID = ?
        FOR UPDATE
    ");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("商品または在庫が存在しません。");
    }

    $stockQty = (int)$row['STOCK_QUANTITY'];
    $unitPrice = $row['UNIT_SELLING_PRICE'];

    // 在庫不足チェック
    if ($stockQty < $quantity) {
        throw new Exception("在庫不足です。現在の在庫: {$stockQty}");
    }

    // 合計金額
    $totalPrice = $unitPrice * $quantity;
    $orderFlag = 1;

    // ORDER登録
    $insertOrder = $PDO->prepare("
        INSERT INTO `ORDER`
        (PURCHASE_ORDER_DATE, PRODUCT_ID, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID, NOTES)
        VALUES
        (NOW(), ?, ?, ?, ?, ?, ?)
    ");

    if (!$insertOrder->execute([
        $productId,
        $customerId,
        $orderFlag,
        $totalPrice,
        $employeeId,
        $notes
    ])) {
        throw new Exception("ORDER登録失敗");
    }

    // 在庫更新
    $newStock = $stockQty - $quantity;
    $updateStock = $PDO->prepare("
        UPDATE STOCK 
        SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() 
        WHERE PRODUCT_ID = ?
    ");
    $updateStock->execute([$newStock, $productId]);


    $PDO->commit();

} catch (Exception $e) {
    // 販売処理自体が失敗した場合はロールバックして終了
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("注文登録エラー: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}

//会計データの登録処理（自動仕訳連携）

try {
    $PDO->beginTransaction(); // 2つ目のトランザクション開始

    // 1. 摘要用に顧客名を取得
    $stmtCustomer = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
    $stmtCustomer->execute([$customerId]);
    $customerName = $stmtCustomer->fetchColumn();
    $journalDescription = ($customerName ?: '不明な顧客') . " 売上";
    
    $todayDate = date('Y-m-d');

    // 2. 自動仕訳関数の実行（accounting/includes/auto_journal_sales.php）
    // (引数: PDOオブジェクト, 日付, 金額, 摘要)
    registerSalesJournal($PDO, $todayDate, $totalPrice, $journalDescription);

    // 会計データを確定
    $PDO->commit();
    require_once '../../accounting/includes/batch_process_sales.php';
    runSalesBatchProcess($PDO);
    $message = "注文を登録し、会計仕訳も自動作成しました。";

} catch (Exception $e) {
    // ★会計連携のみ失敗した場合は、会計側だけロールバックしつつ、成功メッセージ（警告付き）を返す
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("会計連携エラー: " . $e->getMessage());
    // ユーザーには「注文は成功したが、連携は失敗した」と伝える
    $message = "注文は完了しましたが、会計データの作成に失敗しました。（エラー: " . $e->getMessage() . "）";
}

// 最終結果を返す
echo json_encode([
    "success" => true,
    "message" => $message,
    "total_price" => $totalPrice,
    "new_stock" => $newStock
]);
?>