<?php
require_once '../../config.php'; 
require_once '../../accounting/includes/auto_journal_sales.php'; 

header("Content-Type: application/json; charset=utf-8");

$productId  = $_POST['product_id'] ?? null;
$quantity   = $_POST['order_quantity'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$employeeId = $_POST['employee_id'] ?? null;
$notes      = $_POST['notes'] ?? null;

// 変数の初期化
$totalPrice = 0;
$newStock = 0;
$message = "";

try {
    // バリデーション
    if (!$productId || !$quantity || !$customerId || !$employeeId) {
        throw new Exception("必須項目が不足しています。");
    }
    $quantity = (int)$quantity;
    if ($quantity <= 0) {
        throw new Exception("数量が不正です。");
    }

    // =========================================================
    // 【第1段階】販売データの登録処理（注文・在庫）
    // =========================================================
    $PDO->beginTransaction(); // 1つ目のトランザクション開始

    // 在庫・単価取得
    $stmt = $PDO->prepare("SELECT STOCK_QUANTITY, UNIT_SELLING_PRICE FROM STOCK S JOIN PRODUCT P ON S.PRODUCT_ID = P.PRODUCT_ID WHERE S.PRODUCT_ID = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) throw new Exception("商品が存在しません。");
    if ($row['STOCK_QUANTITY'] < $quantity) throw new Exception("在庫不足です。");

    $totalPrice = $row['UNIT_SELLING_PRICE'] * $quantity;
    $newStock = $row['STOCK_QUANTITY'] - $quantity;

    // 注文登録
    $insertOrder = $PDO->prepare("INSERT INTO `ORDER` (PURCHASE_ORDER_DATE, PRODUCT_ID, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID, NOTES) VALUES (NOW(), ?, ?, 1, ?, ?, ?)");
    if (!$insertOrder->execute([$productId, $customerId, $totalPrice, $employeeId, $notes])) {
        throw new Exception("ORDER登録失敗");
    }

    // 在庫更新
    $updateStock = $PDO->prepare("UPDATE STOCK SET STOCK_QUANTITY = ?, LAST_UPDATING_TIME = NOW() WHERE PRODUCT_ID = ?");
    $updateStock->execute([$newStock, $productId]);

    // ★ここで販売データを確定させる（コミット）
    $PDO->commit(); 

} catch (Exception $e) {
    // 第1段階でコケたらロールバックして終了
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    error_log("販売データ登録エラー: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

// =========================================================
// 【第2段階】会計データの登録処理（自動仕訳）
// =========================================================
// ※ここまで来ている時点で、販売データの登録は成功しています。

try {
    $PDO->beginTransaction(); // 2つ目のトランザクション開始

    // 顧客名の取得（摘要用）
    $stmtCustomer = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
    $stmtCustomer->execute([$customerId]);
    $customerName = $stmtCustomer->fetchColumn();
    $journalDescription = ($customerName ?: '不明な顧客') . " 売上";
    $todayDate = date('Y-m-d');

    // 自動仕訳関数の呼び出し
    registerSalesJournal($PDO, $todayDate, $totalPrice, $journalDescription);

    // 会計データを確定（コミット）
    $PDO->commit();
    
    $message = "注文を登録し、会計仕訳も作成しました。";

} catch (Exception $e) {
    // ★会計処理でエラーが出たら、会計側の処理だけロールバックする
    // （販売データはすでに第1段階でコミットされているので消えない）
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }
    
    error_log("会計連携エラー: " . $e->getMessage());
    // ユーザーには「注文はできたけど連携は失敗したよ」と伝える
    $message = "注文は登録されましたが、会計データの自動作成に失敗しました。（エラー: " . $e->getMessage() . "）";
}

// 最終結果を返す
echo json_encode([
    "success" => true,
    "message" => $message,
    "total_price" => $totalPrice,
    "new_stock" => $newStock
]);
?>