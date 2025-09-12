<?php
session_start();
require_once '../config.php';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: customer.php');
    exit;
}

$customer_id = (int)$_GET['customer_id'];

// 顧客情報を取得
$stmt_customer = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$stmt_customer->execute([$customer_id]);
$customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "指定された顧客は存在しません。";
    exit;
}

// この顧客の問い合わせ履歴を取得
$stmt_inquiries = $PDO->prepare("SELECT * FROM INQUIRY_DETAIL WHERE CUSTOMER_ID = ? ORDER BY INQUIRY_DATETIME DESC");
$stmt_inquiries->execute([$customer_id]);
$inquiries = $stmt_inquiries->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>問い合わせ履歴</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">問い合わせ履歴</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($customer['NAME']) ?></h5>

    <div class="text-end mb-3">
        <a href="customer.php" class="btn btn-secondary">顧客一覧へ戻る</a>
    </div>

    <table class="table table-hover mt-4">
        <thead>
            <tr>
                <th>問合せ日時</th>
                <th>対応チャネル</th>
                <th>内容</th>
                <th>対応状況</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inquiries)): ?>
                <tr>
                    <td colspan="4" class="text-center">この顧客の問い合わせ履歴はありません。</td>
                </tr>
            <?php else: ?>
                <?php foreach ($inquiries as $inquiry) : ?>
                    <tr>
                        <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($inquiry['INQUIRY_DATETIME']))) ?></td>
                        <td><?= htmlspecialchars($inquiry['CHANNEL']) ?></td>
                        <td style="white-space: pre-wrap;"><?= htmlspecialchars($inquiry['INQUIRY_DETAIL']) ?></td>
                        <td>
                            <?php
                            $status = $inquiry['STATUS'];
                            $badge_class = '';
                            switch ($status) {
                                case '未対応':
                                    $badge_class = 'bg-danger';
                                    break;
                                case '対応中':
                                    $badge_class = 'bg-warning text-dark';
                                    break;
                                case '対応済み':
                                    $badge_class = 'bg-success';
                                    break;
                                default:
                                    $badge_class = 'bg-secondary';
                                    break;
                            }
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>