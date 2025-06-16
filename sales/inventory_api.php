<?php

require_once '../config.php'; // 設定ファイルを読み込む

header('Content-Type: application/json'); // JSON形式でレスポンスを返すことを指定

// SQLクエリを構築
// 商品 (PRODUCT)、在庫 (STOCK)、商品区分 (PRODUCT_KUBUN) の情報を結合して取得
$sql = "SELECT P.PRODUCT_ID, P.PRODUCT_NAME, P.UNIT_SELLING_PRICE, S.STOCK_QUANTITY, K.PRODUCT_KUBUN_NAME
        FROM PRODUCT P
        LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
        LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID";
// SQL文をプリペアドステートメントとして準備
$stmt = $PDO->prepare($sql);

// SQLクエリを実行
$stmt->execute();

// 結果を連想配列としてすべて取得
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 取得したデータをJSON形式で出力
echo json_encode($results);

?>