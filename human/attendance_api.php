<?php
// === 一時的なデバッグコード：必ず後で元に戻してください ===

// すべてのPHPエラーを表示
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$config_path = '../config.php';

if (file_exists($config_path)) {
    echo "SUCCESS: config.php はパス $config_path に見つかりました。";
} else {
    echo "FAILURE: config.php はパス $config_path に見つかりません。";
}

exit; // ここで強制終了

// === 元のコードはここから下にあります ===
// session_start();
// require_once '../config.php';
// header('Content-Type: application/json');
// ... (元のロジック) ...
?>