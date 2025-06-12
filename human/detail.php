<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>人事管理表 - 詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../header.php'; ?>
<body>
    <h1>人事詳細</h1>

<!-- 編集者ページの切り替え -->
<div class="mb-3 p-3 border rounded">
        <form>
            <div class="row g-3 aligh-items-center">
                <div class="col-auto">
                    <label for="display_mode_select" class="col-form-label">表示モード：</label>
                </div>
                <div class="col-auto">
                <select id="display_mode_select" name="edit" class="form-select" onchange="location = this.value;">
                        <option value="main.php">一般画面</option>
                        <option value="editer.php" selected>編集者画面</option>
                </select>
                </div>
            </div>
        </form>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>社員番号</th>
                <th>氏名</th>
                <th>所属部署</th>
                <th>職位</th>
                <th>メールアドレス</th>
                <th>緊急連絡先</th>
                <th>入社日</th>
                <th>郵便番号</th>
                <th>住所</th>
            </tr>
        </thead>
        <tbody>
        <?php
        require_once '../config.php';

        // GETパラメータ確認
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo "<tr><td colspan=\"9\"不正なリクエストです。</td></tr>";
        } else {
            $stmt = $PDO->prepare("
                SELECT e.*, d.DIVISION_NAME, j.JOB_POSITION_NAME
                FROM EMPLOYEE e
                LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
                LEFT JOIN JOB_POSITION j ON e.JOB_POSITION_ID = j.JOB_POSITION_ID
                WHERE e.EMPLOYEE_ID = ?
            ");
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['EMPLOYEE_ID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['DIVISION_NAME'] ?? '未登録') . "</td>";
                echo "<td>" . htmlspecialchars($row['JOB_POSITION_NAME'] ?? '未登録') . "</td>";
                echo "<td>" . htmlspecialchars($row['EMAIL'] ?? '未入力') . "</td>";
                echo "<td>" . htmlspecialchars($row['EMERGENCY_CELL_NUMBER'] ?? '未入力') . "</td>";
                echo "<td>" . htmlspecialchars($row['JOINING_DATE'] ?? '未入力') . "</td>";
                echo "<td>" . htmlspecialchars($row['POST_CODE'] ?? '未入力') . "</td>";
                echo "<td>" . htmlspecialchars($row['ADDRESS'] ?? '未入力') . "</td>";
                echo "</tr>";
            } else {
                echo "<tr><td colspan=\"9\">該当社員が見つかりません。</td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>