<?php
session_start();
require_once '../config.php';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: customer.php');
    exit;
}
$customer_id = $_GET['customer_id'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>問い合わせ登録</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">問い合わせ登録</h2>
    <form action="inquiry-process.php" method="post">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
        
        <div class="mb-3">
            <label for="inquiry_datetime" class="form-label">問い合わせ日時</label>
            <input type="datetime-local" class="form-control" id="inquiry_datetime" name="inquiry_datetime" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="inquiry_detail" class="form-label">問い合わせ内容</label>
            <textarea class="form-control" id="inquiry_detail" name="inquiry_detail" rows="5" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">登録</button>
        <a href="inquiry.php?customer_id=<?= $customer_id ?>" class="btn btn-secondary">キャンセル</a>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>