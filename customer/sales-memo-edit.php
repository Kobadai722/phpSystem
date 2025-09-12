<?php
session_start();
require_once '../config.php';

if (!isset($_GET['negotiation_id']) || !is_numeric($_GET['negotiation_id'])) {
    header('Location: customer.php');
    exit;
}

$negotiation_id = $_GET['negotiation_id'];
$stmt = $PDO->prepare(
    "SELECT nm.*, c.NAME as customer_name 
     FROM NEGOTIATION_MANAGEMENT nm
     JOIN CUSTOMER c ON nm.CUSTOMER_ID = c.CUSTOMER_ID
     WHERE nm.NEGOTIATION_ID = ?"
);
$stmt->execute([$negotiation_id]);
$nego = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nego) {
    header('Location: customer.php');
    exit;
}

// 担当者リスト（社員リスト）を取得
$employees = $PDO->query("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商談編集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">商談編集</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($nego['customer_name']) ?></h5>
    <form action="sales-memo-process.php" method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="negotiation_id" value="<?= $nego['NEGOTIATION_ID'] ?>">
        <input type="hidden" name="customer_id" value="<?= $nego['CUSTOMER_ID'] ?>">
        
        <div class="mb-3">
            <label for="negotiation_date" class="form-label">商談日 <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="negotiation_date" name="negotiation_date" value="<?= htmlspecialchars($nego['NEGOTIATION_DATE']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="employee_id" class="form-label">担当者 <span class="text-danger">*</span></label>
            <select class="form-select" id="employee_id" name="employee_id" required>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['EMPLOYEE_ID'] ?>" <?= ($nego['EMPLOYEE_ID'] == $employee['EMPLOYEE_ID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($employee['NAME']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="trading_amount" class="form-label">取引金額</label>
            <div class="input-group">
                <span class="input-group-text">¥</span>
                <input type="number" class="form-control" id="trading_amount" name="trading_amount" min="0" value="<?= htmlspecialchars($nego['TRADING_AMOUNT']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="order_accuracy" class="form-label">受注確度</label>
            <div class="input-group">
                <input type="number" class="form-control" id="order_accuracy" name="order_accuracy" min="0" max="100" step="0.01" value="<?= htmlspecialchars($nego['ORDER_ACCURACY']) ?>">
                <span class="input-group-text">%</span>
            </div>
        </div>

        <div class="mb-3">
            <label for="negotiation_phase" class="form-label">商談フェーズ <span class="text-danger">*</span></label>
            <select class="form-select" id="negotiation_phase" name="negotiation_phase" required>
                <option value="アプローチ" <?= $nego['NEGOTIATION_PHASE'] == 'アプローチ' ? 'selected' : '' ?>>アプローチ</option>
                <option value="ヒアリング" <?= $nego['NEGOTIATION_PHASE'] == 'ヒアリング' ? 'selected' : '' ?>>ヒアリング</option>
                <option value="提案" <?= $nego['NEGOTIATION_PHASE'] == '提案' ? 'selected' : '' ?>>提案</option>
                <option value="クロージング" <?= $nego['NEGOTIATION_PHASE'] == 'クロージング' ? 'selected' : '' ?>>クロージング</option>
                <option value="受注" <?= $nego['NEGOTIATION_PHASE'] == '受注' ? 'selected' : '' ?>>受注</option>
                <option value="失注" <?= $nego['NEGOTIATION_PHASE'] == '失注' ? 'selected' : '' ?>>失注</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="memo" class="form-label">メモ</label>
            <textarea class="form-control" id="memo" name="memo" rows="5"><?= htmlspecialchars($nego['MEMO']) ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">更新</button>
        <a href="sales-memo.php?customer_id=<?= $nego['CUSTOMER_ID'] ?>" class="btn btn-secondary">キャンセル</a>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>