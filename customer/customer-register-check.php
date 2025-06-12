<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json'); // JSON形式でレスポンスを返すことを宣言

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームデータの取得とサニタイズ
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cell_number = filter_input(INPUT_POST, 'cell_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_EMAIL);
    $post_code = filter_input(INPUT_POST, 'post_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // 必須項目のチェック
    if (empty($name) || empty($mail) || empty($post_code) || empty($address)) {
        $response['message'] = '必須項目が入力されていません。';
        echo json_encode($response);
        exit;
    }

    // メールアドレスの形式チェック
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = '有効なメールアドレスではありません。';
        echo json_encode($response);
        exit;
    }

    try {
        // データベース接続
        $pdo = new PDO(DSN, DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // プリペアドステートメントを有効にする

        // プリペアドステートメントでSQLインジェクション対策
        $stmt = $pdo->prepare("INSERT INTO customers (name, cell_number, mail, post_code, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $cell_number, $mail, $post_code, $address]);

        $response['success'] = true;
        $response['message'] = '顧客情報が正常に登録されました。';

    } catch (PDOException $e) {
        $response['message'] = 'データベースエラー: ' . $e->getMessage();
        // エラーログの記録（本番環境では詳細なエラーメッセージをユーザーに見せないようにしましょう）
        error_log('Customer registration error: ' . $e->getMessage());
    }
} else {
    $response['message'] = '無効なリクエストです。';
}

echo json_encode($response);
?>