<?php
session_start();
require_once '../config.php';

if (!isset($_GET['inquiry_detail_id']) || !is_numeric($_GET['inquiry_detail_id'])) {
    header('Location: inquiry.php');
    exit;
}

$inquiry_detail_id = $_GET['inquiry_detail_id'];
$stmt = $PDO->prepare("SELECT i.*, c.NAME as customer_name FROM INQUIRY_DETAIL i JOIN CUSTOMER c ON i.CUSTOMER_ID = c.CUSTOMER_ID WHERE i.INQUIRY_DETAIL_ID = ?");
$stmt->execute([$inquiry_detail_id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inquiry) {
    header('Location: inquiry.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>問い合わせ編集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">問い合わせ編集</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($inquiry['customer_name']) ?></h5>
    <form action="inquiry-process.php" method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="inquiry_detail_id" value="<?= $inquiry['INQUIRY_DETAIL_ID'] ?>">
        
        <div class="mb-3">
            <label for="inquiry_datetime" class="form-label">問い合わせ日時</label>
            <input type="datetime-local" class="form-control" id="inquiry_datetime" name="inquiry_datetime" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($inquiry['INQUIRY_DATETIME']))) ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="inquiry_detail" class="form-label">問い合わせ内容</label>
            <textarea class="form-control" id="inquiry_detail" name="inquiry_detail" rows="5" required><?= htmlspecialchars($inquiry['INQUIRY_DETAIL']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">対応状況</label>
            <select name="status" id="status" class="form-select">
                <option value="未対応" <?= $inquiry['STATUS'] == '未対応' ? 'selected' : '' ?>>未対応</option>
                <option value="対応中" <?= $inquiry['STATUS'] == '対応中' ? 'selected' : '' ?>>対応中</option>
                <option value="対応済み" <?= $inquiry['STATUS'] == '対応済み' ? 'selected' : '' ?>>対応済み</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">更新</button>
        <a href="inquiry.php" class="btn btn-secondary">キャンセル</a>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>