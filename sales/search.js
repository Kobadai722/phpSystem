// 商品検索処理を行う関数
function search() {
    // 入力フォームから検索キーワードを取得
    const keyword = document.getElementById("searchInput").value;

    // PHPのAPIにPOSTリクエストを送信
    fetch("search_api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded" // フォーム形式で送信
        },
        body: "keyword=" + encodeURIComponent(keyword) // キーワードをURLエンコードして送信
    })
    .then(response => response.json()) // レスポンスをJSON形式に変換
    .then(data => {
        // テーブルの tbody 要素を取得
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = ""; // テーブルの内容を一度クリア

        // 検索結果が0件の場合、メッセージを表示
        if (data.length === 0) {
            const row = `<tr><td colspan="5">該当する商品がありません</td></tr>`;
            tbody.insertAdjacentHTML("beforeend", row);
            return;
        }

        // 検索結果がある場合、それぞれのデータをテーブルに表示
        data.forEach(row => {
            // 各値が null の場合は空文字に置き換えて表示
            const tr = `
                <tr>
                    <td>${row.PRODUCT_ID ?? ''}</td>
                    <td>${row.PRODUCT_NAME ?? ''}</td>
                    <td>${row.UNIT_SELLING_PRICE ?? ''}</td>
                    <td>${row.STOCK_QUANTITY ?? ''}</td>
                    <td>${row.PRODUCT_KUBUN_NAME ?? ''}</td> 
                </tr>
            `;
            // テーブルの末尾に追加
            tbody.insertAdjacentHTML("beforeend", tr);
        });
    });
}
