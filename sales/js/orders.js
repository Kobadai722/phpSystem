// orders.js（商品一覧と同じページネーション構造に統一）

let currentPage = 1;
const limit = 10;

// 初期読み込み
document.addEventListener("DOMContentLoaded", () => {
    search(1);
});

// 検索 & ページネーション本体
function search(page = 1) {
    currentPage = page;

    const params = {
        orderId: document.getElementById("orderId").value,
        customerName: document.getElementById("customerName").value,
        paymentStatus: document.getElementById("paymentStatus").value,
        page: page,
        limit: limit
    };

    const queryString = new URLSearchParams(params).toString();

    fetch(`../api/get_orders_api.php?${queryString}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById("ordersTableBody");
            tbody.innerHTML = "";

            if (!data.success) {
                tbody.innerHTML =
                    `<tr><td colspan="7" class="text-center text-danger">データ取得エラー</td></tr>`;
                return;
            }

            if (data.data.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="7" class="text-center">該当する注文がありません</td></tr>`;
                return;
            }

            data.data.forEach(order => {
                const date = new Date(order.ORDER_DATETIME)
                    .toLocaleDateString("ja-JP");
                const amount = "¥" + Number(order.TOTAL_AMOUNT).toLocaleString();

                const tr = `
                    <tr>
                        <td>${order.ORDER_ID}</td>
                        <td>${date}</td>
                        <td>${order.CUSTOMER_NAME}</td>
                        <td>${amount}</td>
                        <td>${order.STATUS}</td>
                        <td>${order.STATUS}</td>
                        <td>
                            <a href="order_detail.php?id=${order.ORDER_ID}" 
                                class="btn btn-primary btn-sm">詳細</a>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML("beforeend", tr);
            });

            createPagination(data.page, data.totalPages);
        })
        .catch(err => {
            console.error("Error:", err);
        });
}

// ページネーション生成
function createPagination(current, totalPages) {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.className =
            `btn btn-sm ${i === current ? "btn-primary" : "btn-outline-primary"} me-1`;
        btn.textContent = i;
        btn.onclick = () => search(i);
        pagination.appendChild(btn);
    }
}

// 検索フォームの制御
document.getElementById("searchForm").addEventListener("submit", e => {
    e.preventDefault();
    search(1);
});

document.getElementById("resetButton").addEventListener("click", () => {
    document.getElementById("searchForm").reset();
    search(1);
});
