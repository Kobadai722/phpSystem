<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい商品の追加</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <main>
        <?php include 'localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">新しい商品の追加</h2>
                
                <form id="productRegisterForm"> <div class="mb-3">
                        <label for="product_name" class="form-label">商品名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required maxlength="20">
                        <div class="form-text text-muted">最大20文字まで</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">単価 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price" required min="0" step="1">
                        <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">初期在庫 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" required min="0" step="1">
                        <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_category" class="form-label">商品区分 <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_category" name="product_category" required>
                            <option value="">選択してください</option>
                            <option value="文房具">文房具</option>
                            <option value="衛生用品">衛生用品</option>
                            <option value="食品">食品</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-success btn-lg" id="openConfirmModal"> <i class="bi bi-save me-2"></i>登録する
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
                    <table class="table">
                        <tr>
                            <th>商品名</th>
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
                    <button type="button" class="btn btn-success" id="registerProductBtn">登録する</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">登録結果</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
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
            const openConfirmModalBtn = document.getElementById('openConfirmModal');
            const registerProductBtn = document.getElementById('registerProductBtn');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            const messageModalBody = document.getElementById('messageModalBody');

            openConfirmModalBtn.addEventListener('click', function() {
                // フォームの入力値を取得
                const productName = document.getElementById('product_name').value;
                const unitPrice = document.getElementById('unit_price').value;
                const initialStock = document.getElementById('initial_stock').value;
                const productCategorySelect = document.getElementById('product_category');
                const productCategory = productCategorySelect.options[productCategorySelect.selectedIndex].text;

                // バリデーションチェック（簡易的なもの）
                let errors = [];
                if (!productName) {
                    errors.push('商品名は必須です。');
                } else if (productName.length > 20) {
                    errors.push('商品名は20文字以内で入力してください。');
                }
                if (unitPrice === '' || isNaN(unitPrice) || parseInt(unitPrice) < 0) {
                    errors.push('単価は0以上の整数で入力してください。');
                }
                if (initialStock === '' || isNaN(initialStock) || parseInt(initialStock) < 0) {
                    errors.push('初期在庫は0以上の整数で入力してください。');
                }
                if (productCategorySelect.value === '') {
                    errors.push('商品区分を選択してください。');
                }

                if (errors.length > 0) {
                    messageModalBody.innerHTML = '<div class="alert alert-danger">' + errors.join('<br>') + '</div>';
                    messageModal.show();
                    return;
                }

                // モーダルに表示する内容を設定
                document.getElementById('confirm_product_name').textContent = productName;
                document.getElementById('confirm_unit_price').textContent = unitPrice;
                document.getElementById('confirm_initial_stock').textContent = initialStock;
                document.getElementById('confirm_product_category').textContent = productCategory;

                // 確認モーダルを表示
                confirmModal.show();
            });

            registerProductBtn.addEventListener('click', function() {
                // 確認モーダルを閉じる
                confirmModal.hide();

                // フォームデータを取得
                const formData = new FormData(productRegisterForm);
                // product_categoryの表示名を送信する代わりに、optionのvalueを送信
                formData.set('product_category', document.getElementById('product_category').value);


                // AJAXリクエストを送信
                fetch('add_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageModalBody.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        // 成功したらフォームをリセットするなどの処理
                        productRegisterForm.reset();
                    } else {
                        messageModalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                    messageModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageModalBody.innerHTML = `<div class="alert alert-danger">データの登録中にエラーが発生しました。</div>`;
                    messageModal.show();
                });
            });
        });
    </script>
</body>
</html>