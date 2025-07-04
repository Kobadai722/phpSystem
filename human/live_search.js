document.addEventListener('DOMContentLoaded', function() {
    const nameKeywordInput = document.getElementById('name_keyword');
    const idKeywordInput = document.getElementById('id_keyword');
    const divisionSelect = document.getElementById('division_id');
    const employeeTableBody = document.getElementById('employeeTableBody');

    // 検索を実行する関数
    let searchTimer; // 遅延実行のためのタイマー

    function performSearch() {
        clearTimeout(searchTimer); // 既存のタイマーをクリア

        // 入力から少し遅れて検索を実行（タイピング中の頻繁なリクエストを防ぐ）
        searchTimer = setTimeout(() => {
            const name = nameKeywordInput.value;
            const id = idKeywordInput.value;
            const division = divisionSelect.value;

            // URLSearchParamsを使ってクエリパラメータを構築
            const params = new URLSearchParams();
            if (name) params.append('name_keyword', name);
            if (id) params.append('id_keyword', id);
            if (division) params.append('division_id', division);

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
                        updateTable(data.data);
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
    function updateTable(employees) {
        employeeTableBody.innerHTML = ''; // テーブルの中身をクリア

        if (employees.length === 0) {
            employeeTableBody.innerHTML = '<tr><td colspan="6">該当する従業員が見つかりません。</td></tr>';
            return;
        }

        employees.forEach(employee => {
            const row = `
                <tr>
                    <td scope="row">${escapeHtml(employee.EMPLOYEE_ID)}</td>
                    <td><a href="detail.php?id=${escapeHtml(employee.EMPLOYEE_ID)}">${escapeHtml(employee.NAME)}</a></td>
                    <td>${escapeHtml(employee.DIVISION_NAME)}</td>
                    <td>${escapeHtml(employee.JOB_POSITION_NAME)}</td>
                    <td>${escapeHtml(employee.JOINING_DATE)}</td>
                    <td>${escapeHtml(employee.EMERGENCY_CELL_NUMBER)}</td>
                </tr>
            `;
            employeeTableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // HTMLエスケープ処理（XSS対策）
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

    // イベントリスナーの追加
    nameKeywordInput.addEventListener('input', performSearch);
    idKeywordInput.addEventListener('input', performSearch);
    divisionSelect.addEventListener('change', performSearch);

    // ページロード時に初回検索を実行
    performSearch();

    // クリアボタンの表示/非表示を制御する既存の関数も調整
    // human.jsにあるかもしれませんが、ここにも置いておきます
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
});