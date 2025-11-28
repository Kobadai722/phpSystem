<?php
// ----- ページ設定と部品の読み込み -----
$page_title = '仕訳一覧表示';
$current_page = 'list';


// a_header.phpでCSS等を読み込んでいます
require_once __DIR__ . '/../includes/a_header.php';
// パスは環境に合わせて調整してください
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';
// =================================================================
// 1. 検索条件・ソート条件の取得
// =================================================================

// 検索パラメータ
$search_start_date = $_GET['start_date'] ?? '';
$search_end_date   = $_GET['end_date'] ?? '';
$search_account_id = $_GET['account_id'] ?? '';

// ソートパラメータ (デフォルトは日付の降順)
$sort_column = $_GET['sort'] ?? 'date';
$sort_order  = $_GET['order'] ?? 'desc';

// ソート順の切り替えロジック
$next_order_date   = ($sort_column === 'date' && $sort_order === 'desc') ? 'asc' : 'desc';
$next_order_amount = ($sort_column === 'amount' && $sort_order === 'desc') ? 'asc' : 'desc';

// アイコンの表示ロジック
$icon_date   = ($sort_column === 'date') ? ($sort_order === 'desc' ? '▼' : '▲') : '';
$icon_amount = ($sort_column === 'amount') ? ($sort_order === 'desc' ? '▼' : '▲') : '';


// =================================================================
// 2. データベースからのデータ取得
// =================================================================
try {
    // --- 勘定科目リストの取得 ---
    $stmt_acc = $PDO->query("SELECT ID, NAME FROM ACCOUNTS ORDER BY ID");
    $accounts_list = $stmt_acc->fetchAll(PDO::FETCH_ASSOC);

    // --- 仕訳データの取得クエリ構築 ---
    $sql = "SELECT
                h.ID,
                h.ENTRY_DATE,
                h.DESCRIPTION,
                debit_acc.NAME AS debit_name,
                debit_entry.AMOUNT AS debit_amount,
                credit_acc.NAME AS credit_name,
                credit_entry.AMOUNT AS credit_amount
            FROM
                JOURNAL_HEADERS AS h
            LEFT JOIN
                JOURNAL_ENTRIES AS debit_entry ON h.ID = debit_entry.HEADER_ID AND debit_entry.TYPE = '借方'
            LEFT JOIN
                ACCOUNTS AS debit_acc ON debit_entry.ACCOUNT_ID = debit_acc.ID
            LEFT JOIN
                JOURNAL_ENTRIES AS credit_entry ON h.ID = credit_entry.HEADER_ID AND credit_entry.TYPE = '貸方'
            LEFT JOIN
                ACCOUNTS AS credit_acc ON credit_entry.ACCOUNT_ID = credit_acc.ID";

    // --- 検索条件 (WHERE句) ---
    $where_clauses = [];
    $params = [];

    if ($search_start_date !== '') {
        $where_clauses[] = "h.ENTRY_DATE >= ?";
        $params[] = $search_start_date;
    }
    if ($search_end_date !== '') {
        $where_clauses[] = "h.ENTRY_DATE <= ?";
        $params[] = $search_end_date;
    }
    if ($search_account_id !== '') {
        $where_clauses[] = "(debit_entry.ACCOUNT_ID = ? OR credit_entry.ACCOUNT_ID = ?)";
        $params[] = $search_account_id;
        $params[] = $search_account_id;
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // --- ソート順 (ORDER BY句) ---
    if ($sort_column === 'amount') {
        $sql .= " ORDER BY debit_entry.AMOUNT " . ($sort_order === 'asc' ? 'ASC' : 'DESC');
    } else {
        $sql .= " ORDER BY h.ENTRY_DATE " . ($sort_order === 'asc' ? 'ASC' : 'DESC') . ", h.ID DESC";
    }

    $stmt = $PDO->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!-- a_main_beta.cssを追加で読み込む（レイアウト調整用） -->
<link rel="stylesheet" href="../css/a_main_beta.css">

<body>
    <!-- ハンバーガーメニュー (Offcanvasトリガー) -->
    <button class="btn btn-light shadow-sm hamburger-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>

    <?php 
    // Bootstrap版サイドバーを読み込み
    require_once __DIR__ . '/../includes/sidebar_bootstrap.php'; 
    ?>

    <!-- ページ全体を囲むコンテナ -->
    <div class="page-container">
        
        <!-- メインコンテンツ -->
        <!-- padding-left: 80px でハンバーガーボタンとの重なりを防ぐ -->
        <main class="main-content" style="padding-left: 80px; padding-top: 20px;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>📘 <?php echo htmlspecialchars($page_title); ?></h1>
                <a href="/accounting/siwake_hyo/input_siwakehyo.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> 新規登録
                </a>
            </div>

            <!-- 検索・絞り込みフォーム -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body bg-light">
                    <form action="" method="GET">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
                        <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order); ?>">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">日付期間</label>
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($search_start_date); ?>">
                                    <span class="input-group-text">～</span>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($search_end_date); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">勘定科目</label>
                                <select name="account_id" class="form-select">
                                    <option value="">-- 全て --</option>
                                    <?php foreach ($accounts_list as $acc): ?>
                                        <option value="<?php echo $acc['ID']; ?>" <?php if ((string)$acc['ID'] === $search_account_id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($acc['NAME']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> 絞り込み
                                </button>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="output_siwakehyo_beta.php" class="btn btn-outline-secondary btn-sm">条件クリア</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 仕訳一覧テーブル -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped bg-white">
                    <thead class="table-primary">
                        <tr>
                            <th>仕訳番号</th>
                            <th>
                                <a href="?sort=date&order=<?php echo $next_order_date; ?>&start_date=<?php echo htmlspecialchars($search_start_date); ?>&end_date=<?php echo htmlspecialchars($search_end_date); ?>&account_id=<?php echo htmlspecialchars($search_account_id); ?>" class="text-dark text-decoration-none d-block">
                                    日付 <?php echo $icon_date; ?>
                                </a>
                            </th>
                            <th>摘要</th>
                            <th>借方科目</th>
                            <th>
                                <a href="?sort=amount&order=<?php echo $next_order_amount; ?>&start_date=<?php echo htmlspecialchars($search_start_date); ?>&end_date=<?php echo htmlspecialchars($search_end_date); ?>&account_id=<?php echo htmlspecialchars($search_account_id); ?>" class="text-dark text-decoration-none d-block">
                                    借方金額 <?php echo $icon_amount; ?>
                                </a>
                            </th>
                            <th>貸方科目</th>
                            <th>貸方金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">該当するデータがありません。</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($row['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ENTRY_DATE']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
                                    <td><?php echo htmlspecialchars($row['debit_name'] ?? ''); ?></td>
                                    <td class="text-end"><?php echo is_numeric($row['debit_amount']) ? number_format($row['debit_amount']) : ''; ?></td>
                                    <td><?php echo htmlspecialchars($row['credit_name'] ?? ''); ?></td>
                                    <td class="text-end"><?php echo is_numeric($row['credit_amount']) ? number_format($row['credit_amount']) : ''; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <a href="../../main.php" class="btn btn-secondary">トップページに戻る</a>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS (Offcanvas動作に必須) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>