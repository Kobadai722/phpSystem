// orders.js

document.addEventListener('DOMContentLoaded', () => {
    const ordersTableBody = document.getElementById('ordersTableBody');
    const searchForm = document.getElementById('searchForm');

    // 注文データをAPIから取得し、テーブルに表示する関数
    const fetchOrders = async (params = {}) => {
        ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>';
        try {
            const queryParams = new URLSearchParams(params).toString();
            const response = await fetch(`../api/get_orders_api.php?${queryParams}`);
            const data = await response.json();

            ordersTableBody.innerHTML = '';

            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(order => {
                    const row = document.createElement('tr');
                    // 修正: データベースのカラム名に合わせてキー名を変更
                    const orderDate = new Date(order.ORDER_DATETIME);
                    const formattedDate = orderDate.toLocaleDateString('ja-JP', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '/');
                    const formattedAmount = '¥' + Number(order.TOTAL_AMOUNT).toLocaleString();

                    row.innerHTML = `
                        <td>${escapeHTML(order.ORDER_ID)}</td>
                        <td>${escapeHTML(formattedDate)}</td>
                        <td>${escapeHTML(order.CUSTOMER_NAME)}</td>
                        <td>${escapeHTML(formattedAmount)}</td>
                        <td>${escapeHTML(order.STATUS)}</td>
                        <td>${escapeHTML(order.STATUS)}</td>
                        <td class="actions">
                            <a href="order_detail_view.php?id=${escapeHTML(order.ORDER_ID)}" class="btn btn-info btn-sm me-1">詳細</a>
                            <a href="order_detail_edit.php?id=${escapeHTML(order.ORDER_ID)}&mode=edit" class="btn btn-warning btn-sm">編集</a>
                        </td>
                    `;
                    ordersTableBody.appendChild(row);
                });
            } else if (data.success && data.data && data.data.length === 0) {
                ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">表示する注文がありません。</td></tr>';
            } else {
                ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データの取得中にエラーが発生しました: ${escapeHTML(data.error_message || '不明なエラー')}</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching orders:', error);
            ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データを取得できませんでした: ${escapeHTML(error.message)}</td></tr>`;
        }
    };

    // HTMLエスケープ関数
    const escapeHTML = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    // 検索フォームの送信イベントリスナー
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const params = {
            // HTMLのinput idと一致させる
            orderId: document.getElementById('orderId').value,
            customerName: document.getElementById('customerName').value,
            paymentStatus: document.getElementById('paymentStatus').value,
            deliveryStatus: document.getElementById('deliveryStatus').value
        };
        fetchOrders(params);
    });

    // リセットボタンのクリックイベントリスナー
    document.getElementById('resetButton').addEventListener('click', () => {
        searchForm.reset();
        fetchOrders();
    });

    // ページ読み込み時に初期データを取得
    fetchOrders();
});