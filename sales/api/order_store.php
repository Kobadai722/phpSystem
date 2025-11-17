<?php

require_once '../../config.php';

header('Content-Type: application/json');


$orderTargetId = $_POST['ORDER_TARGET_ID'] ?? 0;
$price = $_POST['PRICE'] ?? 0;
$employeeId = $_POST['EMPLOYEE_ID'] ?? 0;

// 入力チェック
if ($orderTargetId == 0 || $price == 0 || $employeeId == 0) {
    echo json_encode([
        'success' => false,
        'message' => '必要な項目が入力されていません。'
    ]);
    exit;
}

try {
    // トランザクション開始
    $PDO->beginTransaction();

    // 現在日時
    $currentDateTime = date('Y-m-d H:i:s');

    $stmt = $PDO->prepare(
        "INSERT INTO `ORDER`
        (PURCHASE_ORDER_DATE, ORDER_TARGET_ID, ORDER_FLAG, PRICE, EMPLOYEE_ID)
        VALUES (:order_date, :target_id, 1, :price, :employee_id)"
    );

    $stmt->bindParam(':order_date', $currentDateTime);
    $stmt->bindParam(':target_id', $orderTargetId, PDO::PARAM_INT);
    $stmt->bindParam(':price', $price, PDO::PARAM_INT);
    $stmt->bindParam(':employee_id', $employeeId, PDO::PARAM_INT);

    $stmt->execute();

    // 登録された ORDER_ID を取得
    $newOrderId = $PDO->lastInsertId();

    // コミット
    $PDO->commit();

    echo json_encode([
        'success' => true,
        'message' => "売上データが正常に登録されました。（ORDER_ID: {$newOrderId}）",
        'order_id' => $newOrderId
    ]);
    exit;

} catch (Exception $e) {

    // ロールバック
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'エラーが発生しました: ' . $e->getMessage()
    ]);
    exit;
}
?>
