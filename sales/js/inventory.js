document.addEventListener('DOMContentLoaded', function() {
    const addConfirmModal = new bootstrap.Modal(document.getElementById('addConfirmModal'));
    const editProductIdInput = document.getElementById('editProductId');
    const currentProductNameSpan = document.getElementById('currentProductName');
    const productNameInput = document.getElementById('name');
    const stockQuantityInput = document.getElementById('stockQuantity');
    const unitPriceInput = document.getElementById('unitPrice');
    const productCategorySelect = document.getElementById('productCategory');
    const saveConfirmButton = document.getElementById('saveConfirmButton');

    // 「編集」ボタンのクリックイベントを処理
    document.querySelectorAll('.edit-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            editProductIdInput.value = productId; // 隠しフィールドに商品IDを設定

            // 以前のバリデーションメッセージをクリア
            productNameInput.classList.remove('is-invalid');
            stockQuantityInput.classList.remove('is-invalid');
            unitPriceInput.classList.remove('is-invalid');
            productCategorySelect.classList.remove('is-invalid');


            // 商品情報をAPIから取得
            fetch(`../api/get_product_details.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        currentProductNameSpan.textContent = product.PRODUCT_NAME; // 現在の商品名を表示
                        productNameInput.value = product.PRODUCT_NAME; // 新しい商品名の入力欄に現在の名前をセット
                        stockQuantityInput.value = product.STOCK_QUANTITY;
                        unitPriceInput.value = product.UNIT_SELLING_PRICE;
                        productCategorySelect.value = product.PRODUCT_KUBUN_ID; // 選択肢のvalue属性と一致させる

                        // モーダルを表示
                        addConfirmModal.show();
                    } else {
                        alert('商品情報の取得に失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('商品情報の取得中にエラーが発生しました。');
                });
        });
    });

    // モーダルの「保存」ボタンのクリックイベントを処理
    saveConfirmButton.addEventListener('click', function() {
        const productId = editProductIdInput.value;
        const newProductName = productNameInput.value.trim();
        const newStockQuantity = stockQuantityInput.value.trim();
        const newUnitPrice = unitPriceInput.value.trim();
        const newProductCategory = productCategorySelect.value;

        let isValid = true;

        // バリデーションチェック (簡易版)
        if (newProductName === '') {
            productNameInput.classList.add('is-invalid');
            isValid = false;
        } else {
            productNameInput.classList.remove('is-invalid');
        }

        if (newStockQuantity === '' || isNaN(newStockQuantity) || parseInt(newStockQuantity) < 0) {
            stockQuantityInput.classList.add('is-invalid');
            isValid = false;
        } else {
            stockQuantityInput.classList.remove('is-invalid');
        }
        
        if (newUnitPrice === '' || isNaN(newUnitPrice) || parseFloat(newUnitPrice) < 0) {
            unitPriceInput.classList.add('is-invalid');
            isValid = false;
        } else {
            unitPriceInput.classList.remove('is-invalid');
        }

        if (newProductCategory === '') {
            productCategorySelect.classList.add('is-invalid');
            isValid = false;
        } else {
            productCategorySelect.classList.remove('is-invalid');
        }

        if (!isValid) {
            return; // バリデーションエラーがあれば送信しない
        }

        // FormDataオブジェクトを作成してAPIに送信
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('product_name', newProductName);
        formData.append('stock_quantity', newStockQuantity);
        formData.append('unit_price', newUnitPrice);
        formData.append('product_category', newProductCategory);

        fetch('../api/update_product_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('商品情報が正常に更新されました。');
                addConfirmModal.hide(); // モーダルを閉じる
                location.reload(); // ページをリロードして最新の情報を表示
            } else {
                alert('商品情報の更新に失敗しました: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('商品情報の更新中にエラーが発生しました。');
        });
    });

    // モーダルが閉じられたときにフォームのバリデーション状態をリセット
    document.getElementById('addConfirmModal').addEventListener('hidden.bs.modal', function () {
        productNameInput.classList.remove('is-invalid');
        stockQuantityInput.classList.remove('is-invalid');
        unitPriceInput.classList.remove('is-invalid');
        productCategorySelect.classList.remove('is-invalid');
    });
});