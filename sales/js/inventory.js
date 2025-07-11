// inventory.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    loadInventory(); // ページ読み込み時に在庫リストを取得・表示

    const addConfirmModal = new bootstrap.Modal(document.getElementById('addConfirmModal'));
    
    // 隠しフィールド
    const editProductIdInput = document.getElementById('editProductId');

    // 現在の情報を表示する要素への参照 (stock_management.php のIDに合わせて調整)
    const currentProductNameSpan = document.getElementById('currentProductName');
    const currentStockQuantitySpan = document.getElementById('currentStockQuantity');
    const currentUnitPriceSpan = document.getElementById('currentUnitPrice');
    const currentProductCategorySpan = document.getElementById('currentProductCategory');

    // 変更用の入力フォーム要素への参照 (stock_management.php のIDに合わせて調整)
    const inputProductName = document.getElementById('name'); // id="name"
    const inputStockQuantity = document.getElementById('stockQuantity'); // id="stockQuantity"
    const inputUnitPrice = document.getElementById('unitPrice'); // id="unitPrice"
    const inputProductCategory = document.getElementById('productCategory'); // id="productCategory" (select要素)
    
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
            inputProductName.classList.remove('is-invalid');
            inputStockQuantity.classList.remove('is-invalid');
            inputUnitPrice.classList.remove('is-invalid');
            inputProductCategory.classList.remove('is-invalid');

            // 商品情報をAPIから取得し、モーダルに設定
            fetch(`../api/get_product_details.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        
                        // 現在の情報を表示する<span>に値をセット
                        currentProductNameSpan.textContent = product.PRODUCT_NAME;
                        currentStockQuantitySpan.textContent = product.STOCK_QUANTITY;
                        currentUnitPriceSpan.textContent = product.UNIT_SELLING_PRICE;
                        currentProductCategorySpan.textContent = product.PRODUCT_KUBUN_NAME; // 商品区分名を表示

                        // 変更用の入力フォームに現在の値をセット
                        inputProductName.value = product.PRODUCT_NAME;
                        inputStockQuantity.value = product.STOCK_QUANTITY;
                        inputUnitPrice.value = product.UNIT_SELLING_PRICE;
                        inputProductCategory.value = product.PRODUCT_KUBUN_ID; // 商品区分IDをセット
                        
                    } else {
                        alert('商品情報の取得に失敗しました: ' + data.message);
                        // データ取得に失敗した場合、モーダルが空のまま開くのを防ぐため閉じる
                        addConfirmModal.hide(); 
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('商品情報の取得中にエラーが発生しました。');
                    addConfirmModal.hide();
                });
        }
    });

    // モーダルの「保存」ボタンのクリックイベントを処理
    saveConfirmButton.addEventListener('click', function() {
        const productId = editProductIdInput.value;
        // 変更用の入力フォームから値を取得
        const newProductName = inputProductName.value.trim();
        const newStockQuantity = inputStockQuantity.value.trim();
        const newUnitPrice = inputUnitPrice.value.trim();
        const newProductCategory = inputProductCategory.value; // 商品区分ID

        let isValid = true;

        // クライアントサイドでのバリデーションチェック
        if (newProductName === '') {
            inputProductName.classList.add('is-invalid');
            isValid = false;
        } else {
            inputProductName.classList.remove('is-invalid');
        }

        if (newStockQuantity === '' || isNaN(newStockQuantity) || parseInt(newStockQuantity) < 0) {
            inputStockQuantity.classList.add('is-invalid');
            isValid = false;
        } else {
            inputStockQuantity.classList.remove('is-invalid');
        }
        
        if (newUnitPrice === '' || isNaN(newUnitPrice) || parseFloat(newUnitPrice) < 0) {
            inputUnitPrice.classList.add('is-invalid');
            isValid = false;
        } else {
            inputUnitPrice.classList.remove('is-invalid');
        }

        if (newProductCategory === '') {
            inputProductCategory.classList.add('is-invalid');
            isValid = false;
        } else {
            inputProductCategory.classList.remove('is-invalid');
        }

        if (!isValid) {
            return;
        }

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
                addConfirmModal.hide();
                loadInventory(); // テーブルのデータを再読み込みして最新の状態にする
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
        inputProductName.classList.remove('is-invalid');
        inputStockQuantity.classList.remove('is-invalid');
        inputUnitPrice.classList.remove('is-invalid');
        inputProductCategory.classList.remove('is-invalid');
    });
});


// 在庫情報を取得して表示する関数 (stock_management.phpの<tbody>が空のため、必須)
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
            tbody.innerHTML = ""; // 一度中身をクリア

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
                                class="btn btn-outline-primary btn-sm edit-product-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#addConfirmModal"
                                data-product-id="${productId}">
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