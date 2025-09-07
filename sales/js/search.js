// search.js

// ページのロードが完了したときに実行される初期化処理
document.addEventListener('DOMContentLoaded', () => {
    search(); // ページ読み込み時に商品リストを検索・表示
    // 削除確認モーダル関連のイベントリスナー設定を削除
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
            const row = `<tr><td colspan="5" class="text-center">該当する商品がありません</td></tr>`; // colspanを5に変更
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
                    <td></td>
                    </tr>
            `;
            // テーブルの末尾に追加
            tbody.insertAdjacentHTML("beforeend", tr);
        });
    })
    .catch(error => {
        console.error('検索処理中にエラーが発生しました:', error);
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">データの取得中にエラーが発生しました。</td></tr>`; // colspanを5に変更
    });
}

