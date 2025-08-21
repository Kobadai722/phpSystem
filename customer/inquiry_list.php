<?php
session_start();
require_once '../config.php';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: ../customer/customer.php');
    exit;
}

$customer_id = $_GET['customer_id'];

// 顧客情報を取得
$customer_stmt = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$customer_stmt->execute([$customer_id]);
$customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header('Location: ../customer/customer.php');
    exit;
}

// 問い合わせ情報を取得
$inquiries_stmt = $PDO->prepare("SELECT * FROM INQUIRY_DETAIL WHERE CUSTOMER_ID = ? ORDER BY INQUIRY_DATETIME DESC");
$inquiries_stmt->execute([$customer_id]);
$inquiries = $inquiries_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>問い合わせ管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4"><?= htmlspecialchars($customer['NAME']) ?>様 お問い合わせ一覧</h2>
    <div class="text-end mb-3">
        <a href="inquiry_register.php?customer_id=<?= $customer_id ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規登録</a>
        <a href="../customer/customer.php" class="btn btn-secondary">顧客一覧に戻る</a>
    </div>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>問合せ日時</th>
                <th>内容</th>
                <th>対応状況</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inquiries as $inquiry) : ?>
                <tr>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($inquiry['INQUIRY_DATETIME']))) ?></td>
                    <td style="white-space: pre-wrap;"><?= htmlspecialchars($inquiry['INQUIRY_DETAIL']) ?></td>
                    <td>
                        <form action="inquiry_process.php" method="post" class="d-inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="inquiry_detail_id" value="<?= $inquiry['INQUIRY_DETAIL_ID'] ?>">
                            <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="未対応" <?= $inquiry['STATUS'] == '未対応' ? 'selected' : '' ?>>未対応</option>
                                <option value="対応中" <?= $inquiry['STATUS'] == '対応中' ? 'selected' : '' ?>>対応中</option>
                                <option value="対応済み" <?= $inquiry['STATUS'] == '対応済み' ? 'selected' : '' ?>>対応済み</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="inquiry_edit.php?inquiry_detail_id=<?= $inquiry['INQUIRY_DETAIL_ID'] ?>" class="btn btn-primary btn-sm">編集</a>
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
            <div class="modal-body">
                <p>この問い合わせを削除しますか？</p>
            </div>
            <div class="modal-footer">
                <form action="inquiry_process.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="inquiry_detail_id" id="inquiryIdToDelete">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
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
        var modalInputId = deleteModal.querySelector('#inquiryIdToDelete');
        modalInputId.value = inquiryId;
    });
</script>
</body>
</html>