<?php
session_start();
require_once '../config.php';

// IDチェック
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: editer.php');
    exit;
}

$attendance_id = $_GET['id'];

// 対象の勤怠データを取得
$stmt = $PDO->prepare("
    SELECT a.*, e.NAME as employee_name 
    FROM ATTENDANCE a
    JOIN EMPLOYEE e ON a.EMPLOYEE_ID = e.EMPLOYEE_ID
    WHERE a.ATTENDANCE_ID = ?
");
$stmt->execute([$attendance_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error_message'] = "データが見つかりません。";
    header('Location: editer.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠修正</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <h2>勤怠データ修正</h2>
        <div class="card mt-4">
            <div class="card-header">
                <?= htmlspecialchars($record['employee_name']) ?> さんの勤怠（<?= htmlspecialchars($record['ATTENDANCE_DATE']) ?>）
            </div>
            <div class="card-body">
                <form action="attendance_update.php" method="post">
                    <input type="hidden" name="attendance_id" value="<?= htmlspecialchars($record['ATTENDANCE_ID']) ?>">
                    
                    <div class="mb-3">
                        <label for="attendance_time" class="form-label">出勤時刻</label>
                        <input type="time" class="form-control" id="attendance_time" name="attendance_time" step="1" value="<?= htmlspecialchars($record['ATTENDANCE_TIME']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="leave_time" class="form-label">退勤時刻</label>
                        <input type="time" class="form-control" id="leave_time" name="leave_time" step="1" value="<?= htmlspecialchars($record['LEAVE_TIME'] ?? '') ?>">
                        <div class="form-text">退勤していない場合は空欄にしてください。</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="editer.php" class="btn btn-secondary">キャンセル</a>
                        <div>
                            <button type="submit" name="action" value="delete" class="btn btn-danger me-2" onclick="return confirm('本当にこの記録を削除しますか？');">削除</button>
                            <button type="submit" name="action" value="update" class="btn btn-primary">修正を保存</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>