
function search() {
    const keyword = document.getElementById("searchInput").value;

    fetch("search_api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "keyword=" + encodeURIComponent(keyword)
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = "";

        if (data.length === 0) {
            const row = `<tr><td colspan="5">該当する商品がありません</td></tr>`;
            tbody.insertAdjacentHTML("beforeend", row);
            return;
        }

        data.forEach(row => {
            const tr = `
                <tr>
                    <td>${row.PRODUCT_ID  ?? ''}</td>
                    <td>${row.PRODUCT_NAME  ?? ''}</td>
                    <td>${row.UNIT_SELLING_PRICE  ?? ''}</td>
                    <td>${row.STOCK_QUANTITY  ?? ''}</td>
                    <td>${row.PRODUCT_KUBUN_NAME  ?? ''}</td>  // ?? '' は、null または undefined のときだけ ''（空文字）を表示する構文です。
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", tr);
        });
    });
}
