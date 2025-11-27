document.addEventListener('DOMContentLoaded', function() {
    const nameKeywordInput = document.getElementById('name_keyword');
    const idKeywordInput = document.getElementById('id_keyword');
    const divisionSelect = document.getElementById('division_id');
    const employeeTableBody = document.getElementById('employeeTableBody');

    let searchTimer;

    function performSearch() {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            const name = nameKeywordInput.value;
            const id = idKeywordInput.value;
            const division = divisionSelect.value;
            
            const isEditerPage = window.location.pathname.includes('editer.php');

            const params = new URLSearchParams();
            if (name) params.append('name_keyword', name);
            if (id) params.append('id_keyword', id);
            if (division) params.append('division_id', division);
            
            fetch(`fetch_employees.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateTable(data.data, isEditerPage);
                    } else {
                        console.error('API Error:', data.message);
                        employeeTableBody.innerHTML = '<tr><td colspan="6">検索結果の取得中にエラーが発生しました。</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    employeeTableBody.innerHTML = '<tr><td colspan="6">検索結果の読み込みに失敗しました。</td></tr>';
                });
        }, 300);
    }

    function updateTable(employees, isEditerPage) {
        employeeTableBody.innerHTML = '';

        const colspanCount = isEditerPage ? '4' : '6';

        if (employees.length === 0) {
            employeeTableBody.innerHTML = `<tr><td colspan="${colspanCount}">該当する従業員が見つかりません。</td></tr>`;
            return;
        }

        employees.forEach(employee => {
            const fromParam = isEditerPage ? 'editer' : 'main';
            let row;
            if (isEditerPage) {
                row = `
                    <tr>
                        <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                        <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}&from=${fromParam}">${escapeHtml(employee.NAME)}</a></td>
                        <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                        <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                    </tr>
                `;
            } else {
                row = `
                    <tr>
                        <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                        <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}&from=${fromParam}">${escapeHtml(employee.NAME)}</a></td>
                        <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                        <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                        <td>${escapeHtml(employee.JOINING_DATE)}</td>
                        <td>${escapeHtml(employee.EMERGENCY_CELL_NUMBER)}</td>
                    </tr>
                `;
            }
            employeeTableBody.insertAdjacentHTML('beforeend', row);
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
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    nameKeywordInput.addEventListener('input', performSearch);
    idKeywordInput.addEventListener('input', performSearch);
    divisionSelect.addEventListener('change', performSearch);
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        performSearch();
    });

    
    performSearch();

    const clearButtons = document.querySelectorAll('.clear-input-btn');
    nameKeywordInput.addEventListener('input', function() {
        const clearBtn = this.nextElementSibling;
        if (this.value) {
            clearBtn.style.display = 'inline';
        } else {
            clearBtn.style.display = 'none';
        }
    });
    idKeywordInput.addEventListener('input', function() {
        const clearBtn = this.nextElementSibling;
        if (this.value) {
            clearBtn.style.display = 'inline';
        } else {
            clearBtn.style.display = 'none';
        }
    });
    window.clearInputField = function(spanElement) {
        const inputField = spanElement.previousElementSibling;
        inputField.value = '';
        spanElement.style.display = 'none';
        performSearch();
    };
});