<?php
session_start(); // セッション開始
require_once '../config.php';

$page_h1_title = "人事詳細";
$page_title_tag = "人事管理表 - 詳細";
$employee_data = null;
$error_message_for_table = null;

// 社員IDの取得
$employee_id = $_GET['id'] ?? null;

if (isset($employee_id) && is_numeric($employee_id)) {
    $stmt = $PDO->prepare("
        SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
        FROM EMPLOYEE e
        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID
        WHERE e.EMPLOYEE_ID = ?
    ");
    $stmt->execute([$employee_id]);
    $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee_data) {
        $employee_name = htmlspecialchars($employee_data['NAME']);
        $page_h1_title = $employee_name . "さんの詳細・編集";
        $page_title_tag = $employee_name . "さんの詳細・編集 - 人事管理表";
    } else {
        $error_message_for_table = "該当社員が見つかりません。";
        $page_h1_title = "エラー";
        $page_title_tag = "該当社員なし - 人事管理表";
    }
} else {
    $error_message_for_table = "不正なリクエストです。";
    $page_h1_title = "エラー";
    $page_title_tag = "不正なリクエスト - 人事管理表";
}

// 部署と職位のリストを取得 (編集フォーム用)
$divisions = [];
$job_positions = [];
if ($employee_data) { // 社員データがある場合のみ取得
    $stmt_divisions = $PDO->query("SELECT DIVISION_ID, DIVISION_NAME FROM DIVISION ORDER BY DIVISION_ID");
    $divisions = $stmt_divisions->fetchAll(PDO::FETCH_ASSOC);

    $stmt_jobs = $PDO->query("SELECT JOB_POSITION_ID, JOB_POSITION_NAME FROM JOB_POSITION ORDER BY JOB_POSITION_ID");
    $job_positions = $stmt_jobs->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title_tag; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../header.php'; ?>
<body>
    <div class="container py-4">
        <h1><?php echo $page_h1_title; ?></h1>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="mb-3 p-3 border rounded">
            <div class="text-end mt-2">
                <a href="main.php" class="btn btn-outline-secondary">メインページへ戻る</a>
                <a href="editer.php" class="btn btn-outline-info">編集者ページへ戻る</a>
            </div>
        </div>
        
        <?php if ($error_message_for_table): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($error_message_for_table) ?>
            </div>
        <?php elseif ($employee_data): ?>
            <?php if ($employee_data['IS_DELETED']): ?>
                <div class="alert alert-warning" role="alert">
                    この社員は現在削除済みです。復元すると編集可能になります。
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>社員番号</th>
                            <th>氏名</th>
                            <th>所属部署</th>
                            <th>職位</th>
                            <th>緊急連絡先</th>
                            <th>入社日</th>
                            <th>郵便番号</th>
                            <th>住所</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?></td>
                            <td><?= htmlspecialchars($employee_data['NAME']) ?> <span class="badge bg-danger">削除済み</span></td>
                            <td><?= htmlspecialchars($employee_data['DIVISION_NAME'] ?? '未登録') ?></td>
                            <td><?= htmlspecialchars($employee_data['JOB_POSITION_NAME'] ?? '未登録') ?></td>
                            <td><?= htmlspecialchars($employee_data['EMERGENCY_CELL_NUMBER'] ?? '未入力') ?></td>
                            <td><?= htmlspecialchars($employee_data['JOINING_DATE'] ?? '未入力') ?></td>
                            <td><?= htmlspecialchars($employee_data['POST_CODE'] ?? '未入力') ?></td>
                            <td><?= htmlspecialchars($employee_data['ADDRESS'] ?? '未入力') ?></td>
                        </tr>
                    </tbody>
                </table>
                <form action="human-restore.php" method="post" class="mt-3">
                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?>">
                    <button type="submit" class="btn btn-info">この社員を復元する</button>
                </form>
            <?php else: ?>
                <form action="human-update.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="EMPLOYEE_ID" value="<?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="NAME" class="form-label">氏名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="NAME" name="NAME" value="<?= htmlspecialchars($employee_data['NAME']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="DIVISION_ID" class="form-label">所属部署 <span class="text-danger">*</span></label>
                            <select class="form-select" id="DIVISION_ID" name="DIVISION_ID" required>
                                <?php foreach ($divisions as $division): ?>
                                    <option value="<?= htmlspecialchars($division['DIVISION_ID']) ?>" <?= ($employee_data['DIVISION_ID'] == $division['DIVISION_ID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($division['DIVISION_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="JOB_POSITION_ID" class="form-label">職位 <span class="text-danger">*</span></label>
                            <select class="form-select" id="JOB_POSITION_ID" name="JOB_POSITION_ID" required>
                                <?php foreach ($job_positions as $job): ?>
                                    <option value="<?= htmlspecialchars($job['JOB_POSITION_ID']) ?>" <?= ($employee_data['JOB_POSITION_ID'] == $job['JOB_POSITION_ID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($job['JOB_POSITION_NAME']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="JOINING_DATE" class="form-label">入社日</label>
                            <input type="date" class="form-control" id="JOINING_DATE" name="JOINING_DATE" value="<?= htmlspecialchars($employee_data['JOINING_DATE']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="EMERGENCY_CELL_NUMBER" class="form-label">緊急連絡先</label>
                            <input type="tel" class="form-control" id="EMERGENCY_CELL_NUMBER" name="EMERGENCY_CELL_NUMBER" value="<?= htmlspecialchars($employee_data['EMERGENCY_CELL_NUMBER']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="POST_CODE" class="form-label">郵便番号</label>
                            <input type="text" class="form-control" id="POST_CODE" name="POST_CODE" value="<?= htmlspecialchars($employee_data['POST_CODE']) ?>">
                        </div>
                        <div class="col-12">
                            <label for="ADDRESS" class="form-label">住所</label>
                            <input type="text" class="form-control" id="ADDRESS" name="ADDRESS" value="<?= htmlspecialchars($employee_data['ADDRESS']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="PASSWORD" class="form-label">パスワード (変更する場合のみ入力)</label>
                            <input type="password" class="form-control" id="PASSWORD" name="PASSWORD">
                            <small class="form-text text-muted">※パスワードを更新しない場合は空欄のままにしてください。</small>
                        </div>
                    </div>
                    <hr class="my-4">
                    <button class="btn btn-primary" type="submit">更新する</button>
                    <a href="detail.php?id=<?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?>" class="btn btn-secondary">表示モードに戻る</a>
                </form>
                <hr class="my-4">
                <button type="button" class="btn btn-danger"
                        data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
                        data-employee-id="<?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?>"
                        data-employee-name="<?= htmlspecialchars($employee_data['NAME']) ?>">
                    この社員を削除する
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">削除確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                <div class="modal-body">
                    本当に <strong id="modalEmployeeName"></strong> さんの情報を削除しますか？<br>
                    この操作は元に戻せます (論理削除)。
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <form action="human-delete.php" method="post" style="display: inline;">
                        <input type="hidden" name="employee_id" id="modalEmployeeId" value="">
                        <button type="submit" class="btn btn-danger">削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 削除モーダル用のJavaScript (editer.phpやhuman.jsから流用)
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButton = document.querySelector('.btn-danger[data-bs-target="#deleteConfirmModal"]');
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');

        if (deleteButton && deleteConfirmModal) {
            const modalEmployeeNameSpan = deleteConfirmModal.querySelector('#modalEmployeeName');
            const modalEmployeeIdInput = deleteConfirmModal.querySelector('#modalEmployeeId');

            deleteButton.addEventListener('click', function() {
                const employeeId = this.dataset.employeeId;
                const employeeName = this.dataset.employeeName;

                if (modalEmployeeNameSpan) {
                    modalEmployeeNameSpan.textContent = employeeName;
                }
                if (modalEmployeeIdInput) {
                    modalEmployeeIdInput.value = employeeId;
                }
            });
        }
    });
</script>
</html>