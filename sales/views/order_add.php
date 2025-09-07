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
                
                <form id="orderAddForm" action="add_order_process.php" method="POST"> 
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">商品名 <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">選択してください</option>
                            <option value="1">商品A (単価: 1,500円)</option>
                            <option value="2">商品B (単価: 500円)</option>
                            <option value="3">商品C (単価: 3,200円)</option>
                        </select>
                        <div class="invalid-feedback" id="product_id_error"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_quantity" class="form-label">注文数量 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1">
                        <div class="invalid-feedback" id="order_quantity_error"></div> <div class="form-text text-muted">1以上の整数を入力してください</div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitFormBtn"> 
                            <i class="bi bi-cart-plus me-2"></i>注文する
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
                    <p>以下の内容で注文を登録します。よろしいですか？</p>
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25">商品名</th>
                            <td id="confirm_product_name"></td>
                        </tr>
                        <tr>
                            <th>注文数量</th>
                            <td id="confirm_order_quantity"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-success" id="addOrderBtn">
                        <span id="addOrderBtnText">注文を確定する</span>
                        <span id="addOrderBtnSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span>
                        <span id="addOrderBtnLoadingText" style="display:none;"> 注文処理中...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">注文結果</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderAddForm = document.getElementById('orderAddForm');
            const submitFormBtn = document.getElementById('submitFormBtn');
            const addOrderBtn = document.getElementById('addOrderBtn');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            const resultModalBody = document.getElementById('resultModalBody');

            const addOrderBtnText = document.getElementById('addOrderBtnText');
            const addOrderBtnSpinner = document.getElementById('addOrderBtnSpinner');
            const addOrderBtnLoadingText = document.getElementById('addOrderBtnLoadingText');

            const productIdSelect = document.getElementById('product_id');
            const orderQuantityInput = document.getElementById('order_quantity');
            const productIdError = document.getElementById('product_id_error');
            const orderQuantityError = document.getElementById('order_quantity_error');
            
            function clearValidationErrors() {
                productIdSelect.classList.remove('is-invalid');
                orderQuantityInput.classList.remove('is-invalid');
                productIdError.textContent = '';
                orderQuantityError.textContent = '';
            }

            function validateForm() {
                clearValidationErrors();
                let isValid = true;
                
                const productIdValue = productIdSelect.value;
                if (productIdValue === '') {
                    productIdSelect.classList.add('is-invalid');
                    productIdError.textContent = '商品を選択してください。';
                    isValid = false;
                }
                
                const orderQuantity = orderQuantityInput.value;
                if (orderQuantity === '' || isNaN(orderQuantity) || parseInt(orderQuantity) < 1 || !Number.isInteger(parseFloat(orderQuantity))) {
                    orderQuantityInput.classList.add('is-invalid');
                    orderQuantityError.textContent = '注文数量は1以上の整数を入力してください。';
                    isValid = false;
                }
                
                return isValid;
            }

            orderAddForm.addEventListener('submit', function(event) {
                event.preventDefault();
                if (!validateForm()) {
                    return;
                }

                const selectedProductName = productIdSelect.options[productIdSelect.selectedIndex].text;
                document.getElementById('confirm_product_name').textContent = selectedProductName;
                document.getElementById('confirm_order_quantity').textContent = orderQuantityInput.value;

                confirmModal.show();
            });

            addOrderBtn.addEventListener('click', async function() {
                confirmModal.hide();

                addOrderBtnText.style.display = 'none';
                addOrderBtnSpinner.style.display = 'inline-block';
                addOrderBtnLoadingText.style.display = 'inline';
                addOrderBtn.disabled = true;

                const formData = new FormData(orderAddForm);

                try {
                    const response = await fetch('../api/add_order_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        resultModalBody.innerHTML = `<div class="alert alert-success" role="alert">${data.message}</div>`;
                        orderAddForm.reset();
                        clearValidationErrors();
                    } else {
                        resultModalBody.innerHTML = `<div class="alert alert-danger" role="alert">${data.message}</div>`;
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    resultModalBody.innerHTML = `<div class="alert alert-danger" role="alert">注文処理中にエラーが発生しました。<br>詳細: ${error.message}</div>`;
                } finally {
                    addOrderBtnText.style.display = 'inline';
                    addOrderBtnSpinner.style.display = 'none';
                    addOrderBtnLoadingText.style.display = 'none';
                    addOrderBtn.disabled = false;
                    
                    resultModal.show();
                }
            });

            orderAddForm.addEventListener('reset', function() {
                clearValidationErrors();
            });
        });
    </script>
</body>
</html>