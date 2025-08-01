// orders.js

document.addEventListener('DOMContentLoaded', function() {
    const ordersTableBody = document.getElementById('ordersTableBody');
    const searchForm = document.getElementById('searchForm');
    const resetButton = document.getElementById('resetButton');

    // 注文データをAPIから取得し、テーブルに表示する関数
    async function fetchOrders(params = {}) {
        ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>';
        try {
            const queryParams = new URLSearchParams(params).toString();
            // ファイルツリーに合わせてパスを修正
            const response = await fetch(`../api/get_orders_api.php?${queryParams}`);
            const data = await response.json();

            ordersTableBody.innerHTML = ''; // 既存の行をクリア

            if (data.success && data.orders.length > 0) {
                data.orders.forEach(order => {
                    const row = document.createElement('tr');
                    const orderDatetime = new Date(order.order_datetime);
                    const formattedDatetime = orderDatetime.toLocaleString('ja-JP', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).replace(/\//g, '/');
                    const formattedAmount = '¥' + Number(order.total_amount).toLocaleString();

                    // APIの返却データに合わせて表示ロジックを修正
                    row.innerHTML = `
                        <td>${escapeHTML(order.order_id)}</td>
                        <td>${escapeHTML(formattedDatetime)}</td>
                        <td>${escapeHTML(order.customer_name)}</td>
                        <td>${escapeHTML(formattedAmount)}</td>
                        <td>${escapeHTML(order.payment_status)}</td>
                        <td>${escapeHTML(order.delivery_status)}</td>
                        <td class="actions">
                            <a href="order_detail_view.php?id=${escapeHTML(order.order_id)}" class="btn btn-info btn-sm me-1">詳細</a>
                            <a href="order_detail_edit.php?id=${escapeHTML(order.order_id)}&mode=edit" class="btn btn-warning btn-sm">編集</a>
                        </td>
                    `;
                    ordersTableBody.appendChild(row);
                });
            } else if (data.success && data.orders.length === 0) {
                ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">表示する注文がありません。</td></tr>';
            } else {
                ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データの取得中にエラーが発生しました: ${escapeHTML(data.message || '不明なエラー')}</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching orders:', error);
            ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データを取得できませんでした: ${escapeHTML(error.message)}</td></tr>`;
        }
    }

    // HTMLエスケープ関数
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // 検索フォームの送信イベントリスナー
    searchForm.addEventListener('submit', function(event) {
        event.preventDefault(); // フォームのデフォルト送信を防止
        const orderId = document.getElementById('orderId').value;
        const customerName = document.getElementById('customerName').value;
        const paymentStatus = document.getElementById('paymentStatus').value;
        const deliveryStatus = document.getElementById('deliveryStatus').value;

        const params = {
            orderId: orderId,
            customerName: customerName,
            paymentStatus: paymentStatus,
            deliveryStatus: deliveryStatus
        };
        fetchOrders(params);
    });

    // リセットボタンのクリックイベントリスナー
    resetButton.addEventListener('click', function() {
        // フォームをリセット
        searchForm.reset();
        // フィルタリングなしで再度データを取得
        fetchOrders({});
    });

    // ページ読み込み時に初期データを取得
    fetchOrders();
});