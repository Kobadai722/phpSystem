<?php

require_once '../config.php'; // DB接続設定などを読み込みます。このファイルでPDOインスタンスが$PDOとして利用可能と仮定します。

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから必要な情報を取得
// null合体演算子 (??) で未定義の場合に空文字列を設定
$productName = $_POST['product_name'] ?? '';
$unitPrice = $_POST['unit_price'] ?? '';
$initialStock = $_POST['initial_stock'] ?? '';
// stock-register.php からは PRODUCT_KUBUN_ID が value として送られてくる
$productCategoryId = $_POST['product_category'] ?? ''; 

// 入力値のバリデーション
$errors = [];

if (empty($productName)) {
    $errors[] = '商品名は必須です。';
} elseif (mb_strlen($productName, 'UTF-8') > 20) { // PRODUCT.PRODUCT_NAMEがVARCHAR(20)なので20文字制限
    $errors[] = '商品名は20文字以内で入力してください。';
}

// filter_varを使用して整数として検証
// FILTER_VALIDATE_INTは非整数値や空文字列の場合falseを返す
if (filter_var($unitPrice, FILTER_VALIDATE_INT) === false || $unitPrice < 0) {
    $errors[] = '単価は0以上の整数で入力してください。';
}

if (filter_var($initialStock, FILTER_VALIDATE_INT) === false || $initialStock < 0) {
    $errors[] = '初期在庫は0以上の整数で入力してください。';
}

// ここで商品区分IDが正しく数値であるか、かつ0より大きいか（有効なIDか）をチェック
if (filter_var($productCategoryId, FILTER_VALIDATE_INT) === false || $productCategoryId <= 0) {
    $errors[] = '商品区分が正しく指定されていません。';
}


// バリデーションエラーがある場合は、エラーメッセージをまとめて返す
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();

    // 1. PRODUCT_KUBUN_IDが実在するか確認 (今回は直接IDを受け取るので、IDの存在チェックを行う)
    // PRODUCT_KUBUN_NAME で検索するロジックを PRODUCT_KUBUN_ID で直接検索するロジックに変更
    $stmtKubunCheck = $PDO->prepare("SELECT COUNT(*) FROM PRODUCT_KUBUN WHERE PRODUCT_KUBUN_ID = :product_kubun_id");
    $stmtKubunCheck->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtKubunCheck->execute();
    if ($stmtKubunCheck->fetchColumn() === 0) { // 該当するIDが見つからなかった場合
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品区分IDは存在しません。']);
        exit;
    }

    // 2. PRODUCTテーブルに商品情報を挿入
    // REORDER_POINTとORDER_NUMBERは、ER図によるとnullableなので、現在のフォーム入力には含まれないと仮定しNULLを挿入
    $stmtProduct = $PDO->prepare(
        "INSERT INTO PRODUCT (PRODUCT_NAME, UNIT_PRICE, PRODUCT_KUBUN_ID, REORDER_POINT, ORDER_NUMBER) 
         VALUES (:product_name, :unit_price, :product_kubun_id, NULL, NULL)"
    );
    $stmtProduct->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtProduct->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtProduct->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT); // IDを直接使用
    $stmtProduct->execute();

    // 挿入された商品のPRODUCT_IDを取得
    // PDO::lastInsertId() は、PDO接続で最後に挿入された行のIDを返します
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
    echo json_encode(['success' => true, 'message' => '商品が正常に追加されました。', 'product_id' => $newProductId]);

} catch (PDOException $e) { // PDO関連のエラーをキャッチ
    $PDO->rollBack(); // エラー発生時はロールバック
    error_log("Database error in add_api.php: " . $e->getMessage()); // エラーをログに出力
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。時間をおいて再度お試しください。' // 本番では詳細なエラーメッセージは非表示
    ]);
} catch (Exception $e) { // その他のエラーをキャッチ
    $PDO->rollBack(); // エラー発生時はロールバック
    error_log("Unexpected error in add_api.php: " . $e->getMessage()); // エラーをログに出力
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました。システム管理者にお問い合わせください。'
    ]);
}

?>