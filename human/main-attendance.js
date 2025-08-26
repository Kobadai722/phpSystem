document.addEventListener('DOMContentLoaded', function() {
    const clockInBtn = document.getElementById('mainClockInBtn');
    const clockOutBtn = document.getElementById('mainClockOutBtn');
    const statusMessage = document.getElementById('statusMessage');

    // メッセージを表示する関数
    function showStatusMessage(message, type) {
        statusMessage.textContent = message;
        statusMessage.className = `mt-3 alert alert-${type} text-center fw-bold fs-5`;
        statusMessage.style.display = 'block';

        // 3秒後にメッセージを非表示にする
        setTimeout(() => {
            statusMessage.style.display = 'none';
        }, 3000);
    }

    function updateUI(record) {
        if (record) {
            clockInBtn.style.display = 'none';
            if (record.CLOCK_OUT_TIME) {
                clockOutBtn.style.display = 'none';
                showStatusMessage(`出勤済み：${record.CLOCK_IN_TIME} / 退勤済み：${record.CLOCK_OUT_TIME}`, 'success');
            } else {
                clockOutBtn.style.display = 'block';
                showStatusMessage(`出勤済み：${record.CLOCK_IN_TIME}`, 'info');
            }
        } else {
            clockInBtn.style.display = 'block';
            clockOutBtn.style.display = 'none';
            showStatusMessage('未出勤', 'warning');
        }
    }

    function fetchCurrentStatus() {
        fetch('human/api/attendance_api.php?action=getHistory')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const today = new Date().toISOString().slice(0, 10);
                    const todayRecord = data.history.find(record => record.ATTENDANCE_DATE === today);
                    updateUI(todayRecord);
                } else {
                    showStatusMessage('ステータス情報の取得に失敗しました。', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showStatusMessage('通信エラーが発生しました。', 'danger');
            });
    }

    if (clockInBtn) {
        clockInBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('human/api/attendance_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clockIn'
            })
            .then(response => response.json())
            .then(data => {
                showStatusMessage(data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    fetchCurrentStatus();
                }
            });
        });
    }

    if (clockOutBtn) {
        clockOutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('human/api/attendance_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clockOut'
            })
            .then(response => response.json())
            .then(data => {
                showStatusMessage(data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    fetchCurrentStatus();
                }
            });
        });
    }

    fetchCurrentStatus();
});