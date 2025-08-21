<?php
session_start();
require_once '../config.php';

// 検索条件の取得
$search_customer_name = $_GET['customer_name'] ?? '';
$search_content = $_GET['content'] ?? '';
$search_status = $_GET['status'] ?? '';

// ベースとなるSQL
$sql = "SELECT i.*, c.NAME AS customer_name 
        FROM INQUIRY_DETAIL i 
        JOIN CUSTOMER c ON i.CUSTOMER_ID = c.CUSTOMER_ID 
        WHERE 1=1";
$params = [];

// 検索条件の組み立て
if (!empty($search_customer_name)) {
    $sql .= " AND c.NAME LIKE ?";
    $params[] = '%' . $search_customer_name . '%';
}
if (!empty($search_content)) {
    $sql .= " AND i.INQUIRY_DETAIL LIKE ?";
    $params[] = '%' . $search_content . '%';
}
if (!empty($search_status)) {
    $sql .= " AND i.STATUS = ?";
    $params[] = $search_status;
}
$sql .= " ORDER BY i.INQUIRY_DATETIME DESC";

$stmt = $PDO->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>問い合わせ一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">問い合わせ一覧</h2>
    <div class="text-end mb-3">
        <a href="inquiry-register.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規登録</a>
    </div>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="customer_name" class="form-label">企業名</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" value="<?= htmlspecialchars($search_customer_name) ?>">
        </div>
        <div class="col-md-4">
            <label for="content" class="form-label">問い合わせ内容</label>
            <input type="text" name="content" id="content" class="form-control" value="<?= htmlspecialchars($search_content) ?>">
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">対応状況</label>
            <select name="status" id="status" class="form-select">
                <option value="">すべて</option>
                <option value="未対応" <?= $search_status == '未対応' ? 'selected' : '' ?>>未対応</option>
                <option value="対応中" <?= $search_status == '対応中' ? 'selected' : '' ?>>対応中</option>
                <option value="対応済み" <?= $search_status == '対応済み' ? 'selected' : '' ?>>対応済み</option>
            </select>
        </div>
        <div class="col-md-1 align-self-end">
            <button type="submit" class="btn btn-primary">検索</button>
        </div>
    </form>
    <hr>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>企業名</th>
                <th>問合せ日時</th>
                <th>内容</th>
                <th>対応状況</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inquiries as $inquiry) : ?>
                <tr>
                    <td><?= htmlspecialchars($inquiry['customer_name']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($inquiry['INQUIRY_DATETIME']))) ?></td>
                    <td style="white-space: pre-wrap;"><?= htmlspecialchars($inquiry['INQUIRY_DETAIL']) ?></td>
                    <td>
                        <form action="inquiry-process.php" method="post" class="d-inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="inquiry_detail_id" value="<?= $inquiry['INQUIRY_DETAIL_ID'] ?>">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="未対応" <?= $inquiry['STATUS'] == '未対応' ? 'selected' : '' ?>>未対応</option>
                                <option value="対応中" <?= $inquiry['STATUS'] == '対応中' ? 'selected' : '' ?>>対応中</option>
                                <option value="対応済み" <?= $inquiry['STATUS'] == '対応済み' ? 'selected' : '' ?>>対応済み</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="inquiry-edit.php?inquiry_detail_id=<?= $inquiry['INQUIRY_DETAIL_ID'] ?>" class="btn btn-primary btn-sm">編集</a>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $inquiry['INQUIRY_DETAIL_ID'] ?>">
                            削除
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">削除の確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><p>この問い合わせを削除しますか？</p></div>
            <div class="modal-footer">
                <form action="inquiry-process.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="inquiry_detail_id" id="inquiryIdToDelete">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger">削除</button>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var inquiryId = button.getAttribute('data-id');
        deleteModal.querySelector('#inquiryIdToDelete').value = inquiryId;
    });
</script>
</body>
</html>