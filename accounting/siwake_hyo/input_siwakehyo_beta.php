<?php
// ----- ページ設定 -----
$page_title = '仕訳入力フォーム';
$current_page = 'input';

// パスは環境に合わせて調整してください
require_once __DIR__ . '/../includes/a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';


// 勘定科目リストの取得
try {
    $sql = "SELECT ID, NAME FROM ACCOUNTS ORDER BY ID";
    $stmt = $PDO->query($sql);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!-- 共通CSS (レイアウト用) -->
<link rel="stylesheet" href="../css/a_main_beta.css">

<body>
    <!-- ハンバーガーメニュー -->
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php require_once __DIR__ . '/../includes/sidebar_bootstrap.php'; ?>

    <!-- ページ全体を囲むコンテナ -->
    <div class="page-container">
        
        <!-- メインコンテンツ -->
        <main class="main-content" style="padding-left: 80px; padding-top: 20px;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-pencil-square"></i> 仕訳入力</h1>
                <a href="output_siwakehyo.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul"></i> 一覧へ戻る
                </a>
            </div>

            <!-- 入力フォームカード -->
            <div class="card shadow-sm" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">新規取引の登録</h5>
                </div>
                <div class="card-body">
                    <form action="submit_siwake.php" method="post" id="journalForm" class="needs-validation" novalidate>
                        
                        <!-- 1. 基本情報 -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="entry_date" class="form-label fw-bold">日付 <span class="text-danger">*</span></label>
                                <input type="date" name="entry_date" id="entry_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label for="description" class="form-label fw-bold">摘要 <span class="text-danger">*</span></label>
                                <input type="text" name="description" id="description" class="form-control" placeholder="例: 7月分売上、事務用品購入など" required>
                                <div class="invalid-feedback">摘要を入力してください。</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- 2. 仕訳データ (借方・貸方) -->
                        <div class="row g-3">
                            <!-- 借方 (左側) -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border border-primary border-opacity-25">
                                    <h6 class="text-primary mb-3"><i class="bi bi-arrow-left-circle"></i> 借方 (Debit)</h6>
                                    
                                    <div class="mb-3">
                                        <label for="debit_account" class="form-label small">借方科目</label>
                                        <select name="debit_account" id="debit_account" class="form-select" required>
                                            <option value="" selected disabled>選択...</option>
                                            <?php foreach ($accounts as $account): ?>
                                                <option value="<?php echo $account['ID']; ?>">
                                                    <?php echo htmlspecialchars($account['NAME']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="debit_amount" class="form-label small">金額</label>
                                        <div class="input-group">
                                            <input type="number" name="debit_amount" id="debit_amount" class="form-control" placeholder="0" required min="1">
                                            <span class="input-group-text">円</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 貸方 (右側) -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border border-success border-opacity-25">
                                    <h6 class="text-success mb-3">貸方 (Credit) <i class="bi bi-arrow-right-circle"></i></h6>
                                    
                                    <div class="mb-3">
                                        <label for="credit_account" class="form-label small">貸方科目</label>
                                        <select name="credit_account" id="credit_account" class="form-select" required>
                                            <option value="" selected disabled>選択...</option>
                                            <?php foreach ($accounts as $account): ?>
                                                <option value="<?php echo $account['ID']; ?>">
                                                    <?php echo htmlspecialchars($account['NAME']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="credit_amount" class="form-label small">金額</label>
                                        <div class="input-group">
                                            <input type="number" name="credit_amount" id="credit_amount" class="form-control" placeholder="0" required min="1">
                                            <span class="input-group-text">円</span>
                                        </div>
                                        <div class="form-text text-muted" id="amount_diff_msg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 登録ボタン -->
                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle-fill"></i> 仕訳を登録する
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 入力支援スクリプト -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const debitInput = document.getElementById('debit_amount');
            const creditInput = document.getElementById('credit_amount');
            const diffMsg = document.getElementById('amount_diff_msg');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('journalForm');

            // 1. 借方金額を入力したら、貸方にも自動コピーする
            debitInput.addEventListener('input', function() {
                // 貸方が空、または借方と同じ値だった場合は、自動で追従させる
                // (ユーザーが貸方を意図的に変えていない場合のみ)
                if (creditInput.value === '' || creditInput.dataset.autoCopied === 'true') {
                    creditInput.value = this.value;
                    creditInput.dataset.autoCopied = 'true'; // 自動コピー中フラグ
                }
                validateAmounts();
            });

            // 貸方を手動で変更したら、自動コピーフラグを解除
            creditInput.addEventListener('input', function() {
                creditInput.dataset.autoCopied = 'false';
                validateAmounts();
            });

            // 2. 貸借金額の一致チェック
            function validateAmounts() {
                const debit = parseInt(debitInput.value) || 0;
                const credit = parseInt(creditInput.value) || 0;

                if (debit !== credit) {
                    const diff = debit - credit;
                    diffMsg.textContent = `⚠️ 貸借不一致: ${diff > 0 ? '貸方が' + diff + '円不足' : '貸方が' + Math.abs(diff) + '円超過'}`;
                    diffMsg.className = 'form-text text-danger fw-bold';
                    submitBtn.disabled = true; // 不一致なら送信不可
                    creditInput.classList.add('is-invalid');
                } else {
                    diffMsg.textContent = '✅ 貸借一致';
                    diffMsg.className = 'form-text text-success';
                    submitBtn.disabled = false;
                    creditInput.classList.remove('is-invalid');
                }
            }

            // 3. Bootstrapのバリデーション適用
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
</body>
</html>