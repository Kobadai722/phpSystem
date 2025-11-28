<?php
// 設定ファイルの読み込み (パスは環境に合わせて調整してください)
require_once __DIR__ . '/../config.php';
// 自動仕訳機能の読み込み
require_once __DIR__ . '/includes/auto_journal_sales.php';

$message = '';
$alert_type = '';

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $amount = $_POST['amount'] ?? 0;
    $customer_name = $_POST['customer_name'] ?? 'テスト顧客';
    
    // 摘要を作成
    $description = $customer_name . " 様 売上";

    try {
        // トランザクション開始
        $PDO->beginTransaction();

        // ★ここで自動仕訳関数を呼び出す！
        // 実際には、ここで販売管理システムのテーブル更新処理なども行われます
        $journal_id = registerSalesJournal($PDO, $order_date, $amount, $description);

        // すべて成功したらコミット
        $PDO->commit();

        $message = "✅ 売上連携が成功しました！<br>仕訳ID: <strong>{$journal_id}</strong> として登録されました。";
        $alert_type = "success";

    } catch (Exception $e) {
        // エラーが発生したらロールバック
        $PDO->rollBack();
        $message = "❌ エラーが発生しました: " . $e->getMessage();
        $alert_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上連携テスト</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">🛠️ 販売システム連携テスト</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    販売管理システムで「注文確定」が行われたと仮定して、<br>
                    会計システムに自動で仕訳データを登録するテストを行います。
                </p>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label for="order_date" class="form-label">売上日</label>
                        <input type="date" class="form-control" id="order_date" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">顧客名（摘要用）</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="例: 株式会社〇〇" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">売上金額</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="amount" name="amount" placeholder="10000" required>
                            <span class="input-group-text">円</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">売上確定（テスト実行）</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="siwake_hyo/output_siwakehyo.php" class="btn btn-link">仕訳帳を確認する</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>