// inventory.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    loadInventory(); // ページ読み込み時に在庫リストを取得・表示
});

// 在庫情報を取得して表示する関数
function loadInventory() {
    // PHPのAPIにGETリクエストを送信（POSTでも可）
    fetch("inventory_api.php")
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
                const row = `<tr><td colspan="5" class="text-center">在庫データが存在しません</td></tr>`;
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
                                class="btn btn-outline-Info btn-sm"
                                data-bs-toggle="modal" 
                                data-bs-target="#addConfirmModal"
                                data-product-id="${productId}"
                                data-product-name="${productName}">
                                追加
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
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">在庫データの取得中にエラーが発生しました。</td></tr>`;
        });
}