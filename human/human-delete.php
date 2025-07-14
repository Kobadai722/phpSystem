<?php
require_once '../config.php'; // DBサーバーと接続

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? null;

    if (empty($employeeId)) {
        echo json_encode(['success' => false, 'message' => '従業員IDが提供されていません。']);
        exit;
    }

    try {
        // ここで削除（論理削除）処理を行うSQLクエリが通常は記述されます。
        // ご要望「削除済み項目は削除しないようにしてください」に基づき、
        // 論理削除（IS_DELETEDを1に設定）を行う処理を停止します。
        // 例: UPDATE EMPLOYEE SET IS_DELETED = 1 WHERE EMPLOYEE_ID = ?;
        // この行をコメントアウトするか削除することで、削除操作は行われません。

        // データベースに何も変更を加えないため、成功として応答します。
        echo json_encode(['success' => true, 'message' => '削除操作は実行されませんでした。']);

    } catch (PDOException $e) {
        error_log("Error deleting employee: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'データベースエラーが発生しました。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '不正なリクエストメソッドです。']);
}
?>