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
                        <div class="invalid-feedback">
                            商品名を入力してください
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">単価 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price" required min="0">
                        <div class="invalid-feedback">
                            単価を0以上の整数で入力してください
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">初期在庫 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" required min="0">
                        <div class="invalid-feedback">
                            初期在庫を0以上の整数で入力してください
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_category" class="form-label">商品区分 <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_category" name="product_category" required>
                            <option value="">選択してください</option>
                            </select>
                        <div class="invalid-feedback">
                            商品区分を選択してください
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">備考/説明</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"></textarea> <div class="form-text text-muted">商品の詳細情報や特記事項を記入してください。（500文字以内）</div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="registerProductBtn">
                            <span id="registerProductBtnText">商品を登録</span>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;" id="registerProductBtnSpinner"></span>
                            <span id="registerProductBtnLoadingText" style="display: none;">登録中...</span>
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">入力内容をリセット</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">商品登録結果</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const productCategorySelect = document.getElementById('product_category');
            const productRegisterForm = document.getElementById('productRegisterForm');
            const registerProductBtn = document.getElementById('registerProductBtn');
            const registerProductBtnText = document.getElementById('registerProductBtnText');
            const registerProductBtnSpinner = document.getElementById('registerProductBtnSpinner');
            const registerProductBtnLoadingText = document.getElementById('registerProductBtnLoadingText');
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            const resultModalBody = document.getElementById('resultModalBody');

            // 商品区分をAPIから読み込む関数
            async function loadProductCategories() {
                try {
                    const response = await fetch('../api/product_kubun_api.php'); // 商品区分APIのパス
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const categories = await response.json();
                    
                    // 既存のオプションをクリア（「選択してください」以外）
                    while (productCategorySelect.children.length > 1) {
                        productCategorySelect.removeChild(productCategorySelect.lastChild);
                    }

                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.PRODUCT_KUBUN_ID;
                        option.textContent = category.PRODUCT_KUBUN_NAME;
                        productCategorySelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('商品区分の読み込みに失敗しました:', error);
                    // エラーメッセージを表示するなどの処理
                }
            }

            // 初期ロード時に商品区分を読み込む
            loadProductCategories();

            // バリデーションエラーをクリアする関数
            function clearValidationErrors() {
                const inputs = productRegisterForm.querySelectorAll('.form-control, .form-select');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = '';
                    }
                });
            }

            // フォームの入力値をチェックする関数
            function validateForm() {
                let isValid = true;
                clearValidationErrors(); // まずエラー表示をクリア

                const productName = document.getElementById('product_name');
                const unitPrice = document.getElementById('unit_price');
                const initialStock = document.getElementById('initial_stock');
                const productCategory = document.getElementById('product_category');
                const description = document.getElementById('description'); // descriptionフィールドも取得

                if (productName.value.trim() === '') {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent = '商品名は必須です。';
                    isValid = false;
                } else if (productName.value.length > 20) {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent = '商品名は20文字以内で入力してください。';
                    isValid = false;
                }

                if (unitPrice.value.trim() === '' || isNaN(unitPrice.value) || parseInt(unitPrice.value) < 0) {
                    unitPrice.classList.add('is-invalid');
                    unitPrice.nextElementSibling.textContent = '単価を0以上の整数で入力してください。';
                    isValid = false;
                }

                if (initialStock.value.trim() === '' || isNaN(initialStock.value) || parseInt(initialStock.value) < 0) {
                    initialStock.classList.add('is-invalid');
                    initialStock.nextElementSibling.textContent = '初期在庫を0以上の整数で入力してください。';
                    isValid = false;
                }

                if (productCategory.value === '') {
                    productCategory.classList.add('is-invalid');
                    productCategory.nextElementSibling.textContent = '商品区分を選択してください。';
                    isValid = false;
                }

                // descriptionのバリデーション
                if (description.value.length > 500) {
                    description.classList.add('is-invalid');
                    description.nextElementSibling.textContent = '備考/説明は500文字以内で入力してください。';
                    isValid = false;
                }


                return isValid;
            }

            // フォーム送信イベントリスナー
            productRegisterForm.addEventListener('submit', async (event) => {
                event.preventDefault(); // デフォルトのフォーム送信を防止

                if (!validateForm()) {
                    return; // バリデーションに失敗したら処理を中断
                }

                // ローディング表示開始
                registerProductBtnText.style.display = 'none';
                registerProductBtnSpinner.style.display = 'inline-block';
                registerProductBtnLoadingText.style.display = 'inline';
                registerProductBtn.disabled = true; // ボタンを無効化

                const formData = new FormData(productRegisterForm); // フォームデータを取得

                try {
                    const response = await fetch('../api/add_api.php', { // 登録APIのパス
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        // HTTPエラーレスポンスの場合
                        const errorText = await response.text();
                        throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                    }

                    const data = await response.json(); // JSONレスポンスをパース

                    if (data.success) {
                        resultModalBody.innerHTML = `<div class="alert alert-success" role="alert">${data.message}</div>`;
                        productRegisterForm.reset(); // フォームをリセット
                        clearValidationErrors(); // バリデーションエラー表示もクリア
                        // 必要であれば、商品IDを表示するなど
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