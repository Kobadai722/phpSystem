// search.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    search(); // ページ読み込み時に商品リストを検索・表示
    // 削除確認モーダル関連のイベントリスナーを設定
    setupDeleteModalListeners();
});

// 検索入力フィールドのクリアボタン表示/非表示を切り替える関数
function toggleClearButton() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    // 入力値があればクリアボタンを表示、なければ非表示
    clearButton.style.display = searchInput.value ? 'block' : 'none';
}

// 検索入力フィールドをクリアする関数
function clearSearch() {
    document.getElementById('searchInput').value = ''; // 入力値を空にする
    toggleClearButton(); // クリアボタンの状態を更新
    search(); // クリア後、再度検索を実行して全商品を表示
}

// 商品検索処理を行う関数
function search() {
    // 入力フォームから検索キーワードを取得
    const keyword = document.getElementById("searchInput").value;

    // PHPのAPIにPOSTリクエストを送信
    fetch("../api/search_api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded" // フォーム形式で送信
        },
        body: "keyword=" + encodeURIComponent(keyword) // キーワードをURLエンコードして送信
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json(); // レスポンスをJSON形式に変換
    })
    .then(data => {
        // テーブルの tbody 要素を取得
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = ""; // テーブルの内容を一度クリア

        // 検索結果が0件の場合、メッセージを表示
        if (data.length === 0) {
            const row = `<tr><td colspan="6" class="text-center">該当する商品がありません</td></tr>`; // colspanを6に変更
            tbody.insertAdjacentHTML("beforeend", row);
            return;
        }

        // 検索結果がある場合、それぞれのデータをテーブルに表示
        data.forEach(row => {
            // 各値が null の場合は空文字に置き換えて表示
            const productId = row.PRODUCT_ID ?? '';
            const productName = row.PRODUCT_NAME ?? '';
            const unitSellingPrice = row.UNIT_SELLING_PRICE ?? '';
            const stockQuantity = row.STOCK_QUANTITY ?? '';
            const productKubunName = row.PRODUCT_KUBUN_NAME ?? '';

            const tr = `
                <tr>
                    <td>${productId}</td>
                    <td>${productName}</td>
                    <td>${unitSellingPrice}</td>
                    <td>${stockQuantity}</td>
                    <td>${productKubunName}</td>
                    <td>
                        <button 
                            class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteConfirmModal"
                            data-product-id="${productId}"
                            data-product-name="${productName}">
                            削除
                        </button>
                    </td>
                </tr>
            `;
            // テーブルの末尾に追加
            tbody.insertAdjacentHTML("beforeend", tr);
        });
    })
    .catch(error => {
        console.error('検索処理中にエラーが発生しました:', error);
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">データの取得中にエラーが発生しました。</td></tr>`;
    });
}

// --- 削除確認モーダル関連のJavaScriptロジック ---

// 削除確認モーダルに関連するイベントリスナーを設定する関数
function setupDeleteModalListeners() {
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    // モーダルが表示される直前に実行されるイベントリスナー
    deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
        // モーダルをトリガーした（クリックされた）ボタン要素を取得
        const button = event.relatedTarget;
        // ボタンのカスタムデータ属性から商品IDと商品名を取得
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');

        // モーダル内の表示要素を取得
        const modalProductId = deleteConfirmModal.querySelector('#modalProductId');
        const modalProductName = deleteConfirmModal.querySelector('#modalProductName');
        const confirmDeleteButton = deleteConfirmModal.querySelector('#confirmDeleteButton');

        // モーダルに商品情報を設定
        modalProductId.textContent = productId;
        modalProductName.textContent = productName;
        // 削除実行ボタンに削除対象の商品IDをデータ属性として設定（削除処理で利用するため）
        confirmDeleteButton.setAttribute('data-product-id', productId);
    });

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