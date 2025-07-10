// inventory.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    loadInventory(); // ページ読み込み時に在庫リストを取得・表示

    const addConfirmModal = new bootstrap.Modal(document.getElementById('addConfirmModal'));
    const editProductIdInput = document.getElementById('editProductId');
    const currentProductNameSpan = document.getElementById('currentProductName');
    const productNameInput = document.getElementById('name');
    const stockQuantityInput = document.getElementById('stockQuantity');
    const unitPriceInput = document.getElementById('unitPrice');
    const productCategorySelect = document.getElementById('productCategory');
    const saveConfirmButton = document.getElementById('saveConfirmButton');

    // tbody要素に対してイベント委譲を設定
    const tbody = document.querySelector("tbody");
    tbody.addEventListener('click', function(event) {
        // クリックされた要素が「編集」ボタンかどうかをチェック
        if (event.target.classList.contains('edit-product-btn')) {
            const button = event.target;
            const productId = button.dataset.productId;

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

        // クライアントサイドでのバリデーションチェック (簡易版)
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
    // PHPのAPIにGETリクエストを送信
    fetch("../api/inventory_api.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json(); // レスポンスをJSON形式に変換
        })
        .then(data => {
            // テーブルの tbody 要素を取得
            const tbody = document.querySelector("tbody");
            tbody.innerHTML = ""; // 一度中身をクリア

            // データが0件の場合、メッセージを表示
            if (data.length === 0) {
                const row = `<tr><td colspan="6" class="text-center">在庫データが存在しません</td></tr>`; // colspanを6に修正
                tbody.insertAdjacentHTML("beforeend", row);
                return;
            }

            // 在庫データをテーブルに表示
            data.forEach(item => {
                // 各値が null の場合は空文字に置き換えて表示
                const productId = item.PRODUCT_ID ?? '';
                const productName = item.PRODUCT_NAME ?? '';
                const stockQuantity = item.STOCK_QUANTITY ?? '';
                const unitSellingPrice = item.UNIT_SELLING_PRICE ?? '';
                const productKubunName = item.PRODUCT_KUBUN_NAME ?? ''; // lastUpdated から変更

                const tr = `
                    <tr>
                        <td>${productId}</td>
                        <td>${productName}</td>
                        <td>${stockQuantity}</td>
                        <td>${unitSellingPrice}</td>
                        <td>${productKubunName}</td> 
                        <td>
                            <button 
                                class="btn btn-info btn-sm edit-product-btn" data-product-id="${productId}">
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
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">在庫データの取得中にエラーが発生しました。</td></tr>`; // colspanを6に修正
        });
}