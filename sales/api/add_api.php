<?php

require_once '../../config.php';

header('Content-Type: application/json');

$productName = $_POST['product_name'] ?? '';
$unitPrice = $_POST['unit_price'] ?? '';
$initialStock = $_POST['initial_stock'] ?? '';
$productCategoryId = $_POST['product_category'] ?? '';
$description = $_POST['description'] ?? ''; // ★追加：descriptionを取得

$errors = [];

if (empty($productName)) {
    $errors[] = '商品名は必須です。';
} elseif (mb_strlen($productName, 'UTF-8') > 20) {
    $errors[] = '商品名は20文字以内で入力してください。';
}

if (!filter_var($unitPrice, FILTER_VALIDATE_INT) || $unitPrice < 0) {
    $errors[] = '単価は0以上の整数で入力してください。';
}

if (!filter_var($initialStock, FILTER_VALIDATE_INT) || $initialStock < 0) {
    $errors[] = '初期在庫は0以上の整数で入力してください。';
}

if (empty($productCategoryId)) {
    $errors[] = '商品区分を選択してください。';
}

// descriptionのバリデーション（例：最大文字数制限）
// PRODUCTテーブルのDESCRIPTIONカラムの型に合わせて調整してください
if (mb_strlen($description, 'UTF-8') > 500) { // 例: 500文字制限
    $errors[] = '備考/説明は500文字以内で入力してください。';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $PDO->beginTransaction();

    // 商品区分名を取得してプレフィックスを決定
    $stmtKubun = $PDO->prepare("SELECT PRODUCT_KUBUN_NAME FROM PRODUCT_KUBUN WHERE PRODUCT_KUBUN_ID = :product_kubun_id");
    $stmtKubun->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtKubun->execute();
    $productKubun = $stmtKubun->fetch(PDO::FETCH_ASSOC);

    if (!$productKubun) {
        throw new Exception("指定された商品区分が見つかりません。");
    }

    $prefix = '';
    // 要件定義書に基づいてプレフィックスを設定
    switch ($productKubun['PRODUCT_KUBUN_NAME']) {
        case 'PC':
            $prefix = 'PC';
            break;
        case 'Mobile':
            $prefix = 'MB';
            break;
        case 'Monitor':
            $prefix = 'MN';
            break;
        case 'Option':
            $prefix = 'OP';
            break;
        default:
            $prefix = 'OT'; // Other
            break;
    }

    // 新しい商品IDを生成
    // 例: PC00001, MB00002
    // 同じプレフィックスを持つ最新のIDを取得
    $stmtLastId = $PDO->prepare("SELECT PRODUCT_ID FROM PRODUCT WHERE PRODUCT_ID LIKE :prefix ORDER BY PRODUCT_ID DESC LIMIT 1");
    $stmtLastId->bindValue(':prefix', $prefix . '%', PDO::PARAM_STR);
    $stmtLastId->execute();
    $lastId = $stmtLastId->fetchColumn();

    $newNumericId = 1;
    if ($lastId) {
        // プレフィックスの後の数値部分を取得してインクリメント
        $numericPart = (int)substr($lastId, strlen($prefix));
        $newNumericId = $numericPart + 1;
    }

    $newProductId = $prefix . sprintf('%05d', $newNumericId); // 5桁のゼロ埋め

    // PRODUCTテーブルに商品情報を挿入
    $stmtProduct = $PDO->prepare(
        "INSERT INTO PRODUCT (PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE, PRODUCT_KUBUN_ID, DESCRIPTION, REORDER_POINT, ORDER_NUMBER) 
         VALUES (:product_id, :product_name, :unit_price, :product_kubun_id, :description, NULL, NULL)" // ★変更：DESCRIPTIONを追加
    );
    $stmtProduct->bindParam(':product_id', $newProductId, PDO::PARAM_STR);
    $stmtProduct->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtProduct->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtProduct->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtProduct->bindParam(':description', $description, PDO::PARAM_STR); // 追加：descriptionをバインド
    $stmtProduct->execute();

    // STOCKテーブルに初期在庫を挿入
    $stmtStock = $PDO->prepare(
        "INSERT INTO STOCK (PRODUCT_ID, STOCK_QUANTITY, LAST_UPDATING_TIME) 
         VALUES (:product_id, :stock_quantity, NOW())"
    );
    $stmtStock->bindParam(':product_id', $newProductId, PDO::PARAM_STR); // 変更: INTではなくSTRに
    $stmtStock->bindParam(':stock_quantity', $initialStock, PDO::PARAM_INT);
    $stmtStock->execute();

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