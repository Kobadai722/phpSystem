<?php
session_start();
require_once '../config.php';

// --- エラーを画面に表示する設定 ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    header('Location: customer.php');
    exit;
}

// GETパラメータを強制的に整数に変換
$customer_id = (int)$_GET['customer_id'];

// --- 顧客情報の取得 ---
$stmt_customer = $PDO->prepare("SELECT NAME FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$stmt_customer->execute([$customer_id]);
$customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    // 顧客が存在しない場合は安全に終了
    echo "指定された顧客IDは存在しません。";
    exit;
}

// --- 商談情報の取得 ---
$negotiations = []; // 変数を初期化
try {
    // 【修正箇所】プリペアドステートメントを使用し、ORDER BY句を新しい日付カラム (NEGOTIATION_DATE) に修正
    $stmt_negotiation = $PDO->prepare(
        "SELECT * FROM NEGOTIATION_MANAGEMENT WHERE CUSTOMER_ID = ? ORDER BY NEGOTIATION_DATE DESC"
    );
    $stmt_negotiation->execute([$customer_id]);
    $negotiations = $stmt_negotiation->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // もしエラーが出た場合に備える
    die("データベースエラー: " . $e->getMessage());
}


// --- 社員情報の取得 ---
$employee_stmt = $PDO->query("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE");
$employees = $employee_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商談管理一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
<main class="container">
    <h2 class="my-4">商談管理一覧</h2>
    <h5 class="mb-4">顧客名: <?= htmlspecialchars($customer['NAME']) ?></h5>

    <div class="text-end mb-3">
        <a href="sales-memo-register.php?customer_id=<?= $customer_id ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規登録</a>
        <a href="customer.php" class="btn btn-secondary">顧客一覧へ戻る</a>
    </div>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <table class="table table-hover mt-4">
        <thead>
            <tr>
                <th>商談日</th>
                <th>担当者</th>
                <th>取引金額</th>
                <th>受注確度</th>
                <th>商談フェーズ</th>
                <th>メモ</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($negotiations)): ?>
                <tr>
                    <td colspan="7" class="text-center">この顧客の商談データはまだありません。</td>
                </tr>
            <?php else: ?>
                <?php foreach ($negotiations as $nego) : ?>
                    <tr>
                        <td><?= htmlspecialchars($nego['NEGOTIATION_DATE']) ?></td>
                        <?php
                            $employee_name = $employees[$nego['EMPLOYEE_ID']] ?? '不明';
                        ?>
                        <td><?= htmlspecialchars($employee_name) ?></td>
                        <td><?= $nego['TRADING_AMOUNT'] !== null ? '¥' . number_format($nego['TRADING_AMOUNT']) : 'N/A' ?></td>
                        <td><?= $nego['ORDER_ACCURACY'] !== null ? htmlspecialchars($nego['ORDER_ACCURACY']) . '%' : 'N/A' ?></td>
                        <td><?= htmlspecialchars($nego['NEGOTIATION_PHASE']) ?></td>
                        <td style="white-space: pre-wrap;"><?= htmlspecialchars($nego['MEMO']) ?></td>
                        <td>
                            <a href="sales-memo-edit.php?negotiation_id=<?= $nego['NEGOTIATION_ID'] ?>" class="btn btn-primary btn-sm">編集</a>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $nego['NEGOTIATION_ID'] ?>">
                                削除
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
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
            <div class="modal-body"><p>この商談情報を削除しますか？</p></div>
            <div class="modal-footer">
                <form action="sales-memo-process.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="negotiation_id" id="negoIdToDelete">
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
        var negoId = button.getAttribute('data-id');
        deleteModal.querySelector('#negoIdToDelete').value = negoId;
    });
</script>
</body>
</html>