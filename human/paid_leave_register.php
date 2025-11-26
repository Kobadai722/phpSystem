<?php
session_start();
require_once '../config.php';

// 処理: 有給付与
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $grant_date = $_POST['grant_date'];
    $days_granted = $_POST['days_granted'];
    
    // 有効期限は付与日から2年後
    $expiration_date = date('Y-m-d', strtotime($grant_date . ' +2 year'));

    try {
        $stmt = $PDO->prepare("INSERT INTO PAID_LEAVES (EMPLOYEE_ID, GRANT_DATE, DAYS_GRANTED, EXPIRATION_DATE) VALUES (?, ?, ?, ?)");
        $stmt->execute([$employee_id, $grant_date, $days_granted, $expiration_date]);
        $message = "社員ID: {$employee_id} に {$days_granted} 日の有給を付与しました。";
    } catch (PDOException $e) {
        $error = "エラー: " . $e->getMessage();
    }
}

// 社員リスト取得
$employees = $PDO->query("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>有給休暇付与</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>有給休暇付与</h2>
            <a href="editer.php" class="btn btn-secondary">編集者画面へ戻る</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">対象社員</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">選択してください</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['EMPLOYEE_ID'] ?>">
                                <?= htmlspecialchars($emp['NAME']) ?> (ID: <?= $emp['EMPLOYEE_ID'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">付与日</label>
                        <input type="date" name="grant_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">付与日数</label>
                        <input type="number" name="days_granted" class="form-control" value="10" min="1" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">付与する</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>