<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい注文の追加</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">新しい注文の追加</h2>
                
                <form id="orderAddForm" method="POST"> 
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">商品名 (PRODUCT_ID)</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">選択してください</option>
                            <option value="1">商品A (ID: 1)</option>
                            <option value="2">商品B (ID: 2)</option>
                            <option value="3">商品C (ID: 3)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_quantity" class="form-label">注文数量 (ORDER_QUANTITY)</label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1" value="1">
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">顧客ID (CUSTOMER_ID)</label>
                        <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" step="1" value="1">
                        <div class="form-text text-muted">有効な顧客IDを入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">備考 (NOTES)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255"></textarea>
                        <div class="form-text text-muted">最大255文字まで</div>
                    </div>
                    
                    <div class="alert alert-info small mt-4">
                        <p class="mb-1">以下の項目はサーバー側で自動設定されます: ORDER\_DATETIME, TOTAL\_AMOUNT, STATUS, CREATED\_AT, UPDATED\_AT</p>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitFormBtn" data-bs-toggle="modal" data-bs-target="#confirmModal"> 
                            <i class="bi bi-cart-plus me-2"></i>注文を登録する
                        </button>
                        <a href="order_management.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">注文内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>この内容で注文を登録してもよろしいですか？</p>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="confirmOrderBtn">注文を確定</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('orderAddForm');
            const submitBtn = document.getElementById('submitFormBtn');
            const confirmBtn = document.getElementById('confirmOrderBtn');
            const modalElement = document.getElementById('confirmModal');
            // BootstrapのModalインスタンスを取得
            const confirmModal = new bootstrap.Modal(modalElement);
            
            // 1. フォーム送信（submit）時は、ブラウザバリデーションのみ行い、モーダル表示をキャンセル（Bootstrapのdata-bs-toggleで処理されるため）
            form.addEventListener('submit', function(event) {
                // HTML5の必須入力チェック
                if (!form.checkValidity()) {
                    event.preventDefault(); // バリデーションエラーならモーダルを出さない
                    event.stopPropagation();
                    form.classList.add('was-validated');
                } else {
                    // バリデーションOKの場合、JavaScriptのsubmitイベントをキャンセル
                    // Bootstrapのdata-bs-toggleがモーダル表示を処理するため、ここでは何もせず
                }
            });

            // 2. モーダル内の「注文を確定」ボタンが押された時の処理（API実行）
            confirmBtn.addEventListener('click', async function() {
                // API実行中のUI制御
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '処理中...';

                const formData = new FormData(form);

                // APIコール（try/catchなしの最小限処理）
                const response = await fetch('../api/add_order_api.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    alert('成功: ' + data.message);
                    form.reset();
                    form.classList.remove('was-validated');
                    confirmModal.hide(); // 成功したらモーダルを閉じる
                } else {
                    alert('失敗: ' + data.message);
                }

                // 終了処理
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '注文を確定';
            });
            
            // モーダルが閉じられた時に送信ボタンのテキストをリセットする（念のため）
            modalElement.addEventListener('hidden.bs.modal', function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>注文を登録する';
            })
        });
    </script>
</body>
</html>