<?php

require_once '../config.php'; // DB接続設定などを読み込みます。このファイルでPDOインスタンスが$PDOとして利用可能と仮定します。

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから必要な情報を取得
// null合体演算子 (??) で未定義の場合に空文字列を設定
$productName = $_POST['product_name'] ?? '';
$unitPrice = $_POST['unit_price'] ?? '';
$initialStock = $_POST['initial_stock'] ?? '';
// product_category は選択された商品区分IDとして受け取る
$productCategoryId = $_POST['product_category'] ?? '';

// 入力値のサーバーサイドバリデーション
$errors = [];

if (empty($productName)) {
    $errors[] = '商品名は必須です。';
} elseif (mb_strlen($productName, 'UTF-8') > 20) {
    $errors[] = '商品名は20文字以内で入力してください。';
}

// filter_varを使用して単価が0以上の整数であるか検証
if (filter_var($unitPrice, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
    $errors[] = '単価は0以上の整数で入力してください。';
}

// filter_varを使用して初期在庫が0以上の整数であるか検証
if (filter_var($initialStock, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
    $errors[] = '初期在庫は0以上の整数で入力してください。';
}

// 商品区分IDが数値であり、かつ有効なIDであるか検証
// PRODUCT_KUBUN_IDは通常1以上の整数と仮定
if (filter_var($productCategoryId, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
    $errors[] = '有効な商品区分が選択されていません。';
}

// バリデーションエラーがある場合は、エラーメッセージをまとめてJSONで返す
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]); // クライアント側でHTMLとして表示するため<br>
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();

    // 1. PRODUCT_KUBUN_IDがPRODUCT_KUBUNテーブルに実在するか確認
    $stmtKubunCheck = $PDO->prepare("SELECT COUNT(*) FROM PRODUCT_KUBUN WHERE PRODUCT_KUBUN_ID = :product_kubun_id");
    $stmtKubunCheck->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtKubunCheck->execute();
    if ($stmtKubunCheck->fetchColumn() === 0) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品区分IDは存在しません。データベースを確認してください。']);
        exit;
    }

    // 2. PRODUCTテーブルに商品情報を挿入
    // REORDER_POINTとORDER_NUMBERは現在のフォーム入力には含まれないのでNULLを挿入
    $stmtProduct = $PDO->prepare(
        "INSERT INTO PRODUCT (PRODUCT_NAME, UNIT_PRICE, PRODUCT_KUBUN_ID, REORDER_POINT, ORDER_NUMBER) 
         VALUES (:product_name, :unit_price, :product_kubun_id, NULL, NULL)"
    );
    $stmtProduct->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtProduct->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtProduct->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT); // IDを直接使用
    $stmtProduct->execute();

    // 挿入された商品のPRODUCT_IDを取得
    $newProductId = $PDO->lastInsertId();

    // 3. STOCKテーブルに初期在庫を挿入
    $stmtStock = $PDO->prepare(
        "INSERT INTO STOCK (PRODUCT_ID, STOCK_QUANTITY, LAST_UPDATING_TIME) 
         VALUES (:product_id, :stock_quantity, NOW())" // NOW() で現在時刻を記録
    );
    $stmtStock->bindParam(':product_id', $newProductId, PDO::PARAM_INT);
    $stmtStock->bindParam(':stock_quantity', $initialStock, PDO::PARAM_INT);
    $stmtStock->execute();

    // 全ての操作が成功したらコミット
    $PDO->commit();
    echo json_encode(['success' => true, 'message' => '商品が正常に追加されました。']);

} catch (PDOException $e) {
    $PDO->rollBack();
    error_log("Database error in add_api.php: " . $e->getMessage()); 
    echo json_encode([
        'success' => false,
        'message' => 'データベース処理中にエラーが発生しました。時間をおいて再度お試しください。'
    ]);
} catch (Exception $e) {
    $PDO->rollBack();
    error_log("Unexpected error in add_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました。システム管理者にお問い合わせください。'
    ]);
}

?>