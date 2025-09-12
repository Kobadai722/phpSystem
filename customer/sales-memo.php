<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

// ▼▼▼▼▼ ここからデバッグコード ▼▼▼▼▼
echo '<div class="alert alert-danger m-3">';
echo '<strong>最終デバッグ情報:</strong><br>';
echo '<hr>';

try {
    // 条件をつけずに、テーブルの全データを取得する
    $all_data_stmt = $PDO->query("SELECT * FROM NEGOTIATION_MANAGEMENT");
    $all_data = $all_data_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "1. NEGOTIATION_MANAGEMENT テーブルの全データ取得を試みました。<br>";
    
    if ($all_data === false) {
        echo '<strong style="color: red;">エラー: データの取得に失敗しました。PDO::queryがfalseを返しました。</strong><br>';
    } elseif (empty($all_data)) {
        echo '<strong style="color: orange;">警告: テーブルは空です。データが1件も取得できませんでした。</strong><br>';
    } else {
        echo '<strong style="color: green;">成功: 全 ' . count($all_data) . ' 件のデータを取得しました。</strong><br>';
        echo '<pre style="background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 200px; overflow-y: auto;">';
        // var_dump() で取得した全データを詳細に表示
        print_r($all_data);
        echo '</pre>';
    }

} catch (PDOException $e) {
    echo '<strong style="color: red;">データベースエラー発生: ' . $e->getMessage() . '</strong><br>';
}
echo '</div>';
// ▲▲▲▲▲ ここまでデバッグコード ▲▲▲▲▲


// --- 以降のPHPコードは、デバッグ中は影響しないように念のため残します ---
if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: customer.php');
    exit;
}
$customer_id = $_GET['customer_id'];
$stmt_customer = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$stmt_customer->execute([$customer_id]);
$customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    header('Location: customer.php');
    exit;
}
$stmt_negotiation = $PDO->prepare("SELECT * FROM NEGOTIATION_MANAGEMENT WHERE CUSTOMER_ID = ?");
$stmt_negotiation->execute([$customer_id]);
$negotiations = $stmt_negotiation->fetchAll(PDO::FETCH_ASSOC);
$employee_stmt = $PDO->query("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE");
$employees = $employee_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商談管理一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<main class="container">
    <h2 class="my-4">商談管理一覧</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($customer['NAME'] ?? '顧客情報なし') ?></h5>
    </main>
</body>
</html>