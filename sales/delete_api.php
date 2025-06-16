<?php

require_once '../config.php'; // DB接続設定などを読み込み

header('Content-Type: application/json'); // JSONでレスポンスを返す

// POSTデータから商品IDを取得（未設定の場合はエラー）
$productId = $_POST['product_id'] ?? '';

if (empty($productId)) {
    echo json_encode(['success' => false, 'message' => '商品IDが指定されていません。']);
    exit;
}

try {
    // トランザクション開始（関連テーブルがある場合の整合性確保のため）
    $PDO->beginTransaction();

    // 在庫テーブルから該当商品を削除（存在する場合）
    $stmtStock = $PDO->prepare("DELETE FROM STOCK WHERE PRODUCT_ID = :product_id");
    $stmtStock->bindParam(':product_id', $productId, PDO::PARAM_STR);
    $stmtStock->execute();

    // 商品テーブルから該当商品を削除
    $stmtProduct = $PDO->prepare("DELETE FROM PRODUCT WHERE PRODUCT_ID = :product_id");
    $stmtProduct->bindParam(':product_id', $productId, PDO::PARAM_STR);
    $stmtProduct->execute();

    // 1件以上削除されたかを確認
    if ($stmtProduct->rowCount() > 0) {
        $PDO->commit(); // 正常に削除できたらコミット
        echo json_encode(['success' => true]);
    } else {
        $PDO->rollBack(); // 商品が見つからなかった場合はロールバック
        echo json_encode(['success' => false, 'message' => '指定された商品が見つかりませんでした。']);
    }

} catch (Exception $e) {
    // エラー発生時はロールバックし、エラーメッセージを返す
    $PDO->rollBack();
    echo json_encode([
        'success' => false,
        'message' => '削除処理中にエラーが発生しました: ' . $e->getMessage()
    ]);
}
