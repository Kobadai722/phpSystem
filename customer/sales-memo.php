<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

// --- HTMLヘッダーと基本CSS ---
echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><title>デバッグ</title>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>';
echo '<main class="container mt-3">';

// --- デバッグセクション ---
echo '<div class="alert alert-warning">';
echo '<h3>最終診断</h3>';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    echo '<p class="text-danger">エラー: URLに正しい customer_id が指定されていません。</p>';
} else {
    $customer_id = (int)$_GET['customer_id'];
    echo "<p><strong>診断対象の顧客ID:</strong> " . htmlspecialchars($customer_id) . "</p><hr>";

    try {
        // --- 診断1: 顧客テーブルの存在確認 ---
        $customer_check_stmt = $PDO->prepare("SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = ?");
        $customer_check_stmt->execute([$customer_id]);
        $customer_data = $customer_check_stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h4>診断1: 顧客テーブルの存在確認</h4>";
        if ($customer_data) {
            echo '<p class="text-success">成功: 顧客ID ' . $customer_id . ' の顧客「' . htmlspecialchars($customer_data['NAME']) . '」は存在します。</p>';
        } else {
            echo '<p class="text-danger">失敗: 顧客ID ' . $customer_id . ' の顧客はCUSTOMERテーブルに存在しません。</p>';
        }
        echo "<hr>";

        // --- 診断2: prepared statement (安全な方法) でのデータ取得 ---
        $stmt_prepare = $PDO->prepare("SELECT * FROM NEGOTIATION_MANAGEMENT WHERE CUSTOMER_ID = ?");
        $stmt_prepare->execute([$customer_id]);
        $negotiations_prepare = $stmt_prepare->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>診断2: 安全な方法 (prepared statement) でのデータ取得</h4>";
        echo '<p>実行クエリ: <code>SELECT * FROM NEGOTIATION_MANAGEMENT WHERE CUSTOMER_ID = ?</code> (<code>?</code> には <code>' . $customer_id . '</code> をバインド)</p>';
        echo '<p>取得件数: <strong>' . count($negotiations_prepare) . ' 件</strong></p>';
        if (empty($negotiations_prepare)) {
             echo '<p class="text-danger">→ この方法ではデータを取得できませんでした。</p>';
        } else {
             echo '<p class="text-success">→ この方法でデータを正常に取得できました。</p>';
        }
        echo "<hr>";
        
        // --- 診断3: direct query (直接的な方法) でのデータ取得 ---
        $query_direct = "SELECT * FROM NEGOTIATION_MANAGEMENT WHERE CUSTOMER_ID = " . $customer_id;
        $stmt_direct = $PDO->query($query_direct);
        $negotiations_direct = $stmt_direct->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>診断3: 直接的な方法 (direct query) でのデータ取得</h4>";
        echo '<p>実行クエリ: <code>' . htmlspecialchars($query_direct) . '</code></p>';
        echo '<p>取得件数: <strong>' . count($negotiations_direct) . ' 件</strong></p>';
         if (empty($negotiations_direct)) {
             echo '<p class="text-danger">→ この方法でもデータを取得できませんでした。</p>';
        } else {
             echo '<p class="text-success">→ この方法でデータを正常に取得できました。</p>';
        }
        echo "<hr>";
        
        echo "<h4>診断結果</h4>";
        if(!empty($negotiations_prepare) || !empty($negotiations_direct)){
             echo '<p class="text-success">少なくともどちらかの方法でデータの取得には成功しています。この下の本来のページでデータが表示されるはずです。</p>';
        } else {
            echo '<p class="text-danger">どちらの方法でもデータを取得できませんでした。データベースのテーブル定義（特にCUSTOMER_IDカラムのデータ型）に問題がある可能性が非常に高いです。</p>';
        }


    } catch (PDOException $e) {
        echo '<p class="text-danger">データベースエラー発生: ' . $e->getMessage() . '</p>';
    }
}
echo '</div>';
echo '<a href="customer.php" class="btn btn-secondary">顧客一覧に戻る</a>';
echo '<hr>';
echo '</main></body></html>';

// 診断コードの実行をここで終了し、本来のページ表示は行わない
exit;