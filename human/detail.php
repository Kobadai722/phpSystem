<?php
require_once '../config.php';

$page_h1_title = "人事詳細";
$page_title_tag = "人事管理表 - 詳細";
$employee_data = null;
$error_message_for_table = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $PDO->prepare("
        SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
        FROM EMPLOYEE e
        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID
        WHERE e.EMPLOYEE_ID = ?
    ");
    $stmt->execute([$_GET['id']]);
    $employee_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee_data) {
        $employee_name = htmlspecialchars($employee_data['NAME']);
        $page_h1_title = $employee_name . "さんの詳細";
        $page_title_tag = $employee_name . "さんの詳細 - 人事管理表";
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
</head>
<?php include '../header.php'; ?>
<body>
    <h1><?php echo $page_h1_title; ?></h1>

<div class="mb-3 p-3 border rounded">
        <form>
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="display_mode_select" class="col-form-label">表示モード：</label>
                </div>
                <div class="col-auto">
                <select id="display_mode_select" name="edit" class="form-select" onchange="location = this.value;">
                        <option value="main.php">一般画面</option>
                        <option value="editer.php">編集者画面</option>
                </select>
                </div>
            </div>
        </form>
        <div class="text-end mt-2">
            <a href="main.php" class="btn btn-outline-secondary">メインページへ戻る</a>
        </div>
    </div>
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
        <?php

        if ($error_message_for_table) {
            echo "<tr><td colspan=\"8\">" . htmlspecialchars($error_message_for_table) . "</td></tr>";
        } elseif ($employee_data) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($employee_data['EMPLOYEE_ID']) . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['DIVISION_NAME'] ?? '未登録') . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['JOB_POSITION_NAME'] ?? '未登録') . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['EMERGENCY_CELL_NUMBER'] ?? '未入力') . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['JOINING_DATE'] ?? '未入力') . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['POST_CODE'] ?? '未入力') . "</td>";
            echo "<td>" . htmlspecialchars($employee_data['ADDRESS'] ?? '未入力') . "</td>";
            echo "</tr>";
        }
        
        ?>
        </tbody>
    </table>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>