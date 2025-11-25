<?php
/**
 * sale_add.php
 * 新しい売上を登録するためのフォーム。
 * config.phpから$PDOオブジェクトを受け取り、商品と顧客のリストを取得します。
 */
// 実際のconfig.phpへのパスを仮定
require_once '../../config.php'; 

$products = [];
$customers = [];
$error_message = null;

try {
    // 1. 商品リスト取得処理
    // PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE のカラムを想定
    $stmt_products = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
    $stmt_products->execute();
    $products = $stmt_products->fetchAll(\PDO::FETCH_ASSOC);
    
    // 2. 顧客リスト取得処理
    // CUSTOMER_ID, CUSTOMER_NAME のカラムを想定
    $stmt_customers = $PDO->prepare("SELECT CUSTOMER_ID, CUSTOMER_NAME FROM CUSTOMER ORDER BY CUSTOMER_ID");
    $stmt_customers->execute();
    $customers = $stmt_customers->fetchAll(\PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    // データ取得エラーの場合
    $error_message = "データ取得エラー: フォーム表示に必要な商品・顧客情報の取得に失敗しました。(" . $e->getMessage() . ")";
    error_log($error_message);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい売上の登録</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- ユーザーのスタイルシートパスに合わせて調整 -->
    <link rel="stylesheet" href="../css/styles.css"> 
    <style>
        .container { max-width: 700px; }
        .alert-box { border-radius: 0.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container bg-light p-5 rounded-3 shadow-lg">
                <h2 class="mb-4 border-bottom pb-2">新しい売上の登録</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-box" role="alert">
                        <strong>エラーが発生しました:</strong><br><?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php else: ?>
                    <div id="message_area" class="alert-box" style="display: none;"></div>

                    <form id="saleAddForm" method="POST" novalidate> 
                        
                        <!-- 顧客選択 -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-bold">顧客 <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">選択してください</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($customer['CUSTOMER_ID']); ?>"
                                        data-name="<?php echo htmlspecialchars($customer['CUSTOMER_NAME']); ?>"
                                    >
                                        <?php echo htmlspecialchars($customer['CUSTOMER_NAME']); ?> 
                                        (ID: <?php echo htmlspecialchars($customer['CUSTOMER_ID']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">顧客を選択してください。</div>
                        </div>

                        <!-- 商品選択 -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label fw-bold">商品 <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">選択してください</option>
                                <?php foreach ($products as $product): ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($product['PRODUCT_ID']); ?>"
                                        data-name="<?php echo htmlspecialchars($product['PRODUCT_NAME']); ?>"
                                        data-price="<?php echo htmlspecialchars($product['UNIT_SELLING_PRICE']); ?>"
                                    >
                                        <?php echo htmlspecialchars($product['PRODUCT_NAME']); ?> 
                                        (単価: <?php echo number_format($product['UNIT_SELLING_PRICE']); ?>円)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">商品を選択してください。</div>
                        </div>
                        
                        <!-- 販売数量 -->
                        <div class="mb-3">
                            <label for="sale_quantity" class="form-label fw-bold">販売数量 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="sale_quantity" name="sale_quantity" required min="1" step="1" value="1">
                            <div class="invalid-feedback">販売数量は1以上の整数を入力してください。</div>
                        </div>

                        <!-- 販売日 -->
                        <div class="mb-3">
                            <label for="sale_date" class="form-label fw-bold">販売日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="sale_date" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">販売日を選択してください。</div>
                        </div>

                        <!-- 備考 -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">備考</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255"></textarea>
                            <div class="form-text text-muted">最大255文字まで</div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitFormBtn"> 
                                <i class="bi bi-cash-stack me-2"></i>売上内容を確認
                            </button>
                            <!-- 実際にはメインページなどに戻るリンクに修正してください -->
                            <a href="#" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i>キャンセル
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <!-- 確認モーダル -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="confirmModalLabel"><i class="bi bi-check2-square me-2"></i>売上内容の確認</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">以下の内容で売上を登録します。よろしいですか？</p>

                    <table class="table table-striped table-bordered">
                        <tr><th>顧客</th><td id="confirmCustomerName"></td></tr>
                        <tr><th>商品名</th><td id="confirmProductName"></td></tr>
                        <tr><th>単価</th><td id="confirmProductPrice"></td></tr>
                        <tr><th>数量</th><td id="confirmQuantity"></td></tr>
                        <tr><th><strong>合計金額</strong></th><td id="confirmTotalPrice" class="fw-bold text-danger"></td></tr>
                        <tr><th>販売日</th><td id="confirmSaleDate"></td></tr>
                        <tr><th>備考</th><td id="confirmNotes"></td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="confirmSaleBtn"><i class="bi bi-check-circle me-2"></i>登録を確定</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('saleAddForm');
            const confirmBtn = document.getElementById('confirmSaleBtn');
            const messageArea = document.getElementById('message_area');
            const modalElement = document.getElementById('confirmModal');
            const confirmModal = new bootstrap.Modal(modalElement);
            
            // モーダル内の各要素取得
            const confirmCustomerName = document.getElementById('confirmCustomerName');
            const confirmProductName = document.getElementById('confirmProductName');
            const confirmProductPrice = document.getElementById('confirmProductPrice');
            const confirmQuantity = document.getElementById('confirmQuantity');
            const confirmTotalPrice = document.getElementById('confirmTotalPrice');
            const confirmSaleDate = document.getElementById('confirmSaleDate');
            const confirmNotes = document.getElementById('confirmNotes');
            
            // 通貨フォーマット関数
            const formatCurrency = (amount) => {
                return amount.toLocaleString('ja-JP', { style: 'currency', currency: 'JPY' });
            };
            
            // メッセージ表示関数
            const displayMessage = (type, text) => {
                messageArea.className = `alert alert-${type} alert-box`;
                messageArea.innerHTML = `<strong>${type === 'success' ? '成功' : '失敗'}:</strong> ${text}`;
                messageArea.style.display = 'block';
                // ページトップへスクロール
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            // submitボタン押下時の処理 (確認モーダル表示)
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                messageArea.style.display = 'none'; // メッセージを隠す
                
                if (form.checkValidity()) {
                    // 選択された商品と顧客の情報を取得
                    const customerSelect = document.getElementById('customer_id');
                    const productSelect = document.getElementById('product_id');
                    const selectedCustomerOption = customerSelect.options[customerSelect.selectedIndex];
                    const selectedProductOption = productSelect.options[productSelect.selectedIndex];
                    
                    // data-name属性から顧客名を取得。ない場合はoptionのテキスト全体を使用
                    const customerName = selectedCustomerOption.dataset.name ? selectedCustomerOption.dataset.name.trim() : selectedCustomerOption.textContent.trim();
                    const productName = selectedProductOption.dataset.name || '';
                    const price = parseFloat(selectedProductOption.dataset.price || 0);
                    const quantity = parseInt(document.getElementById('sale_quantity').value || 0);
                    const saleDate = document.getElementById('sale_date').value;
                    const notes = document.getElementById('notes').value || '（なし）';
                    
                    const totalPrice = price * quantity;

                    // モーダルに値セット
                    confirmCustomerName.textContent = customerName;
                    confirmProductName.textContent = productName;
                    confirmProductPrice.textContent = formatCurrency(price);
                    confirmQuantity.textContent = quantity + ' 個';
                    confirmTotalPrice.textContent = formatCurrency(totalPrice);
                    confirmSaleDate.textContent = saleDate;
                    confirmNotes.textContent = notes;

                    confirmModal.show();
                } else {
                    form.classList.add('was-validated');
                }
            });

            // 登録確定ボタン押下 (API呼び出し)
            confirmBtn.addEventListener('click', async function() {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>処理中...';

                // *** ここを修正しました！ ***
                // APIエンドポイントのパスを修正: 'add_sale_api.php'
                const apiPath = 'add_sale_api.php'; 

                const formData = new FormData(form);
                
                try {
                    const response = await fetch(apiPath, { method: 'POST', body: formData });
                    
                    if (!response.ok) {
                        // HTTPステータスが200番台以外の場合、API側でエラーログが出ている可能性が高い
                        const errorText = await response.text();
                        console.error('API Response Error Text:', errorText);
                        throw new Error(`サーバーエラー（HTTP ${response.status}）: APIファイルのパスを確認してください。`);
                    }
                    
                    const data = await response.json(); // JSONとしてパースを試みる

                    if (data.success) {
                        displayMessage('success', data.message);
                        form.reset();
                        form.classList.remove('was-validated');
                        // 販売日を今日の日付に戻す
                        document.getElementById('sale_date').value = '<?php echo date('Y-m-d'); ?>'; 
                    } else {
                        displayMessage('danger', data.message);
                    }
                    
                } catch (error) {
                    displayMessage('danger', `通信エラーが発生しました: ${error.message}`);
                    console.error('Fetch Error:', error);
                } finally {
                    confirmModal.hide(); 
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>登録を確定';
                }
            });
        });
    </script>
</body>
</html>