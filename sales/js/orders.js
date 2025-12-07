// orders.js

document.addEventListener('DOMContentLoaded', () => {
    const ordersTableBody = document.getElementById('ordersTableBody');
    const searchForm = document.getElementById('searchForm');

    // ▼ ページネーション用DOM
    let paginationArea = null;

    // 現在の検索条件
    let currentSearchParams = {};

    // 現在のページ
    let currentPage = 1;
    const limit = 10; // 1ページあたり件数

    // 注文データを取得
    const fetchOrders = async (params = {}) => {
        currentSearchParams = params;

        ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>';

        try {
            const finalParams = {
                ...params,
                page: currentPage,
                limit: limit
            };

            const queryParams = new URLSearchParams(finalParams).toString();
            const response = await fetch(`../api/get_orders_api.php?${queryParams}`);
            const data = await response.json();

            ordersTableBody.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(order => {
                    const row = document.createElement('tr');

                    const orderDate = new Date(order.ORDER_DATETIME);
                    const formattedDate = orderDate.toLocaleDateString('ja-JP');
                    const formattedAmount = '¥' + Number(order.TOTAL_AMOUNT).toLocaleString();

                    row.innerHTML = `
                        <td>${escapeHTML(order.ORDER_ID)}</td>
                        <td>${escapeHTML(formattedDate)}</td>
                        <td>${escapeHTML(order.CUSTOMER_NAME)}</td>
                        <td>${escapeHTML(formattedAmount)}</td>
                        <td>${escapeHTML(order.STATUS)}</td>
                        <td>${escapeHTML(order.STATUS)}</td>
                        <td class="actions">
                            <a href="order_detail.php?id=${escapeHTML(order.ORDER_ID)}" class="btn btn-primary btn-sm me-1">詳細</a>
                        </td>
                    `;
                    ordersTableBody.appendChild(row);
                });

                // ▼ ページネーション生成
                renderPagination(data.totalPages);

            } else {
                ordersTableBody.innerHTML =
                    '<tr><td colspan="7" class="text-center">表示する注文がありません。</td></tr>';
            }

        } catch (error) {
            console.error('Error fetching orders:', error);
            ordersTableBody.innerHTML =
                `<tr><td colspan="7" class="text-center text-danger">データを取得できませんでした: ${escapeHTML(error.message)}</td></tr>`;
        }
    };

    // ページネーション描画
    const renderPagination = (totalPages) => {
        // すでにあれば削除
        if (paginationArea) paginationArea.remove();

        paginationArea = document.createElement('div');
        paginationArea.className = "d-flex justify-content-center mt-3";

        let html = `
            <ul class="pagination">
                <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <button class="page-link" data-page="${currentPage - 1}">前へ</button>
                </li>
        `;

        for (let p = 1; p <= totalPages; p++) {
            html += `
                <li class="page-item ${p === currentPage ? 'active' : ''}">
                    <button class="page-link" data-page="${p}">${p}</button>
                </li>
            `;
        }

        html += `
                <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                    <button class="page-link" data-page="${currentPage + 1}">次へ</button>
                </li>
            </ul>
        `;

        paginationArea.innerHTML = html;

        // ▼ tableの下に追加
        ordersTableBody.parentElement.appendChild(paginationArea);

        // ▼ イベント設定
        paginationArea.querySelectorAll("button.page-link").forEach(btn => {
            btn.addEventListener("click", () => {
                const page = Number(btn.dataset.page);
                if (!isNaN(page) && page >= 1 && page <= totalPages) {
                    currentPage = page;
                    fetchOrders(currentSearchParams);
                }
            });
        });
    };

    // HTMLエスケープ関数
    const escapeHTML = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    // 検索フォーム送信
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        currentPage = 1; // 検索時は1ページ目に戻す

        const params = {
            orderId: document.getElementById('orderId').value,
            customerName: document.getElementById('customerName').value,
            paymentStatus: document.getElementById('paymentStatus').value,
        };

        fetchOrders(params);
    });

    // リセットボタン
    document.getElementById('resetButton').addEventListener('click', () => {
        searchForm.reset();
        currentPage = 1;
        fetchOrders({});
    });

    // 初期表示
    fetchOrders({});
});
