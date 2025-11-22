<?php
require_once '../../config.php'; 

// 商品リスト取得処理
// 売り上げ対象となる商品を取得
$products = [];
try {
    $stmt = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // エラー時は空リストとする
    error_log("商品リスト取得エラー: " . $e->getMessage());
    $products = []; 
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- タイトルを修正 -->
    <title>新しい売り上げの登録</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
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
                <!-- 見出しを修正 -->
                <h2 class="mb-4">新しい売り上げの登録（純粋な顧客向け）</h2>
                
                <div class="card p-4 shadow-sm" style="max-width: 600px;">
                    <!-- フォームのidとmethodは変更なし -->
                    <form id="saleForm" novalidate>
                        
                        <!-- 商品選択 -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label">商品名</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="" selected disabled>商品を選択してください</option>
                                <?php foreach ($products as $product): ?>
                                    <option 
                                        value="<?= htmlspecialchars($product['PRODUCT_ID']) ?>" 
                                        data-price="<?= htmlspecialchars($product['UNIT_SELLING_PRICE']) ?>"
                                    >
                                        <?= htmlspecialchars($product['PRODUCT_NAME']) ?> 
                                        (<?= number_format($product['UNIT_SELLING_PRICE']) ?> 円)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">商品を選択してください。</div>
                        </div>

                        <!-- 数量 (name属性をsale_quantityに変更) -->
                        <div class="mb-3">
                            <label for="sale_quantity" class="form-label">数量</label>
                            <input type="number" class="form-control" id="sale_quantity" name="sale_quantity" min="1" required value="1">
                            <div class="invalid-feedback">1以上の数量を入力してください。</div>
                        </div>

                        <!-- 顧客ID (純粋な顧客IDとして扱う) -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">顧客ID (純粋な顧客)</label>
                            <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" placeholder="顧客のIDを入力してください">
                            <div class="invalid-feedback">顧客IDを入力してください。</div>
                        </div>

                        <!-- 備考 -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">備考</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <!-- ボタンのテキストを修正 -->
                        <button type="button" class="btn btn-primary w-100 mt-3" id="confirmSaleBtn">
                            <i class="bi bi-cart-fill me-2"></i>売り上げ内容を確認
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    
    <?php include '../../footer.php'; ?>

    <!-- 確認モーダル -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- モーダルタイトルを修正 -->
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
                            <tr><th>備考</th><td id="confirmNotes"></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <!-- 確定ボタンのテキストを修正 -->
                    <button type="button" class="btn btn-success" id="executeSaleBtn">売り上げを確定</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigl/rmoW81fT9xK6vYhC83u" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('saleForm');
            const confirmBtn = document.getElementById('confirmSaleBtn');
            const executeSaleBtn = document.getElementById('executeSaleBtn');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            
            const productIdSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('sale_quantity'); // IDも修正

            // 必須入力チェックとモーダル表示
            confirmBtn.addEventListener('click', function() {
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
                    document.getElementById('confirmNotes').textContent = document.getElementById('notes').value || '（なし）';

                    confirmModal.show();
                } else {
                    form.classList.add('was-validated');
                }
            });

            // 売り上げ確定ボタン押下 (API呼び出し)
            executeSaleBtn.addEventListener('click', async function() {
                executeSaleBtn.disabled = true;
                executeSaleBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 処理中...';

                const formData = new FormData(form);
                // APIエンドポイントを修正
                const response = await fetch('../api/add_sale_api.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    // カスタムモーダルまたはトーストを使用することを推奨しますが、元のコードに合わせてalertを使用
                    alert('成功: ' + data.message);
                    form.reset();
                    form.classList.remove('was-validated');
                    confirmModal.hide(); 
                } else {
                    alert('失敗: ' + data.message);
                }

                executeSaleBtn.disabled = false;
                executeSaleBtn.innerHTML = '売り上げを確定';
            });
        });
    </script>
</body>
</html>