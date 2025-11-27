<?php
session_start();
require_once '../config.php';

// 申請一覧を取得（申請者の名前も結合して取得）
$sql = "
    SELECT a.*, e.NAME as employee_name 
    FROM APPLICATIONS a
    JOIN EMPLOYEE e ON a.EMPLOYEE_ID = e.EMPLOYEE_ID
    ORDER BY a.STATUS = 'pending' DESC, a.CREATED_AT DESC
";
$stmt = $PDO->query($sql);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 申請種別の日本語マッピング
$type_map = [
    'paid_leave' => '有給休暇',
    'attendance_correction' => '打刻修正',
    'overtime' => '残業申請',
    'other' => 'その他'
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>申請一覧 - 管理者用</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>申請一覧</h2>
            <a href="editer.php" class="btn btn-secondary">編集者画面へ戻る</a>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>申請ID</th>
                        <th>申請者</th>
                        <th>種別</th>
                        <th>対象日</th>
                        <th>理由・備考</th>
                        <th>申請日時</th>
                        <th>ステータス</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="8" class="text-center">申請はありません。</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app['APPLICATION_ID']) ?></td>
                                <td><?= htmlspecialchars($app['employee_name']) ?></td>
                                <td><?= htmlspecialchars($type_map[$app['APPLICATION_TYPE']] ?? $app['APPLICATION_TYPE']) ?></td>
                                <td><?= htmlspecialchars($app['TARGET_DATE']) ?></td>
                                <td><?= nl2br(htmlspecialchars($app['REASON'])) ?></td>
                                <td><?= htmlspecialchars(date('Y/m/d H:i', strtotime($app['CREATED_AT']))) ?></td>
                                <td>
                                    <?php if ($app['STATUS'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">未承認</span>
                                    <?php elseif ($app['STATUS'] === 'approved'): ?>
                                        <span class="badge bg-success">承認済</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">却下</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($app['STATUS'] === 'pending'): ?>
                                        <form action="application_status_update.php" method="post" class="d-flex gap-1">
                                            <input type="hidden" name="application_id" value="<?= $app['APPLICATION_ID'] ?>">
                                            <button type="submit" name="status" value="approved" class="btn btn-sm btn-success">承認</button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger">却下</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>