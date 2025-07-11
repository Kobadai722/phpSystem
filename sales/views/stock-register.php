<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい商品の追加</title>
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
                <h2 class="mb-4">新しい商品の追加</h2>
                
                <form id="productRegisterForm" action="add_product_process.php" method="POST"> 
                    
                    <div class="mb-3">
                        <label for="product_name" class="form-label">商品名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required maxlength="20">
                        <div class="invalid-feedback" id="product_name_error"></div> <div class="form-text text-muted">最大20文字まで</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">単価 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price" required min="0" step="1">
                        <div class="invalid-feedback" id="unit_price_error"></div> <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">初期在庫 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" required min="0" step="1">
                        <div class="invalid-feedback" id="initial_stock_error"></div> <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_category" class="form-label">商品区分 <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_category" name="product_category" required>
                            <option value="">選択してください</option>
                            <option value="1">作業用品</option>
                            <option value="2">オフィス用品</option>
                            <option value="3">医療・衛生用品</option>
                            <option value="4">IT・デジタル機器</option>
                            <option value="5">建築・土木資材</option>
                        </select>
                        <div class="invalid-feedback" id="product_category_error"></div> </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitFormBtn"> 
                            <i class="bi bi-save me-2"></i>登録する
                        </button>
                        <a href="stock_management.php" class="btn btn-secondary btn-lg">
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
                    <h5 class="modal-title" id="confirmModalLabel">登録内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>以下の内容で商品を登録します。よろしいですか？</p>
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25">商品名</th>
                            <td id="confirm_product_name"></td>
                        </tr>
                        <tr>
                            <th>単価</th>
                            <td id="confirm_unit_price"></td>
                        </tr>
                        <tr>
                            <th>初期在庫</th>
                            <td id="confirm_initial_stock"></td>
                        </tr>
                        <tr>
                            <th>商品区分</th>
                            <td id="confirm_product_category"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-success" id="registerProductBtn">
                        <span id="registerProductBtnText">登録する</span>
                        <span id="registerProductBtnSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span>
                        <span id="registerProductBtnLoadingText" style="display:none;"> 登録中...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">登録結果</h5>
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
            const productRegisterForm = document.getElementById('productRegisterForm');
            const submitFormBtn = document.getElementById('submitFormBtn'); // submitボタンをIDで取得
            const registerProductBtn = document.getElementById('registerProductBtn'); // 確認モーダル内の登録ボタン
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            const resultModalBody = document.getElementById('resultModalBody');

            // ローディングスピナー要素 (確認モーダル内のボタン用)
            const registerProductBtnText = document.getElementById('registerProductBtnText');
            const registerProductBtnSpinner = document.getElementById('registerProductBtnSpinner');
            const registerProductBtnLoadingText = document.getElementById('registerProductBtnLoadingText');

            // バリデーションエラー表示用要素
            const productNameInput = document.getElementById('product_name');
            const unitPriceInput = document.getElementById('unit_price');
            const initialStockInput = document.getElementById('initial_stock');
            const productCategorySelect = document.getElementById('product_category');

            const productNameError = document.getElementById('product_name_error');
            const unitPriceError = document.getElementById('unit_price_error');
            const initialStockError = document.getElementById('initial_stock_error');
            const productCategoryError = document.getElementById('product_category_error');

            // エラーメッセージとBootstrapのis-invalidクラスをクリアする関数
            function clearValidationErrors() {
                productNameInput.classList.remove('is-invalid');
                unitPriceInput.classList.remove('is-invalid');
                initialStockInput.classList.remove('is-invalid');
                productCategorySelect.classList.remove('is-invalid');
                productNameError.textContent = '';
                unitPriceError.textContent = '';
                initialStockError.textContent = '';
                productCategoryError.textContent = '';
            }

            // バリデーションを実行する関数
            function validateForm() {
                clearValidationErrors(); // まず全てのエラーをクリア

                let isValid = true;

                // 商品名バリデーション
                const productName = productNameInput.value.trim();
                if (productName === '') {
                    productNameInput.classList.add('is-invalid');
                    productNameError.textContent = '商品名は必須です。';
                    isValid = false;
                } else if (productName.length > 20) {
                    productNameInput.classList.add('is-invalid');
                    productNameError.textContent = '商品名は20文字以内で入力してください。';
                    isValid = false;
                }

                // 単価バリデーション
                const unitPrice = unitPriceInput.value;
                if (unitPrice === '' || isNaN(unitPrice) || parseInt(unitPrice) < 0 || !Number.isInteger(parseFloat(unitPrice))) {
                    unitPriceInput.classList.add('is-invalid');
                    unitPriceError.textContent = '単価は0以上の整数を入力してください。';
                    isValid = false;
                }

                // 初期在庫バリデーション
                const initialStock = initialStockInput.value;
                if (initialStock === '' || isNaN(initialStock) || parseInt(initialStock) < 0 || !Number.isInteger(parseFloat(initialStock))) {
                    initialStockInput.classList.add('is-invalid');
                    initialStockError.textContent = '初期在庫は0以上の整数を入力してください。';
                    isValid = false;
                }

                // 商品区分バリデーション
                const productCategoryValue = productCategorySelect.value;
                if (productCategoryValue === '') { // valueが空の場合（"選択してください"が選ばれている場合）
                    productCategorySelect.classList.add('is-invalid');
                    productCategoryError.textContent = '商品区分を選択してください。';
                    isValid = false;
                }

                return isValid;
            }

            // フォームのsubmitイベントを捕捉
            productRegisterForm.addEventListener('submit', function(event) {
                event.preventDefault(); // デフォルトのフォーム送信をキャンセル

                if (!validateForm()) {
                    // バリデーションエラーがあれば何もしない
                    return;
                }

                // 確認モーダルに表示する内容を設定
                document.getElementById('confirm_product_name').textContent = productNameInput.value.trim();
                document.getElementById('confirm_unit_price').textContent = unitPriceInput.value;
                document.getElementById('confirm_initial_stock').textContent = initialStockInput.value;
                document.getElementById('confirm_product_category').textContent = productCategorySelect.options[productCategorySelect.selectedIndex].text;

                confirmModal.show(); // 確認モーダルを表示
            });

            // 確認モーダル内の「登録する」ボタンのクリックイベント
            registerProductBtn.addEventListener('click', async function() {
                confirmModal.hide(); // 確認モーダルを閉じる

                // ローディング表示開始
                registerProductBtnText.style.display = 'none';
                registerProductBtnSpinner.style.display = 'inline-block';
                registerProductBtnLoadingText.style.display = 'inline';
                registerProductBtn.disabled = true; // ボタンを無効化

                const formData = new FormData(productRegisterForm);

                try {
                    // AJAXリクエストは既存のadd_api.phpに対して行います
                    const response = await fetch('../api/add_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) { // HTTPステータスが2xx以外の場合
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json(); // JSONレスポンスをパース

                    if (data.success) {
                        resultModalBody.innerHTML = `<div class="alert alert-success" role="alert">${data.message}</div>`;
                        productRegisterForm.reset(); // フォームをリセット
                        clearValidationErrors(); // バリデーションエラー表示もクリア
                    } else {
                        resultModalBody.innerHTML = `<div class="alert alert-danger" role="alert">${data.message}</div>`;
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    resultModalBody.innerHTML = `<div class="alert alert-danger" role="alert">登録中にエラーが発生しました。<br>詳細: ${error.message}</div>`;
                } finally {
                    // ローディング表示終了
                    registerProductBtnText.style.display = 'inline';
                    registerProductBtnSpinner.style.display = 'none';
                    registerProductBtnLoadingText.style.display = 'none';
                    registerProductBtn.disabled = false; // ボタンを有効化
                    
                    resultModal.show(); // 結果モーダルを表示
                }
            });

            // フォームのリセット時にバリデーションエラーもクリア
            productRegisterForm.addEventListener('reset', function() {
                clearValidationErrors();
            });
        });
    </script>
</body>
</html>