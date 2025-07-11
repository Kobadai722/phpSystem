<?php

require_once '../../config.php'; // DB接続設定などを読み込みます。

header('Content-Type: application/json');

// GETリクエストから商品IDを取得
$productId = $_GET['product_id'] ?? null;

// 商品IDのバリデーション
if (empty($productId) || !filter_var($productId, FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => '無効な商品IDが指定されました。']);
    exit;
}

try {
    // PRODUCTテーブルとSTOCKテーブル、PRODUCT_KUBUNテーブルを結合して商品情報を取得
    $stmt = $PDO->prepare(
        "SELECT p.PRODUCT_ID, p.PRODUCT_NAME, p.UNIT_SELLING_PRICE, s.STOCK_QUANTITY, pk.PRODUCT_KUBUN_ID, pk.PRODUCT_KUBUN_NAME
        FROM PRODUCT p
        JOIN STOCK s ON p.PRODUCT_ID = s.PRODUCT_ID
        JOIN PRODUCT_KUBUN pk ON p.PRODUCT_KUBUN_ID = pk.PRODUCT_KUBUN_ID
        WHERE p.PRODUCT_ID = :product_id"
    );
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => '指定された商品が見つかりませんでした。']);
    }

} catch (PDOException $e) {
    error_log("Database error in get_product_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました。'
    ]);
} catch (Exception $e) {
    error_log("Unexpected error in get_product_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '処理中に予期せぬエラーが発生しました。'
    ]);
}

?>