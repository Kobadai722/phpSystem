<?php
// PHPのエラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース設定ファイルを読み込む
require_once '../../config.php';

// JSON形式のレスポンスヘッダーを設定
header('Content-Type: application/json');

try {
    // GETまたはPOSTデータからキーワードを取得
    $keyword = $_GET['keyword'] ?? ($_POST['keyword'] ?? '');

    // SQLクエリを構築
    // S_ORDERとCUSTOMERをCUSTOMER_IDで結合して、注文情報と顧客名を取得
    $sql = "SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS, c.NAME AS CUSTOMER_NAME 
            FROM S_ORDER o 
            LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID";

    // キーワードが入力されている場合、WHERE句を追加して絞り込み
    if (!empty($keyword)) {
        // SQLインジェクションを防ぐため、プリペアドステートメントを使用
        $sql .= " WHERE o.ORDER_ID LIKE :keyword OR c.NAME LIKE :keyword OR o.STATUS LIKE :keyword";
    }

    // 注文日時が新しい順に並べ替え
    $sql .= " ORDER BY o.ORDER_DATETIME DESC";

    // プリペアドステートメントの準備
    $stmt = $PDO->prepare($sql);

    // キーワードが入力されている場合、プレースホルダーに値をバインド
    if (!empty($keyword)) {
        // 部分一致検索のために%を追加
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
    }
    
    // SQLクエリを実行
    $stmt->execute();
    
    // 結果を連想配列としてすべて取得
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 成功レスポンスとしてJSONデータを返す
    echo json_encode(['success' => true, 'data' => $orders]);

} catch (PDOException $e) {
    // データベース接続またはクエリ実行エラーが発生した場合
    echo json_encode([
        'success' => false,
        'error_message' => 'データの取得中にエラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}
?>