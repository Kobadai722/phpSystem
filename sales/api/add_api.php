<?php

require_once '../../config.php'; // DB接続設定などを読み込みます。このファイルでPDOインスタンスが$PDOとして利用可能と仮定します。

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから必要な情報を取得
// null合体演算子 (??) で未定義の場合に空文字列を設定
$productName = $_POST['product_name'] ?? ''; // 商品名
$unitPrice = $_POST['unit_price'] ?? ''; // 単価
$initialStock = $_POST['initial_stock'] ?? ''; // 初期在庫
// stock-register.php からは選択された商品区分ID（value属性）が送られてくるため、そのままIDとして受け取る
$productCategoryId = $_POST['product_category'] ?? ''; // 商品区分ID

// 入力値のバリデーション
$errors = [];

if (empty($productName)) { // 商品名が空の場合
    $errors[] = '商品名は必須です。';
} elseif (mb_strlen($productName, 'UTF-8') > 20) { // PRODUCT.PRODUCT_NAMEがVARCHAR(20)なので20文字制限
    $errors[] = '商品名は20文字以内で入力してください。';
}

// filter_varを使用して整数として検証
// FILTER_VALIDATE_INTは非整数値や空文字列の場合falseを返す
if (filter_var($unitPrice, FILTER_VALIDATE_INT) === false || $unitPrice < 0) { // 単価が0以上の整数でない場合
    $errors[] = '単価は0以上の整数で入力してください。';
}

if (filter_var($initialStock, FILTER_VALIDATE_INT) === false || $initialStock < 0) { // 初期在庫が0以上の整数でない場合
    $errors[] = '初期在庫は0以上の整数で入力してください。';
}

// productCategoryIdが数値として正しいか、かつ0より大きいか（有効なIDか）をチェック
// PRODUCT_KUBUN_IDは通常1以上の整数と仮定
if (filter_var($productCategoryId, FILTER_VALIDATE_INT) === false || $productCategoryId <= 0) { // 商品区分が正しく指定されていない場合
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

    // 1. 受け取ったPRODUCT_KUBUN_IDがPRODUCT_KUBUNテーブルに実在するか確認
    $stmtKubunCheck = $PDO->prepare("SELECT COUNT(*) FROM PRODUCT_KUBUN WHERE PRODUCT_KUBUN_ID = :product_kubun_id");
    $stmtKubunCheck->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtKubunCheck->execute();
    if ($stmtKubunCheck->fetchColumn() === 0) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品区分IDは存在しません。']);
        exit;
    }

    // 2. 同じ商品名が既に存在するか確認 [ここから追加・変更]
    $stmtCheckDuplicateName = $PDO->prepare("SELECT COUNT(*) FROM PRODUCT WHERE PRODUCT_NAME = :product_name");
    $stmtCheckDuplicateName->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtCheckDuplicateName->execute();
    if ($stmtCheckDuplicateName->fetchColumn() > 0) { // 既に同じ商品名が存在する場合
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '入力された商品名は既に登録されています。別の商品名を入力してください。']);
        exit;
    }
    // [ここまで追加・変更]

    // 3. PRODUCTテーブルに商品情報を挿入 (既存の処理)
    $stmtProduct = $PDO->prepare(
        "INSERT INTO PRODUCT (PRODUCT_NAME, UNIT_SELLING_PRICE, PRODUCT_KUBUN_ID, REORDER_POINT, ORDER_NUMBER) 
        VALUES (:product_name, :unit_price, :product_kubun_id, NULL, NULL)"
    );
    $stmtProduct->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtProduct->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtProduct->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtProduct->execute();

    // 挿入された商品のPRODUCT_IDを取得 (既存の処理)
    $newProductId = $PDO->lastInsertId();

    // 4. STOCKテーブルに初期在庫を挿入 (既存の処理)
    $stmtStock = $PDO->prepare(
        "INSERT INTO STOCK (PRODUCT_ID, STOCK_QUANTITY, LAST_UPDATING_TIME) 
        VALUES (:product_id, :stock_quantity, NOW())"
    );
    $stmtStock->bindParam(':product_id', $newProductId, PDO::PARAM_INT);
    $stmtStock->bindParam(':stock_quantity', $initialStock, PDO::PARAM_INT);
    $stmtStock->execute();

    // 全ての操作が成功したらコミット
    $PDO->commit();
    echo json_encode(['success' => true, 'message' => '商品が正常に追加されました。', 'product_id' => $newProductId]);

} catch (PDOException $e) {
    $PDO->rollBack();
    error_log("Database error in add_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。時間をおいて再度お試しください。'
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