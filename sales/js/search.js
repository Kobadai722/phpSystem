// 現在のページ番号を保持
let currentPage = 1;
const limit = 10;

// ページ読み込み時に初期表示
document.addEventListener('DOMContentLoaded', () => {
    search(1);
});

function toggleClearButton() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearButton');
    clearButton.style.display = searchInput.value ? 'block' : 'none';
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    toggleClearButton();
    search(1);
}

//  ページネーション対応の検索関数
function search(page = 1) {
    currentPage = page;

    const keyword = document.getElementById("searchInput").value;

    fetch("../api/search_api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `keyword=${encodeURIComponent(keyword)}&page=${page}&limit=${limit}`
    })
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector("tbody");
        tbody.innerHTML = "";

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">エラーが発生しました</td></tr>`;
            return;
        }

        if (data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center">該当する商品がありません</td></tr>`;
            return;
        }

        data.data.forEach(row => {
            const tr = `
                <tr>
                    <td>${row.PRODUCT_ID ?? ''}</td>
                    <td>${row.PRODUCT_NAME ?? ''}</td>
                    <td>${row.UNIT_SELLING_PRICE ?? ''}</td>
                    <td>${row.STOCK_QUANTITY ?? ''}</td>
                    <td>${row.PRODUCT_KUBUN_NAME ?? ''}</td>
                    <td></td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", tr);
        });

        createPagination(data.page, Math.ceil(data.total / data.limit));
    })
    .catch(err => {
        console.error("Error:", err);
    });
}

//  ページネーション生成
function createPagination(current, totalPages) {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.className = `btn btn-sm ${i === current ? "btn-primary" : "btn-outline-primary"} me-1`;
        btn.textContent = i;
        btn.onclick = () => search(i);
        pagination.appendChild(btn);
    }
}
