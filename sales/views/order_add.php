<?php
require_once '../../config.php'; 

// 商品リスト取得処理
$products = [];
$stmt = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
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
                
                <form id="orderAddForm" method="POST" novalidate> 
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">商品</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">選択してください</option>
                            <?php foreach ($products as $product): ?>
                            <option 
                                value="<?php echo htmlspecialchars($product['PRODUCT_ID']); ?>"
                                data-name="<?php echo htmlspecialchars($product['PRODUCT_NAME']); ?>"
                                data-price="<?php echo htmlspecialchars($product['UNIT_SELLING_PRICE']); ?>"
                            >
                                <?php echo htmlspecialchars($product['PRODUCT_NAME']); ?> 
                                (ID: <?php echo htmlspecialchars($product['PRODUCT_ID']); ?>, 
                                単価: <?php echo number_format($product['UNIT_SELLING_PRICE']); ?>円)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">商品を選択してください。</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_quantity" class="form-label">注文数量</label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1" value="1">
                        <div class="invalid-feedback">注文数量を入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">顧客ID</label>
                        <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" step="1" value="1">
                        <div class="form-text text-muted">有効な顧客IDを入力してください。</div>
                        <div class="invalid-feedback">顧客IDを入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">備考</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255"></textarea>
                        <div class="form-text text-muted">最大255文字まで</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitFormBtn"> 
                            <i class="bi bi-cart-plus me-2"></i>注文を登録する
                        </button>
                        <a href="purchase.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!--  確認モーダル -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">注文内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>以下の内容で注文を登録します。ご確認ください。</p>

                    <table class="table table-bordered">
                        <tr><th>商品名</th><td id="confirmProductName"></td></tr>
                        <tr><th>単価</th><td id="confirmProductPrice"></td></tr>
                        <tr><th>数量</th><td id="confirmQuantity"></td></tr>
                        <tr><th>小計</th><td id="confirmSubtotal"></td></tr>
                        <tr><th>顧客ID</th><td id="confirmCustomerId"></td></tr>
                        <tr><th>備考</th><td id="confirmNotes"></td></tr>
                    </table>
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
            const confirmBtn = document.getElementById('confirmOrderBtn');
            const modalElement = document.getElementById('confirmModal');
            const confirmModal = new bootstrap.Modal(modalElement);

            //  モーダル内の各要素取得
            const confirmProductName = document.getElementById('confirmProductName');
            const confirmProductPrice = document.getElementById('confirmProductPrice');
            const confirmQuantity = document.getElementById('confirmQuantity');
            const confirmSubtotal = document.getElementById('confirmSubtotal');
            const confirmCustomerId = document.getElementById('confirmCustomerId');
            const confirmNotes = document.getElementById('confirmNotes');

            //  submitボタン押下時の処理
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();

                if (form.checkValidity()) {
                    //  バリデーションOK時のみモーダルを表示
                    const selectedOption = document.querySelector('#product_id option:checked');
                    const productName = selectedOption.dataset.name || '';
                    const price = parseFloat(selectedOption.dataset.price || 0);
                    const quantity = parseInt(document.getElementById('order_quantity').value || 0);
                    const subtotal = price * quantity;

                    confirmProductName.textContent = productName;
                    confirmProductPrice.textContent = price.toLocaleString() + ' 円';
                    confirmQuantity.textContent = quantity + ' 個';
                    confirmSubtotal.textContent = subtotal.toLocaleString() + ' 円';
                    confirmCustomerId.textContent = document.getElementById('customer_id').value;
                    confirmNotes.textContent = document.getElementById('notes').value || '（なし）';

                    confirmModal.show();
                } else {
                    form.classList.add('was-validated');
                }
            });

            //  注文確定ボタン押下
            confirmBtn.addEventListener('click', async function() {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '処理中...';

                const formData = new FormData(form);
                const response = await fetch('../api/add_order_api.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    alert('成功: ' + data.message);
                    form.reset();
                    form.classList.remove('was-validated');
                    confirmModal.hide(); 
                } else {
                    alert('失敗: ' + data.message);
                }

                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '注文を確定';
            });
        });
    </script>
</body>
</html>
