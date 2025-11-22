<?php
// config.phpの読み込みは必須
require_once '../../config.php'; 

// 担当者IDを仮定。ここでは固定値 '1' を設定します。
$loggedInEmployeeId = 1; 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい売上の直接登録</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- カスタムスタイル -->
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* カスタムメッセージボックスのスタイル */
        .message-box {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.5s, transform 0.5s;
            transform: translateX(100%);
            min-width: 300px;
            border-radius: 0.5rem;
        }
        .message-box.show {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <!-- ORDER表に直接登録するシンプルなフォームであることを明記 -->
                <h2 class="mb-4">新しい売上の直接登録 (ORDERヘッダー向け)</h2>
                
                <div class="card p-4 shadow-lg" style="max-width: 600px; border-radius: 0.75rem;">
                    <form id="saleForm" class="needs-validation" novalidate>
                        
                        <!-- 顧客ID (DB: ORDER.ORDER_TARGET_ID) -->
                        <div class="mb-3">
                            <label for="order_target_id" class="form-label fw-bold">顧客ID (ORDER_TARGET_ID)</label>
                            <!-- API側が期待する名前（ORDER_TARGET_ID）を使用 -->
                            <input type="number" class="form-control" id="order_target_id" name="ORDER_TARGET_ID" 
                                required min="1" placeholder="顧客のIDを入力してください" style="border-radius: 0.5rem;">
                            <div class="invalid-feedback">顧客IDを入力してください。</div>
                        </div>

                        <!-- 合計金額 (DB: ORDER.PRICE) -->
                        <div class="mb-3">
                            <label for="price" class="form-label fw-bold">合計金額 (PRICE)</label>
                            <!-- API側が期待する名前（PRICE）を使用 -->
                            <div class="input-group">
                                <input type="number" class="form-control" id="price" name="PRICE" 
                                    required min="1" placeholder="合計金額を整数で入力してください" style="border-radius: 0.5rem 0 0 0.5rem;">
                                <span class="input-group-text" style="border-radius: 0 0.5rem 0.5rem 0;">円</span>
                            </div>
                            <div class="invalid-feedback">1円以上の合計金額を入力してください。</div>
                        </div>

                        <!-- 担当者ID (DB: ORDER.EMPLOYEE_ID) -->
                        <div class="mb-3">
                            <label for="employee_id" class="form-label fw-bold">担当者ID (EMPLOYEE_ID)</label>
                            <!-- API側が期待する名前（EMPLOYEE_ID）を使用 -->
                            <input type="number" class="form-control" id="employee_id" name="EMPLOYEE_ID" 
                                required min="1" value="<?= htmlspecialchars($loggedInEmployeeId) ?>" 
                                style="border-radius: 0.5rem;">
                            <div class="invalid-feedback">担当者IDを入力してください。</div>
                            <small class="form-text text-muted">※ 初期値はログインユーザーのIDです。</small>
                        </div>
                        
                        <!-- 登録ボタン -->
                        <!-- 処理先は、ORDER表に直接登録するAPI（例: order_store.php）を想定 -->
                        <button type="button" class="btn btn-success w-100 mt-4 shadow-sm" id="submitButton" style="border-radius: 0.5rem;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>売上を登録
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    
    <!-- カスタムメッセージボックスのコンテナ -->
    <div id="messageContainer"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-geWF76RCwLtnZ8wT91k1z/y6IqfC6b2Zl75cI9BfQ4z8m9dK9lY0v3F4w0l0kF4j9e9bE9F" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('saleForm');
            const submitButton = document.getElementById('submitButton'); 
            const messageContainer = document.getElementById('messageContainer');
            
            // APIのファイルパスを想定（以前の order_store.php に合わせる）
            const API_ENDPOINT = '../actions/order_store.php';

            /**
             * 成功/失敗メッセージを画面右上に表示するカスタムメッセージボックス関数
             */
            function showMessage(type, message) {
                const messageBox = document.createElement('div');
                messageBox.className = `message-box alert alert-dismissible alert-${type} shadow`;
                messageBox.setAttribute('role', 'alert');
                messageBox.innerHTML = `
                    <div>${message}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                messageContainer.appendChild(messageBox);

                setTimeout(() => {
                    messageBox.classList.add('show');
                }, 100);

                setTimeout(() => {
                    messageBox.classList.remove('show');
                    messageBox.addEventListener('transitionend', () => messageBox.remove());
                }, 5000);
            }

            // 登録ボタン押下時の処理
            submitButton.addEventListener('click', async function() {
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return; // バリデーションエラーがあれば終了
                }
                
                // バリデーション成功
                form.classList.remove('was-validated');
                
                // 連打防止とローディング表示
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 登録中...';

                const formData = new FormData(form);
                
                // try/catchを削除し、fetchを実行
                const response = await fetch(API_ENDPOINT, { 
                    method: 'POST', 
                    body: formData 
                });
                
                // HTTPステータスコードは無視し、JSONパースに進む
                const data = await response.json();

                // サーバー側で処理され、JSONとして成功/失敗が返されることを想定
                if (data.success) {
                    showMessage('success', data.message);
                    form.reset();
                } else {
                    // APIから返されたエラーメッセージを表示
                    showMessage('danger', data.message);
                }

                // ボタンの状態を元に戻す (finallyブロックの代わり)
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>売上を登録';
            });
        });
    </script>
</body>
</html>