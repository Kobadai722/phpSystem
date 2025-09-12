document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const customerTableBody = document.getElementById('customerTableBody');

    let searchTimer;

    function performSearch() {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);

            fetch(`customer-get.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateTable(data.data);
                    } else {
                        console.error('API Error:', data.message);
                        customerTableBody.innerHTML = '<tr><td colspan="7">検索結果の取得中にエラーが発生しました。</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    customerTableBody.innerHTML = '<tr><td colspan="7">検索結果の読み込みに失敗しました。</td></tr>';
                });
        }, 300);
    }

    function updateTable(customers) {
        customerTableBody.innerHTML = '';

        if (customers.length === 0) {
            customerTableBody.innerHTML = `<tr><td colspan="7">該当する顧客が見つかりません。</td></tr>`;
            return;
        }

        customers.forEach(customer => {
            let row = `
                <tr>
                    <td scope="row">${escapeHtml(customer.CUSTOMER_ID)}</td>
                    <td>${escapeHtml(customer.NAME)}</td>
                    <td>${escapeHtml(customer.CELL_NUMBER)}</td>
                    <td>${escapeHtml(customer.MAIL)}</td>
                    <td>${escapeHtml(customer.POST_CODE)}</td>
                    <td>${escapeHtml(customer.ADDRESS)}</td>
                    <td>
                        <a href="customer-edit.php?id=${escapeHtml(customer.CUSTOMER_ID)}" class="btn btn-primary btn-sm">編集</a>
                        <a href="sales-memo.php?customer_id=${escapeHtml(customer.CUSTOMER_ID)}" class="btn btn-info btn-sm">メモ</a>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="${escapeHtml(customer.CUSTOMER_ID)}" data-name="${escapeHtml(customer.NAME)}">
                            削除
                        </button>
                    </td>
                </tr>
            `;
            customerTableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') {
            return '';
        }
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        performSearch();
    });

    const inputs = searchForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', performSearch);
    });


    performSearch();
});