document.addEventListener('DOMContentLoaded', () => {
    // 金額をカンマ区切りにする関数
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // カンマを除去する関数
    function removeComma(num) {
        return num.toString().replace(/,/g, "");
    }

    const salaryModal = document.getElementById('salaryModal');
    const amountInput = document.getElementById('modalAmount');
    const salaryForm = document.getElementById('salaryForm');

    // モーダルが開くときの処理
    if (salaryModal) {
        salaryModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            
            // データ属性から値を取得
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            let amount = button.getAttribute('data-amount');
            const type = button.getAttribute('data-type');

            // モーダル内の入力欄にセット
            document.getElementById('modalEmpId').value = id;
            document.getElementById('modalEmpName').value = name;
            document.getElementById('modalType').value = type;

            // 金額にカンマをつけて表示
            if (amount) {
                amountInput.value = formatNumber(amount);
            } else {
                amountInput.value = '';
            }
        });
    }

    // 入力時に自動でカンマをつける処理
    if (amountInput) {
        amountInput.addEventListener('input', function(e) {
            // 数字以外を除去
            let value = this.value.replace(/[^0-9]/g, '');
            // カンマをつけて再設定
            if (value) {
                this.value = formatNumber(value);
            } else {
                this.value = '';
            }
        });
    }

    // フォーム送信時にカンマを取り除く処理
    if (salaryForm) {
        salaryForm.addEventListener('submit', function() {
            amountInput.value = removeComma(amountInput.value);
        });
    }
});