<?php

require_once '../../config.php'; // DB接続設定などを読み込みます。

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから必要な情報を取得
$productId = $_POST['product_id'] ?? null;
$productName = $_POST['product_name'] ?? '';
$unitPrice = $_POST['unit_price'] ?? '';
$stockQuantity = $_POST['stock_quantity'] ?? '';
$productCategoryId = $_POST['product_category'] ?? '';

// 入力値のバリデーション
$errors = [];

if (empty($productId) || !filter_var($productId, FILTER_VALIDATE_INT)) {
    $errors[] = '商品IDが不正です。';
}
if (empty($productName)) {
    $errors[] = '商品名は必須です。';
} elseif (mb_strlen($productName, 'UTF-8') > 20) {
    $errors[] = '商品名は20文字以内で入力してください。';
}
if (filter_var($unitPrice, FILTER_VALIDATE_INT) === false || $unitPrice < 0) {
    $errors[] = '単価は0以上の整数で入力してください。';
}
if (filter_var($stockQuantity, FILTER_VALIDATE_INT) === false || $stockQuantity < 0) {
    $errors[] = '在庫数は0以上の整数で入力してください。';
}
if (filter_var($productCategoryId, FILTER_VALIDATE_INT) === false || $productCategoryId <= 0) {
    $errors[] = '商品区分が正しく指定されていません。';
}

// バリデーションエラーがある場合は、エラーメッセージをまとめて返す
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $PDO->beginTransaction();

    // 1. 商品区分IDの存在チェック
    $stmtKubunCheck = $PDO->prepare("SELECT COUNT(*) FROM PRODUCT_KUBUN WHERE PRODUCT_KUBUN_ID = :product_kubun_id");
    $stmtKubunCheck->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtKubunCheck->execute();
    if ($stmtKubunCheck->fetchColumn() === 0) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '指定された商品区分IDは存在しません。']);
        exit;
    }

    // 2. 同じ商品名が既に存在するか確認 (ただし、自身の商品の場合は許可)
    // 自身の商品ID以外で同じ商品名があるかチェック
    $stmtCheckDuplicateName = $PDO->prepare(
        "SELECT COUNT(*) FROM PRODUCT WHERE PRODUCT_NAME = :product_name AND PRODUCT_ID != :product_id"
    );
    $stmtCheckDuplicateName->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtCheckDuplicateName->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtCheckDuplicateName->execute();
    if ($stmtCheckDuplicateName->fetchColumn() > 0) {
        $PDO->rollBack();
        echo json_encode(['success' => false, 'message' => '入力された商品名は既に別の商品に登録されています。別の商品名を入力してください。']);
        exit;
    }

    // 3. PRODUCTテーブルの更新
    $stmtUpdateProduct = $PDO->prepare(
        "UPDATE PRODUCT SET PRODUCT_NAME = :product_name, UNIT_SELLING_PRICE = :unit_price, PRODUCT_KUBUN_ID = :product_kubun_id
        WHERE PRODUCT_ID = :product_id"
    );
    $stmtUpdateProduct->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmtUpdateProduct->bindParam(':unit_price', $unitPrice, PDO::PARAM_INT);
    $stmtUpdateProduct->bindParam(':product_kubun_id', $productCategoryId, PDO::PARAM_INT);
    $stmtUpdateProduct->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtUpdateProduct->execute();

    // 4. STOCKテーブルの更新
    // STOCKテーブルにはPRODUCT_IDがプライマリキーまたはユニークキーであると仮定し、UPDATE文を使用
    $stmtUpdateStock = $PDO->prepare(
        "UPDATE STOCK SET STOCK_QUANTITY = :stock_quantity, LAST_UPDATING_TIME = NOW()
        WHERE PRODUCT_ID = :product_id"
    );
    $stmtUpdateStock->bindParam(':stock_quantity', $stockQuantity, PDO::PARAM_INT);
    $stmtUpdateStock->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtUpdateStock->execute();

    $PDO->commit();
    echo json_encode(['success' => true, 'message' => '商品情報が正常に更新されました。']);

} catch (PDOException $e) {
    $PDO->rollBack();
    error_log("Database error in update_product_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。時間をおいて再度お試しください。'
    ]);
} catch (Exception $e) {
    $PDO->rollBack();
    error_log("Unexpected error in update_product_api.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました。システム管理者にお問い合わせください。'
    ]);
}

?>