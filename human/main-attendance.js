document.addEventListener('DOMContentLoaded', function() {
    const clockInBtn = document.getElementById('mainClockInBtn');
    const clockOutBtn = document.getElementById('mainClockOutBtn');
    const statusMessage = document.getElementById('statusMessage');

    function updateUI(record) {
        if (record) {
            clockInBtn.style.display = 'none';
            if (record.CLOCK_OUT_TIME) {
                clockOutBtn.style.display = 'none';
                statusMessage.textContent = `出勤済み：${record.CLOCK_IN_TIME} / 退勤済み：${record.CLOCK_OUT_TIME}`;
                statusMessage.className = 'alert alert-success text-center fw-bold fs-5';
            } else {
                clockOutBtn.style.display = 'block';
                statusMessage.textContent = `出勤済み：${record.CLOCK_IN_TIME}`;
                statusMessage.className = 'alert alert-info text-center fw-bold fs-5';
            }
        } else {
            clockInBtn.style.display = 'block';
            clockOutBtn.style.display = 'none';
            statusMessage.textContent = '未出勤';
            statusMessage.className = 'alert alert-warning text-center fw-bold fs-5';
        }
    }

    function fetchCurrentStatus() {
        // APIのパスを修正
        fetch('human/attendance_api.php?action=getHistory')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const today = new Date().toISOString().slice(0, 10);
                    const todayRecord = data.history.find(record => record.ATTENDANCE_DATE === today);
                    updateUI(todayRecord);
                } else {
                    statusMessage.textContent = 'ステータス情報の取得に失敗しました。';
                    statusMessage.className = 'alert alert-danger text-center fw-bold fs-5';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusMessage.textContent = '通信エラーが発生しました。';
                statusMessage.className = 'alert alert-danger text-center fw-bold fs-5';
            });
    }

    clockInBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // APIのパスを修正
        fetch('human/attendance_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=clockIn'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                fetchCurrentStatus();
            } else {
                alert(data.message);
            }
        });
    });

    clockOutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // APIのパスを修正
        fetch('human/attendance_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=clockOut'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                fetchCurrentStatus();
            } else {
                alert(data.message);
            }
        });
    });

    fetchCurrentStatus();
});