// inventory.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    search(); // ページ読み込み時に商品リストを検索・表示
    setupDeleteModalListeners(); // 削除確認モーダル関連のイベントリスナーを設定

    const addConfirmModal = new bootstrap.Modal(document.getElementById('addConfirmModal')); //
    
    // 隠しフィールド
    const editProductIdInput = document.getElementById('editProductId'); //

    // 現在の情報を表示する要素への参照 (stock_management.php のIDに合わせて調整)
    const currentProductNameSpan = document.getElementById('currentProductName'); //
    const currentStockQuantitySpan = document.getElementById('currentStockQuantity'); //
    const currentUnitPriceSpan = document.getElementById('currentUnitPrice'); //
    const currentProductCategorySpan = document.getElementById('currentProductCategory'); //

    // 変更用の入力フォーム要素への参照 (stock_management.php のIDに合わせて調整)
    const inputProductName = document.getElementById('name'); // id="name"
    const inputStockQuantity = document.getElementById('stockQuantity'); // id="stockQuantity"
    const inputUnitPrice = document.getElementById('unitPrice'); // id="unitPrice"
    const inputProductCategory = document.getElementById('productCategory'); // id="productCategory" (select要素)
    
    const saveConfirmButton = document.getElementById('saveConfirmButton'); //

    // tbody要素に対してイベント委譲を設定（動的に追加されるボタンに対応）
    const tbody = document.querySelector("tbody"); //
    tbody.addEventListener('click', function(event) { //
        // クリックされた要素が「編集」ボタンかどうかをチェック
        if (event.target.classList.contains('edit-product-btn')) { //
            const button = event.target; //
            const productId = button.dataset.productId; // data-product-id から商品IDを取得

            // モーダル表示前に、編集対象の商品IDを隠しフィールドにセット
            editProductIdInput.value = productId; //

            // 以前のバリデーションメッセージをクリア
            inputProductName.classList.remove('is-invalid'); //
            inputStockQuantity.classList.remove('is-invalid'); //
            inputUnitPrice.classList.remove('is-invalid'); //
            inputProductCategory.classList.remove('is-invalid'); //

            // 商品情報をAPIから取得し、モーダルに設定
            fetch(`../api/get_product_details.php?product_id=${productId}`) //
                .then(response => response.json()) //
                .then(data => { //
                    if (data.success) { //
                        const product = data.product; //
                        
                        // 現在の情報を表示する<span>に値をセット
                        currentProductNameSpan.textContent = product.PRODUCT_NAME; //
                        currentStockQuantitySpan.textContent = product.STOCK_QUANTITY; //
                        currentUnitPriceSpan.textContent = product.UNIT_SELLING_PRICE; //
                        currentProductCategorySpan.textContent = product.PRODUCT_KUBUN_NAME; // 商品区分名を表示

                        // 変更用の入力フォームに現在の値をセット
                        inputProductName.value = product.PRODUCT_NAME; //
                        inputStockQuantity.value = product.STOCK_QUANTITY; //
                        inputUnitPrice.value = product.UNIT_SELLING_PRICE; //
                        inputProductCategory.value = product.PRODUCT_KUBUN_ID; // 商品区分IDをセット
                        
                    } else {
                        alert('商品情報の取得に失敗しました: ' + data.message); //
                        // データ取得に失敗した場合、モーダルが空のまま開くのを防ぐため閉じる
                        addConfirmModal.hide(); //
                    }
                })
                .catch(error => { //
                    console.error('Fetch error:', error); //
                    alert('商品情報の取得中にエラーが発生しました。'); //
                    addConfirmModal.hide(); //
                });
        }
        // クリックされた要素が「削除」ボタンかどうかをチェック
        if (event.target.classList.contains('delete-product-btn')) {
            const button = event.target;
            const productId = button.getAttribute('data-product-id');
            const productName = button.getAttribute('data-product-name');

            // 削除モーダルに情報を設定
            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
            const modalProductId = deleteConfirmModal.querySelector('#modalProductId');
            const modalProductName = deleteConfirmModal.querySelector('#modalProductName');
            const confirmDeleteButton = deleteConfirmModal.querySelector('#confirmDeleteButton');

            modalProductId.textContent = productId;
            modalProductName.textContent = productName;
            confirmDeleteButton.setAttribute('data-product-id', productId);

            // 削除モーダルを表示
            const bsDeleteModal = new bootstrap.Modal(deleteConfirmModal);
            bsDeleteModal.show();
        }
    });

    // モーダルの「保存」ボタンのクリックイベントを処理
    saveConfirmButton.addEventListener('click', function() { //
        const productId = editProductIdInput.value; //
        // 変更用の入力フォームから値を取得
        const newProductName = inputProductName.value.trim(); //
        const newStockQuantity = inputStockQuantity.value.trim(); //
        const newUnitPrice = inputUnitPrice.value.trim(); //
        const newProductCategory = inputProductCategory.value; // 商品区分ID

        let isValid = true; //

        // クライアントサイドでのバリデーションチェック
        if (newProductName === '') { //
            inputProductName.classList.add('is-invalid'); //
            isValid = false; //
        } else {
            inputProductName.classList.remove('is-invalid'); //
        }

        if (newStockQuantity === '' || isNaN(newStockQuantity) || parseInt(newStockQuantity) < 0) { //
            inputStockQuantity.classList.add('is-invalid'); //
            isValid = false; //
        } else {
            inputStockQuantity.classList.remove('is-invalid'); //
        }
        
        if (newUnitPrice === '' || isNaN(newUnitPrice) || parseFloat(newUnitPrice) < 0) { //
            inputUnitPrice.classList.add('is-invalid'); //
            isValid = false; //
        } else {
            inputUnitPrice.classList.remove('is-invalid'); //
        }

        if (newProductCategory === '') { //
            inputProductCategory.classList.add('is-invalid'); //
            isValid = false; //
        } else {
            inputProductCategory.classList.remove('is-invalid'); //
        }

        if (!isValid) { //
            return; //
        }

        const formData = new FormData(); //
        formData.append('product_id', productId); //
        formData.append('product_name', newProductName); //
        formData.append('stock_quantity', newStockQuantity); //
        formData.append('unit_price', newUnitPrice); //
        formData.append('product_category', newProductCategory); //

        fetch('../api/update_product_api.php', { //
            method: 'POST', //
            body: formData //
        })
        .then(response => response.json()) //
        .then(data => { //
            if (data.success) { //
                alert('商品情報が正常に更新されました。'); //
                addConfirmModal.hide(); //
                search(); // データを再読み込みして最新の状態にする
            } else {
                alert('商品情報の更新に失敗しました: ' + data.message); //
            }
        })
        .catch(error => { //
            console.error('Error:', error); //
            alert('商品情報の更新中にエラーが発生しました。'); //
        });
    });

    // モーダルが閉じられたときにフォームのバリデーション状態をリセット
    document.getElementById('addConfirmModal').addEventListener('hidden.bs.modal', function () { //
        inputProductName.classList.remove('is-invalid'); //
        inputStockQuantity.classList.remove('is-invalid'); //
        inputUnitPrice.classList.remove('is-invalid'); //
        inputProductCategory.classList.remove('is-invalid'); //
    });
});

// 検索入力フィールドのクリアボタン表示/非表示を切り替える関数
function toggleClearButton() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    clearButton.style.display = searchInput.value ? 'block' : 'none';
}

// 検索入力フィールドをクリアする関数
function clearSearch() {
    document.getElementById('searchInput').value = '';
    toggleClearButton();
    search(); // クリア後、再度検索を実行
}

// 商品検索処理および在庫情報を取得して表示する関数（loadInventoryと統合）
function search() {
    const keyword = document.getElementById("searchInput").value; // 検索キーワードを取得

    fetch("../api/inventory_api.php" + (keyword ? `?keyword=${encodeURIComponent(keyword)}` : "")) // search_api.php もしくは inventory_api.php を使用
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
                const row = `<tr><td colspan="6" class="text-center">該当する商品がありません</td></tr>`;
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
                                class="btn btn-outline-primary btn-sm edit-product-btn me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#addConfirmModal"
                                data-product-id="${productId}">
                                編集
                            </button>
                            <button 
                                class="btn btn-outline-danger btn-sm delete-product-btn"
                                data-product-id="${productId}"
                                data-product-name="${productName}">
                                削除
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


// --- 削除確認モーダル関連のJavaScriptロジック ---

// 削除確認モーダルに関連するイベントリスナーを設定する関数
function setupDeleteModalListeners() {
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    // モーダル内の「削除」ボタンがクリックされたときのイベントリスナー
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    confirmDeleteButton.addEventListener('click', function () {
        // 設定しておいた商品IDを取得
        const productIdToDelete = this.getAttribute('data-product-id');
        // 実際の削除処理を実行
        deleteProduct(productIdToDelete);
        // モーダルを閉じる
        const modal = bootstrap.Modal.getInstance(deleteConfirmModal);
        modal.hide();
    });
}

// 実際の商品削除処理を行う関数
// この関数は、PHPのAPIエンドポイントを呼び出して商品をデータベースから削除します。
function deleteProduct(productId) {
    console.log(`商品を削除リクエスト: 商品ID - ${productId}`);

    // PHPのAPIにPOSTリクエストを送信して商品を削除
    fetch("../api/delete_api.php", { // 例: delete_api.phpという削除用APIを想定
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "product_id=" + encodeURIComponent(productId) // 削除対象のIDを送信
    })
    .then(response => {
        if (!response.ok) {
            // HTTPエラーが発生した場合
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json(); // レスポンスをJSON形式に変換
    })
    .then(data => {
        if (data.success) {
            // 削除が成功した場合
            alert(`商品ID: ${productId} が正常に削除されました。`);
            search(); // 削除成功後、テーブルの表示を更新するために再検索を実行
        } else {
            // 削除がサーバー側で失敗した場合（例: データベースエラーなど）
            alert(`商品ID: ${productId} の削除に失敗しました: ${data.message || '不明なエラー'}`);
        }
    })
    .catch(error => {
        // ネットワークエラーやJSON解析エラーなど
        console.error('削除処理中にエラーが発生しました:', error);
        alert('商品の削除中にエラーが発生しました。ネットワーク接続を確認してください。');
    });
}