<?php
session_start();
require_once '../config.php';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: customer.php');
    exit;
}
$customer_id = $_GET['customer_id'];

// 顧客情報を取得
$customer_stmt = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$customer_stmt->execute([$customer_id]);
$customer_data = $customer_stmt->fetch(PDO::FETCH_ASSOC);

// 担当者リスト（社員リスト）を取得
$employees = $PDO->query("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE WHERE IS_DELETED = 0 ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商談登録</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">商談登録</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($customer_data['NAME']) ?></h5>
    <form action="sales-memo-process.php" method="post">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
        
        <div class="mb-3">
            <label for="employee_id" class="form-label">担当者 <span class="text-danger">*</span></label>
            <select class="form-select" id="employee_id" name="employee_id" required>
                <option value="" selected disabled>選択してください</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['EMPLOYEE_ID'] ?>"><?= htmlspecialchars($employee['NAME']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="trading_amount" class="form-label">取引金額</label>
            <div class="input-group">
                <span class="input-group-text">¥</span>
                <input type="number" class="form-control" id="trading_amount" name="trading_amount" min="0" placeholder="例: 500000">
            </div>
        </div>

        <div class="mb-3">
            <label for="order_accuracy" class="form-label">受注確度</label>
            <div class="input-group">
                <input type="number" class="form-control" id="order_accuracy" name="order_accuracy" min="0" max="100" step="0.01" placeholder="例: 80.5">
                <span class="input-group-text">%</span>
            </div>
        </div>

        <div class="mb-3">
            <label for="negotiation_phase" class="form-label">商談フェーズ <span class="text-danger">*</span></label>
            <select class="form-select" id="negotiation_phase" name="negotiation_phase" required>
                <option value="" selected disabled>選択してください</option>
                <option value="アプローチ">アプローチ</option>
                <option value="ヒアリング">ヒアリング</option>
                <option value="提案">提案</option>
                <option value="クロージング">クロージング</option>
                <option value="受注">受注</option>
                <option value="失注">失注</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">登録</button>
        <a href="sales-memo.php?customer_id=<?= $customer_id ?>" class="btn btn-secondary">キャンセル</a>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>