document.addEventListener('DOMContentLoaded', () => {
    fetchInventoryList(); // 在庫一覧の表示
    setupAddForm();       // 追加用のイベント設定
    setupUpdateHandlers();// 更新イベント（在庫数変更）
});

// 在庫一覧を取得して表示する
function fetchInventoryList() {
    fetch('inventory_api.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#inventoryTable tbody');
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = `
                    <tr>
                        <td>${item.PRODUCT_ID}</td>
                        <td>${item.PRODUCT_NAME}</td>
                        <td>${item.STOCK_QUANTITY}</td>
                        <td>
                            <button class="btn btn-sm btn-warning editBtn" data-id="${item.PRODUCT_ID}" data-stock="${item.STOCK_QUANTITY}">変更</button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
            setupUpdateHandlers(); // 動的に追加されたボタンにもイベント設定
        });
}

// 商品追加の送信処理
function setupAddForm() {
    document.getElementById('addProductForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('add_inventory_api.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('商品を追加しました');
                fetchInventoryList();
                this.reset();
            } else {
                alert('追加に失敗しました: ' + data.message);
            }
        });
    });
}

// 在庫変更ボタンにイベントを設定
function setupUpdateHandlers() {
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.dataset.id;
            const currentStock = this.dataset.stock;
            const newStock = prompt(`新しい在庫数を入力してください（現在: ${currentStock}）`, currentStock);
            if (newStock === null) return;

            fetch('update_inventory_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${encodeURIComponent(productId)}&stock_quantity=${encodeURIComponent(newStock)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('在庫数を更新しました');
                    fetchInventoryList();
                } else {
                    alert('在庫変更に失敗しました: ' + data.message);
                }
            });
        });
    });
}
