<?php
require_once '../../config.php'; 

// 商品リスト取得処理
// 売り上げ対象となる商品を取得
$products = [];
try {
    // PRODUCTテーブルから商品ID、商品名、単価を取得
    $stmt = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // データベースエラー時のログ出力
    error_log("商品リスト取得エラー: " . $e->getMessage());
    $products = []; 
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい売り上げの登録</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- カスタムCSS（styles.cssのパスはプロジェクト構造に依存） -->
    <link rel="stylesheet" href="../css/styles.css"> 
    <style>
        /* カスタムメッセージボックスのスタイル（重要） */
        .message-box {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.5s, transform 0.5s;
            transform: translateX(100%);
            min-width: 300px;
        }
        .message-box.show {
            opacity: 1;
            transform: translateX(0);
        }
        .form-select.is-invalid, .form-control.is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">新しい売り上げの登録</h2>
                
                <!-- フォーム本体 -->
                <div class="card p-4 shadow-sm" style="max-width: 600px;">
                    <form id="saleForm" class="needs-validation" novalidate>
                        
                        <!-- 商品選択 -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label">商品名</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="" selected disabled>商品を選択してください</option>
                                <?php foreach ($products as $product): ?>
                                    <option 
                                        value="<?= htmlspecialchars($product['PRODUCT_ID']) ?>" 
                                        data-price="<?= htmlspecialchars($product['UNIT_SELLING_PRICE']) ?>"
                                        data-name="<?= htmlspecialchars($product['PRODUCT_NAME']) ?>"
                                    >
                                        <?= htmlspecialchars($product['PRODUCT_NAME']) ?> 
                                        (<?= number_format($product['UNIT_SELLING_PRICE']) ?> 円)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">商品を選択してください。</div>
                        </div>

                        <!-- 数量 -->
                        <div class="mb-3">
                            <label for="sale_quantity" class="form-label">数量</label>
                            <input type="number" class="form-control" id="sale_quantity" name="sale_quantity" min="1" required value="1">
                            <div class="invalid-feedback">1以上の数量を入力してください。</div>
                        </div>

                        <!-- 顧客ID (ORDER_TARGET_ID として使用) -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">顧客ID (ORDER_TARGET_ID)</label>
                            <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" placeholder="顧客のIDを入力してください">
                            <div class="invalid-feedback">顧客IDを入力してください。</div>
                        </div>

                        <!-- 備考はORDERテーブルのスキーマに合わせて削除 -->
                        
                        <button type="button" class="btn btn-primary w-100 mt-3" id="confirmButton">
                            <i class="bi bi-cart-fill me-2"></i>売り上げ内容を確認
                        </button>
                    </form>
                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($loggedInEmployeeId) ?>">
                </div>
            </div>
        </section>
    </main>
    
    <!-- footerは削除 -->

    <!-- 確認モーダル -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">売り上げ内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>以下の内容で売り上げを登録します。よろしいですか？</p>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>商品名</th><td id="confirmProductName"></td></tr>
                            <tr><th>単価</th><td id="confirmProductPrice"></td></tr>
                            <tr><th>数量</th><td id="confirmQuantity"></td></tr>
                            <tr class="table-info"><th>合計金額</th><td id="confirmSubtotal" class="fw-bold"></td></tr>
                            <tr><th>顧客ID</th><td id="confirmCustomerId"></td></tr>
                            <!-- 備考行は削除 -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <!-- 確定ボタンのIDをAPIと整合性の高い confirmBtn に修正 -->
                    <button type="button" class="btn btn-success" id="confirmBtn">売り上げを確定</button>
                </div>
            </div>
        </div>
    </div>

    <!-- カスタムメッセージボックスのコンテナ（重要） -->
    <div id="messageContainer"></div>

    <!-- Bootstrap JS (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-geWF76RCwLtnZ8wT91k1z/y6IqfC6b2Zl75cI9BfQ4z8m9dK9lY0v3F4w0l0kF4j9e9bE9F"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('saleForm');
            // IDを元の名前に統一
            const confirmButton = document.getElementById('confirmButton'); 
            const confirmBtn = document.getElementById('confirmBtn'); 
            const confirmModalElement = document.getElementById('confirmModal');
            const confirmModal = new bootstrap.Modal(confirmModalElement);
            const messageContainer = document.getElementById('messageContainer');

            const productIdSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('sale_quantity'); 

            /**
             * 成功/失敗メッセージを画面右上に表示するカスタムメッセージボックス関数
             * alert()の代わりに使用します。
             * @param {string} type - 'success' または 'danger'
             * @param {string} message - 表示するメッセージ
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

                // わずかな遅延後に 'show' クラスを追加して表示アニメーションを開始
                setTimeout(() => {
                    messageBox.classList.add('show');
                }, 100);

                // 5秒後に自動的に非表示アニメーションを開始
                setTimeout(() => {
                    messageBox.classList.remove('show');
                    // 非表示トランジション後に要素をDOMから削除
                    messageBox.addEventListener('transitionend', () => messageBox.remove());
                }, 5000);
            }


            // 必須入力チェックとモーダル表示 (ID: confirmButton)
            confirmButton.addEventListener('click', function() {
                // フォームのバリデーションチェック
                if (form.checkValidity()) {
                    form.classList.remove('was-validated');

                    const selectedOption = productIdSelect.options[productIdSelect.selectedIndex];
                    const productName = selectedOption.text.split('(')[0].trim(); // 商品名のみ取得
                    const price = parseInt(selectedOption.getAttribute('data-price'), 10);
                    const quantity = parseInt(quantityInput.value, 10);
                    const subtotal = price * quantity;
                    
                    document.getElementById('confirmProductName').textContent = productName;
                    document.getElementById('confirmProductPrice').textContent = price.toLocaleString() + ' 円';
                    document.getElementById('confirmQuantity').textContent = quantity + ' 個';
                    document.getElementById('confirmSubtotal').textContent = subtotal.toLocaleString() + ' 円';
                    document.getElementById('confirmCustomerId').textContent = document.getElementById('customer_id').value;
                    // document.getElementById('confirmNotes').textContent = document.getElementById('notes').value || '（なし）'; // NOTES削除

                    confirmModal.show();
                } else {
                    // バリデーション失敗時にエラーメッセージを表示
                    form.classList.add('was-validated');
                }
            });

            // 売り上げ確定ボタン押下 (モーダル内のボタン, ID: confirmBtn)
            confirmBtn.addEventListener('click', async function() {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 処理中...';

                const formData = new FormData(form);
                
                try {
                    // APIコール
                    const response = await fetch('../api/add_sale_api.php', { method: 'POST', body: formData });
                    
                    if (!response.ok) {
                        throw new Error('サーバーからの応答が不正です。ステータスコード: ' + response.status);
                    }
                    
                    const data = await response.json();

                    if (data.success) {
                        showMessage('success', data.message);
                        // フォームのリセットとバリデーション状態の解除
                        form.reset();
                        form.classList.remove('was-validated');
                        confirmModal.hide(); // 確定成功時にモーダルを非表示
                    } else {
                        showMessage('danger', data.message);
                    }

                } catch (error) {
                    showMessage('danger', '通信エラーが発生しました: ' + error.message);
                } finally {
                    // 処理終了後、ボタンの状態を元に戻す
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '売り上げを確定';
                }
            });
        });
    </script>
</body>
</html>