document.addEventListener('DOMContentLoaded', function() {
    const nameKeywordInput = document.getElementById('name_keyword');
    const idKeywordInput = document.getElementById('id_keyword');
    const divisionSelect = document.getElementById('division_id');
    const employeeTableBody = document.getElementById('employeeTableBody');
    const includeDeletedCheckbox = document.getElementById('include_deleted'); // 新しく追加

    // 検索を実行する関数
    let searchTimer; // 遅延実行のためのタイマー

    function performSearch() {
        clearTimeout(searchTimer); // 既存のタイマーをクリア

        // 入力から少し遅れて検索を実行（タイピング中の頻繁なリクエストを防ぐ）
        searchTimer = setTimeout(() => {
            const name = nameKeywordInput.value;
            const id = idKeywordInput.value;
            const division = divisionSelect.value;
            
            // 現在のページがediter.phpかどうかを判定
            const isEditerPage = window.location.pathname.includes('editer.php');

            // URLSearchParamsを使ってクエリパラメータを構築
            const params = new URLSearchParams();
            if (name) params.append('name_keyword', name);
            if (id) params.append('id_keyword', id);
            if (division) params.append('division_id', division);
            
            // editer.phpの場合のみ 'include_deleted' パラメータを使用
            if (isEditerPage && includeDeletedCheckbox) {
                params.append('include_deleted', includeDeletedCheckbox.checked ? 'true' : 'false');
            } else {
                // main.php または include_deleted チェックボックスがない場合は常にfalse
                params.append('include_deleted', 'false');
            }


            // Ajaxリクエストを送信
            fetch(`fetch_employees.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateTable(data.data, isEditerPage); // ページタイプを渡す
                    } else {
                        console.error('API Error:', data.message);
                        employeeTableBody.innerHTML = '<tr><td colspan="6">検索結果の取得中にエラーが発生しました。</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    employeeTableBody.innerHTML = '<tr><td colspan="6">検索結果の読み込みに失敗しました。</td></tr>';
                });
        }, 300); // 300ミリ秒の遅延
    }

    // テーブルを更新する関数
    function updateTable(employees, isEditerPage) { // isEditerPage パラメータを追加
        employeeTableBody.innerHTML = ''; // テーブルの中身をクリア

        if (employees.length === 0) {
            employeeTableBody.innerHTML = '<tr><td colspan="' + (isEditerPage ? '5' : '6') + '">該当する従業員が見つかりません。</td></tr>'; // 列数を調整
            return;
        }

        employees.forEach(employee => {
            let row;
            if (isEditerPage) {
                // editer.php 用の行フォーマット
                row = `
                    <tr>
                        <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                        <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}">${escapeHtml(employee.NAME)}` + 
                        (employee.IS_DELETED ? ' <span class="badge bg-danger">削除済み</span>' : '') + 
                        `</a></td>
                        <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                        <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                        <td>
                            ${employee.IS_DELETED ? 
                                `
                                <button type="button" class="btn btn-sm btn-info restore-employee-btn"
                                        data-bs-toggle="modal" data-bs-target="#restoreConfirmModal"
                                        data-employee-id="${escapeHtml(employee.EMPLOYEE_ID)}"
                                        data-employee-name="${escapeHtml(employee.NAME)}">
                                    復元
                                </button>
                                ` : 
                                `
                                <button type="button" class="btn btn-sm btn-danger delete-employee-btn"
                                        data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
                                        data-employee-id="${escapeHtml(employee.EMPLOYEE_ID)}"
                                        data-employee-name="${escapeHtml(employee.NAME)}">
                                    削除
                                </button>
                                `
                            }
                        </td>
                    </tr>
                `;
            } else {
                // main.php 用の行フォーマット
                row = `
                    <tr>
                        <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                        <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}">${escapeHtml(employee.NAME)}</a></td>
                        <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                        <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                        <td>${escapeHtml(employee.JOINING_DATE)}</td>
                        <td>${escapeHtml(employee.EMERGENCY_CELL_NUMBER)}</td>
                    </tr>
                `;
            }
            employeeTableBody.insertAdjacentHTML('beforeend', row);
        });

        
        if (isEditerPage) {
            setupModalButtons();
        }
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


    function setupModalButtons() {
        const deleteButtons = document.querySelectorAll('.delete-employee-btn');
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');

        if (deleteButtons.length > 0 && deleteConfirmModal) {
            const modalEmployeeNameSpan = deleteConfirmModal.querySelector('#modalEmployeeName');
            const modalEmployeeIdInput = deleteConfirmModal.querySelector('#modalEmployeeId');

            deleteButtons.forEach(button => {
                
                button.removeEventListener('click', handleEditDeleteClick); // 新しい関数名
                button.addEventListener('click', handleEditDeleteClick); // 新しい関数名
            });

            function handleEditDeleteClick() { 
                const employeeId = this.dataset.employeeId;
                const employeeName = this.dataset.employeeName;

                if (modalEmployeeNameSpan) {
                    modalEmployeeNameSpan.textContent = employeeName;
                }
                if (modalEmployeeIdInput) {
                    modalEmployeeIdInput.value = employeeId;
                }
            }
        }

        const restoreButtons = document.querySelectorAll('.restore-employee-btn');
        const restoreConfirmModal = document.getElementById('restoreConfirmModal');

        if (restoreButtons.length > 0 && restoreConfirmModal) {
            const modalRestoreEmployeeNameSpan = restoreConfirmModal.querySelector('#modalRestoreEmployeeName');
            const modalRestoreEmployeeIdInput = restoreConfirmModal.querySelector('#modalRestoreEmployeeId');

            restoreButtons.forEach(button => {
                
                button.removeEventListener('click', handleEditRestoreClick); // 新しい関数名
                button.addEventListener('click', handleEditRestoreClick); // 新しい関数名
            });
            
            function handleEditRestoreClick() {           const employeeId = this.dataset.employeeId;
                const employeeName = this.dataset.employeeName;

                if (modalRestoreEmployeeNameSpan) {
                    modalRestoreEmployeeNameSpan.textContent = employeeName;
                }
                if (modalRestoreEmployeeIdInput) {
                    modalRestoreEmployeeIdInput.value = employeeId;
                }
            }
        }
    }


    // イベントリスナーの追加
    nameKeywordInput.addEventListener('input', performSearch);
    idKeywordInput.addEventListener('input', performSearch);
    divisionSelect.addEventListener('change', performSearch);
    if (includeDeletedCheckbox) { 
        includeDeletedCheckbox.addEventListener('change', performSearch);
    }
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
        performSearch(); // クリア後も検索を実行
    };

    // 初期ロード時にもボタンのイベントリスナーを設定
    setupModalButtons();
});