document.addEventListener('DOMContentLoaded', function() {
    const nameKeywordInput = document.getElementById('name_keyword');
    const idKeywordInput = document.getElementById('id_keyword');
    const divisionSelect = document.getElementById('division_id');
    const employeeTableBody = document.getElementById('employeeTableBody');
    // const includeDeletedCheckbox = document.getElementById('include_deleted'); // 削除: 「削除済みを含める」チェックボックス

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
            
            // 削除: 「削除済みを含める」パラメータの追加ロジック
            // if (isEditerPage && includeDeletedCheckbox) {
            //     params.append('include_deleted', includeDeletedCheckbox.checked ? 'true' : 'false');
            // } else {
            //     params.append('include_deleted', 'false');
            // }


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

        // editer.phpの「操作」列がなくなったため、列数を5に調整（以前は6）
        const colspanCount = isEditerPage ? '5' : '6';

        if (employees.length === 0) {
            employeeTableBody.innerHTML = `<tr><td colspan="${colspanCount}">該当する従業員が見つかりません。</td></tr>`;
            return;
        }

        employees.forEach(employee => {
            let row;
            if (isEditerPage) {
                // editer.php 用の行フォーマット
                // 削除ボタンのHTMLを削除
                row = `
                    <tr>
                        <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                        <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}">${escapeHtml(employee.NAME)}` + 
                        // 削除: (employee.IS_DELETED ? ' <span class="badge bg-danger">削除済み</span>' : '') + // 削除済みバッジ
                        `</a></td>
                        <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                        <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                        <td>
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

        // 削除ボタンが存在しなくなるため、setupModalButtons()の呼び出しも不要
        // if (isEditerPage) {
        //     setupModalButtons(); 
        // }
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

    // setupModalButtons() 関数を削除またはコメントアウト
    /*
    function setupModalButtons() {
        const deleteButtons = document.querySelectorAll('.delete-employee-btn');
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');

        if (deleteButtons.length > 0 && deleteConfirmModal) {
            const modalEmployeeNameSpan = deleteConfirmModal.querySelector('#modalEmployeeName');
            const modalEmployeeIdInput = deleteConfirmModal.querySelector('#modalEmployeeId');

            deleteButtons.forEach(button => {
                
                button.removeEventListener('click', handleEditDeleteClick);
                button.addEventListener('click', handleEditDeleteClick);
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

        // 削除: 復元ボタン関連のロジック
        // const restoreButtons = document.querySelectorAll('.restore-employee-btn');
        // const restoreConfirmModal = document.getElementById('restoreConfirmModal');

        // if (restoreButtons.length > 0 && restoreConfirmModal) {
        //     const modalRestoreEmployeeNameSpan = restoreConfirmModal.querySelector('#modalRestoreEmployeeName');
        //     const modalRestoreEmployeeIdInput = restoreConfirmModal.querySelector('#modalRestoreEmployeeId');

        //     restoreButtons.forEach(button => {
                
        //         button.removeEventListener('click', handleEditRestoreClick);
        //         button.addEventListener('click', handleEditRestoreClick);
        //     });
            
        //     function handleEditRestoreClick() {        const employeeId = this.dataset.employeeId;
        //         const employeeName = this.dataset.employeeName;

        //         if (modalRestoreEmployeeNameSpan) {
        //             modalRestoreEmployeeNameSpan.textContent = employeeName;
        //         }
        //         if (modalRestoreEmployeeIdInput) {
        //             modalRestoreEmployeeIdInput.value = employeeId;
        //         }
        //     }
        // }
    }
    */


    // イベントリスナーの追加
    nameKeywordInput.addEventListener('input', performSearch);
    idKeywordInput.addEventListener('input', performSearch);
    divisionSelect.addEventListener('change', performSearch);
    // 削除: if (includeDeletedCheckbox) { includeDeletedCheckbox.addEventListener('change', performSearch); }
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

    // 初期ロード時にもボタンのイベントリスナーを設定 (削除ボタンが存在しないため不要)
    // setupModalButtons();
});