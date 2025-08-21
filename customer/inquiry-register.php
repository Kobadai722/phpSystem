<?php
session_start();
require_once '../config.php';

// 顧客リストを取得
$customers = $PDO->query("SELECT CUSTOMER_ID, NAME FROM CUSTOMER ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);
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
        
        <div class="mb-3">
            <label for="customer_id" class="form-label">企業名</label>
            <select class="form-select" id="customer_id" name="customer_id" required>
                <option value="">選択してください</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['CUSTOMER_ID'] ?>"><?= htmlspecialchars($customer['NAME']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="inquiry_datetime" class="form-label">問い合わせ日時</label>
            <input type="datetime-local" class="form-control" id="inquiry_datetime" name="inquiry_datetime" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="inquiry_detail" class="form-label">問い合わせ内容</label>
            <textarea class="form-control" id="inquiry_detail" name="inquiry_detail" rows="5" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">登録</button>
        <a href="inquiry.php" class="btn btn-secondary">キャンセル</a>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>