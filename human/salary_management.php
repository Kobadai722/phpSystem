<?php
session_start();
require_once '../config.php';

// メッセージ初期化
$msg = '';
$msg_type = '';

// --- POST処理（登録・更新） ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['employee_id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    if ($emp_id && $amount !== '') {
        try {
            // 既存があれば更新、なければ挿入 (ON DUPLICATE KEY UPDATE)
            $sql = "INSERT INTO SALARIES (EMPLOYEE_ID, AMOUNT, TYPE) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE AMOUNT = VALUES(AMOUNT), TYPE = VALUES(TYPE)";
            $stmt = $PDO->prepare($sql);
            $stmt->execute([$emp_id, $amount, $type]);
            
            $msg = "給与情報を保存しました。";
            $msg_type = "success";
        } catch (PDOException $e) {
            $msg = "エラー: " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}

// --- 社員と給与情報の取得 ---
// 左結合(LEFT JOIN)で、給与未設定の社員も表示する
$sql = "
    SELECT e.EMPLOYEE_ID, e.NAME, s.AMOUNT, s.TYPE 
    FROM EMPLOYEE e
    LEFT JOIN SALARIES s ON e.EMPLOYEE_ID = s.EMPLOYEE_ID
    ORDER BY e.EMPLOYEE_ID
";
$employees = $PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>給与設定管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-cash-coin"></i> 給与設定管理</h2>
            <a href="editer.php" class="btn btn-secondary">編集者画面へ戻る</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>氏名</th>
                            <th>給与形態</th>
                            <th>金額</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($emp['EMPLOYEE_ID']) ?></td>
                                <td><?= htmlspecialchars($emp['NAME']) ?></td>
                                <td>
                                    <?php if ($emp['TYPE'] === 'monthly'): ?>
                                        <span class="badge bg-primary">月給</span>
                                    <?php elseif ($emp['TYPE'] === 'hourly'): ?>
                                        <span class="badge bg-warning text-dark">時給</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">未設定</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= isset($emp['AMOUNT']) ? '¥' . number_format($emp['AMOUNT']) : '-' ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#salaryModal"
                                            data-id="<?= $emp['EMPLOYEE_ID'] ?>"
                                            data-name="<?= htmlspecialchars($emp['NAME']) ?>"
                                            data-amount="<?= $emp['AMOUNT'] ?? '' ?>"
                                            data-type="<?= $emp['TYPE'] ?? 'monthly' ?>">
                                        <i class="bi bi-pencil"></i> 設定
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">給与設定変更</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="salaryForm">
                        <input type="hidden" name="employee_id" id="modalEmpId">
                        
                        <div class="mb-3">
                            <label class="form-label">対象社員</label>
                            <input type="text" class="form-control" id="modalEmpName" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">給与形態 <span class="text-danger">*</span></label>
                            <select name="type" id="modalType" class="form-select" required>
                                <option value="monthly">月給</option>
                                <option value="hourly">時給</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">金額 (円) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" name="amount" id="modalAmount" class="form-control" min="0" required>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                            <button type="submit" class="btn btn-primary">保存する</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // モーダルにデータを渡す処理
        const salaryModal = document.getElementById('salaryModal');
        salaryModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            
            // データ属性から値を取得
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const type = button.getAttribute('data-type');

            // モーダル内の入力欄にセット
            document.getElementById('modalEmpId').value = id;
            document.getElementById('modalEmpName').value = name;
            document.getElementById('modalAmount').value = amount;
            document.getElementById('modalType').value = type;
        });
    </script>
</body>
</html>