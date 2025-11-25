<?php
require_once '../../config.php'; 

// 商品リスト取得
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
    <title>売上の追加</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">新しい売上の追加</h2>
                
                <form id="saleAddForm" method="POST" novalidate> 
                    
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
                                (ID: <?php echo $product['PRODUCT_ID']; ?>, 
                                単価: <?php echo number_format($product['UNIT_SELLING_PRICE']); ?>円)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">商品を選んでください。</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_quantity" class="form-label">数量</label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1" value="1">
                        <div class="invalid-feedback">数量を入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">顧客ID</label>
                        <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" step="1">
                        <div class="invalid-feedback">顧客IDを入力してください。</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-cart-plus me-2"></i>売上を登録する
                        </button>
                        <a href="sale.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!-- 確認モーダル -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">売上内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>以下の内容で売上を登録します。</p>

                    <table class="table table-bordered">
                        <tr><th>商品名</th><td id="confirmProductName"></td></tr>
                        <tr><th>単価</th><td id="confirmProductPrice"></td></tr>
                        <tr><th>数量</th><td id="confirmQuantity"></td></tr>
                        <tr><th>小計</th><td id="confirmSubtotal"></td></tr>
                        <tr><th>顧客ID</th><td id="confirmCustomerId"></td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button class="btn btn-primary" id="confirmSaleBtn">売上を確定</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('saleAddForm');
            const confirmBtn = document.getElementById('confirmSaleBtn');
            const modalEl = document.getElementById('confirmModal');
            const modal = new bootstrap.Modal(modalEl);

            const cName = document.getElementById('confirmProductName');
            const cPrice = document.getElementById('confirmProductPrice');
            const cQty = document.getElementById('confirmQuantity');
            const cSub = document.getElementById('confirmSubtotal');
            const cCust = document.getElementById('confirmCustomerId');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                const opt = document.querySelector('#product_id option:checked');
                const name = opt.dataset.name;
                const price = Number(opt.dataset.price);
                const qty = Number(document.getElementById('order_quantity').value);

                cName.textContent = name;
                cPrice.textContent = price.toLocaleString() + "円";
                cQty.textContent = qty + " 個";
                cSub.textContent = (price * qty).toLocaleString() + "円";
                cCust.textContent = document.getElementById('customer_id').value;

                modal.show();
            });

            confirmBtn.addEventListener('click', async function() {
                confirmBtn.disabled = true;
                confirmBtn.textContent = "登録中...";

                const formData = new FormData(form);
                const response = await fetch('../api/add_sale_api.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await response.json();

                if (json.success) {
                    alert("成功: " + json.message);
                    location.reload();
                } else {
                    alert("失敗: " + json.message);
                }

                confirmBtn.disabled = false;
                confirmBtn.textContent = "売上を確定";
            });
        });
    </script>
</body>
</html>
