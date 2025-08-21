<?php
session_start();
require_once '../config.php';

// IDが指定されていない、または不正な場合は一覧ページに戻す
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: customer.php');
    exit;
}

$customer_id = $_GET['id'];

// 編集対象の顧客情報を取得
$stmt = $PDO->prepare("SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// 顧客データが存在しない場合は一覧ページに戻す
if (!$customer) {
    header('Location: customer.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>顧客情報編集</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
    <main class="container">
        <h2 class="my-4">顧客情報編集</h2>
        <form id="editForm" action="customer-update.php" method="post" class="needs-validation" novalidate>
            <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer['CUSTOMER_ID']) ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">企業名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="50" value="<?= htmlspecialchars($customer['NAME']) ?>">
                <div class="invalid-feedback">企業名を入力してください。</div>
            </div>

            <div class="mb-3">
                <label for="cell_number" class="form-label">電話番号</label>
                <input type="tel" class="form-control" id="cell_number" name="cell_number" maxlength="13" value="<?= htmlspecialchars($customer['CELL_NUMBER']) ?>">
                <div class="invalid-feedback">正しい形式の電話番号を入力してください。</div>
            </div>

            <div class="mb-3">
                <label for="mail" class="form-label">メールアドレス <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="mail" name="mail" required maxlength="64" value="<?= htmlspecialchars($customer['MAIL']) ?>">
                <div class="invalid-feedback">有効なメールアドレスを入力してください。</div>
            </div>

            <div class="mb-3">
                <label for="post_code" class="form-label">郵便番号 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="post_code" name="post_code" required maxlength="8" value="<?= htmlspecialchars($customer['POST_CODE']) ?>">
                <div class="invalid-feedback">郵便番号はXXX-XXXXの形式で入力してください。</div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">住所 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address" required maxlength="64" value="<?= htmlspecialchars($customer['ADDRESS']) ?>">
                <div class="invalid-feedback">住所を入力してください。</div>
            </div>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">更新</button>
            <a href="customer.php" class="btn btn-secondary">キャンセル</a>
        </form>
    </main>

    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">更新内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>この内容で更新します。よろしいですか？</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('editForm').submit();">更新する</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>