<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: ../login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠履歴</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>勤怠履歴</h1>
            <a href="main.php" class="btn btn-secondary">人事管理表に戻る</a>
        </div>
        
        <h3><?= htmlspecialchars($employee_name) ?> さんの勤怠記録</h3>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">日付</th>
                        <th scope="col">ステータス</th>
                        <th scope="col">タイムスタンプ</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <tr><td colspan="3" class="text-center">データを読み込み中...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const attendanceTableBody = document.getElementById('attendanceTableBody');

            async function fetchAttendanceHistory() {
                try {
                    const response = await fetch('attendance_history_api.php');
                    if (!response.ok) {
                        throw new Error('ネットワークエラー');
                    }
                    const data = await response.json();
                    
                    attendanceTableBody.innerHTML = '';

                    if (data.success && data.history.length > 0) {
                        data.history.forEach(record => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${record.date}</td>
                                <td>${record.status}</td>
                                <td>${record.timestamp}</td>
                            `;
                            attendanceTableBody.appendChild(row);
                        });
                    } else {
                        attendanceTableBody.innerHTML = `<tr><td colspan="3" class="text-center">勤怠記録がありません。</td></tr>`;
                    }
                } catch (error) {
                    console.error('エラー:', error);
                    attendanceTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">データの取得に失敗しました。</td></tr>`;
                }
            }

            fetchAttendanceHistory();
        });
    </script>
</body>
</html>
