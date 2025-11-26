<?php
session_start(); // セッション開始
require_once '../config.php';

$page_h1_title = "人事詳細";
$page_title_tag = "人事管理表 - 詳細";
$employee_data = null;
$error_message_for_table = null;
$remaining_leaves = 0; // 残日数の初期化

// 社員IDの取得
$employee_id = $_GET['id'] ?? null;

if (isset($employee_id) && is_numeric($employee_id)) {
    // 社員情報の取得
    $stmt = $PDO->prepare("
        SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
        FROM EMPLOYEE e
        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID
        WHERE e.EMPLOYEE_ID = ?
    ");
    $stmt->execute([$employee_id]);
    $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee_data) {
        $employee_name = htmlspecialchars($employee_data['NAME']);
        $page_h1_title = $employee_name . "さんの詳細";
        $page_title_tag = $employee_name . "さんの詳細 - 人事管理表";

        //有給残日数の計算 (有効期限内で未消化のもの）
        $today = date('Y-m-d');
        $stmt_leave = $PDO->prepare("
            SELECT SUM(DAYS_GRANTED - DAYS_USED) 
            FROM PAID_LEAVES 
            WHERE EMPLOYEE_ID = ? AND EXPIRATION_DATE >= ?
        ");
        $stmt_leave->execute([$employee_id, $today]);
        $remaining_leaves = $stmt_leave->fetchColumn() ?: 0;

    } else {
        $error_message_for_table = "該当社員が見つかりません。";
        $page_h1_title = "エラー";
        $page_title_tag = "該当社員なし - 人事管理表";
    }
} else {
    $error_message_for_table = "不正なリクエストです。";
    $page_h1_title = "エラー";
    $page_title_tag = "不正なリクエスト - 人事管理表";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title_tag; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container py-4">
        <h1><?php echo $page_h1_title; ?></h1>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="mb-3 p-3 border rounded">
            <form>
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="display_mode_select" class="col-form-label">表示モード：</label>
                    </div>
                    <div class="col-auto">
                        <select id="display_mode_select" name="edit_mode_select" class="form-select" onchange="location = this.value;">
                            <option value="detail.php?id=<?= htmlspecialchars($employee_id ?? '') ?>" selected>表示モード</option>
                            <option value="detail_edit.php?id=<?= htmlspecialchars($employee_id ?? '') ?>">編集モード</option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (isset($_SESSION['employee_id']) && $_SESSION['employee_id'] == $employee_id): ?>
                <div class="mt-3 ps-2 border-start border-4 border-success">
                    <span class="fw-bold text-secondary">現在の有給休暇残日数:</span>
                    <span class="fs-5 fw-bold text-success ms-2"><?= htmlspecialchars($remaining_leaves) ?> 日</span>
                </div>
            <?php endif; ?>
            <div class="text-end mt-2">
                <a href="main.php" class="btn btn-outline-secondary">メインページへ戻る</a>
            </div>
        </div>

        <?php if ($error_message_for_table): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($error_message_for_table) ?>
            </div>
        <?php elseif ($employee_data): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>社員番号</th>
                        <th>氏名</th>
                        <th>所属部署</th>
                        <th>職位</th>
                        <th>緊急連絡先</th>
                        <th>入社日</th>
                        <th>郵便番号</th>
                        <th>住所</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($employee_data['EMPLOYEE_ID']) ?></td>
                        <td><?= htmlspecialchars($employee_data['NAME']) ?></td>
                        <td><?= htmlspecialchars($employee_data['DIVISION_NAME'] ?? '未登録') ?></td>
                        <td><?= htmlspecialchars($employee_data['JOB_POSITION_NAME'] ?? '未登録') ?></td>
                        <td><?= htmlspecialchars($employee_data['EMERGENCY_CELL_NUMBER'] ?? '未入力') ?></td>
                        <td><?= htmlspecialchars($employee_data['JOINING_DATE'] ?? '未入力') ?></td>
                        <td><?= htmlspecialchars($employee_data['POST_CODE'] ?? '未入力') ?></td>
                        <td><?= htmlspecialchars($employee_data['ADDRESS'] ?? '未入力') ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>