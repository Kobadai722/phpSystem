// inventory.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    loadInventory(); // ページ読み込み時に在庫リストを取得・表示

    // Bootstrapモーダルのインスタンスを取得
    const addConfirmModal = new bootstrap.Modal(document.getElementById('addConfirmModal'));
    
    // モーダルの各要素への参照を取得
    const editProductIdInput = document.getElementById('editProductId');
    const currentProductNameSpan = document.getElementById('currentProductName');
    const productNameInput = document.getElementById('name');
    const stockQuantityInput = document.getElementById('stockQuantity');
    const unitPriceInput = document.getElementById('unitPrice');
    const productCategorySelect = document.getElementById('productCategory');
    const saveConfirmButton = document.getElementById('saveConfirmButton');

    // tbody要素に対してイベント委譲を設定（動的に追加されるボタンに対応）
    const tbody = document.querySelector("tbody");
    tbody.addEventListener('click', function(event) {
        // クリックされた要素が「編集」ボタンかどうかをチェック
        if (event.target.classList.contains('edit-product-btn')) {
            const button = event.target;
            const productId = button.dataset.productId; // data-product-id から商品IDを取得

            // モーダル表示前に、編集対象の商品IDを隠しフィールドにセット
            editProductIdInput.value = productId;

            // 以前のバリデーションメッセージをクリア
            productNameInput.classList.remove('is-invalid');
            stockQuantityInput.classList.remove('is-invalid');
            unitPriceInput.classList.remove('is-invalid');
            productCategorySelect.classList.remove('is-invalid');

            // 商品情報をAPIから取得し、モーダルに設定
            fetch(`../api/get_product_details.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        currentProductNameSpan.textContent = product.PRODUCT_NAME;
                        productNameInput.value = product.PRODUCT_NAME;
                        stockQuantityInput.value = product.STOCK_QUANTITY;
                        unitPriceInput.value = product.UNIT_SELLING_PRICE;
                        productCategorySelect.value = product.PRODUCT_KUBUN_ID; // PRODUCT_KUBUN_ID を使用

                        // Bootstrapのdata属性でモーダルが開くため、ここでは手動で表示しない
                        // addConfirmModal.show(); // この行は不要
                    } else {
                        alert('商品情報の取得に失敗しました: ' + data.message);
                        // 失敗した場合、モーダルが空のまま開くのを防ぐため閉じる（Bootstrapで開いていれば手動で閉じる必要あり）
                        addConfirmModal.hide(); 
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('商品情報の取得中にエラーが発生しました。');
                    addConfirmModal.hide(); // エラー時も閉じる
                });
        }
    });

    // モーダルの「保存」ボタンのクリックイベントを処理
    saveConfirmButton.addEventListener('click', function() {
        const productId = editProductIdInput.value;
        const newProductName = productNameInput.value.trim();
        const newStockQuantity = stockQuantityInput.value.trim();
        const newUnitPrice = unitPriceInput.value.trim();
        const newProductCategory = productCategorySelect.value;

        let isValid = true;

        // クライアントサイドでのバリデーションチェック
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
                loadInventory(); // 在庫リストを再読み込みして最新の情報を表示
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


// 在庫情報を取得して表示する関数
function loadInventory() {
    fetch("../api/inventory_api.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.querySelector("tbody");
            tbody.innerHTML = "";

            if (data.length === 0) {
                const row = `<tr><td colspan="6" class="text-center">在庫データが存在しません</td></tr>`;
                tbody.insertAdjacentHTML("beforeend", row);
                return;
            }

            data.forEach(item => {
                const productId = item.PRODUCT_ID ?? '';
                const productName = item.PRODUCT_NAME ?? '';
                const stockQuantity = item.STOCK_QUANTITY ?? '';
                const unitSellingPrice = item.UNIT_SELLING_PRICE ?? '';
                const productKubunName = item.PRODUCT_KUBUN_NAME ?? ''; 

                const tr = `
                    <tr>
                        <td>${productId}</td>
                        <td>${productName}</td>
                        <td>${stockQuantity}</td>
                        <td>${unitSellingPrice}</td>
                        <td>${productKubunName}</td>
                        <td>
                            <button
                                class="btn btn-outline-primary btn-sm edit-product-btn" data-bs-toggle="modal"                         data-bs-target="#addConfirmModal"               data-product-id="${productId}">
                                編集
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML("beforeend", tr);
            });
        })
        .catch(error => {
            console.error('在庫情報の取得中にエラーが発生しました:', error);
            const tbody = document.querySelector("tbody");
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">在庫データの取得中にエラーが発生しました。</td></tr>`;
        });
}