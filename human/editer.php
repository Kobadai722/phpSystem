<?php
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
error_reporting(E_ALL);

session_start();
require_once '../config.php';
$stmt_pending = $PDO->query("SELECT COUNT(*) FROM APPLICATIONS WHERE STATUS = 'pending'");
$pending_count = $stmt_pending->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>人事管理ダッシュボード</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="human.css">
</head>

<body>
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
    <h1 class="mb-4"><i class="bi bi-people-fill me-2"></i> 人事管理ダッシュボード</h1>
    <?php
    require_once '../config.php';
    $stmt_divisions = $PDO->query("SELECT DIVISION_ID, DIVISION_NAME FROM DIVISION ORDER BY DIVISION_ID");
    $divisions = $stmt_divisions->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <div>
        <form id="searchForm" class="mb-3 p-3 border rounded">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="name_keyword" class="col-form-label">氏名：</label>
                </div>
                <div class="col-auto">
                    <div class="position-relative">
                        <input type="text" id="name_keyword" name="name_keyword" class="form-control pe-4" value="<?= htmlspecialchars($_GET['name_keyword'] ?? '', ENT_QUOTES) ?>">
                        <span onclick="clearInputField(this)" class="clear-input-btn position-absolute top-50 end-0 translate-middle-y me-2" style="cursor: pointer;" title="クリア">
                            <i class="fas fa-times-circle"></i>
                        </span>
                    </div>
                </div>

                <div class="col-auto">
                    <label for="id_keyword" class="col-form-label">従業員番号：</label>
                </div>
                <div class="col-auto">
                    <div class="position-relative">
                        <input type="text" id="id_keyword" name="id_keyword" class="form-control pe-4" value="<?= htmlspecialchars($_GET['id_keyword'] ?? '', ENT_QUOTES) ?>">
                        <span onclick="clearInputField(this)" class="clear-input-btn position-absolute top-50 end-0 translate-middle-y me-2" style="cursor: pointer;" title="クリア">
                            <i class="fas fa-times-circle"></i>
                        </span>
                    </div>
                </div>

                <div class="col-auto">
                    <label for="division_id" class="col-form-label">所属部署：</label>
                </div>
                <div class="col-auto">
                    <select id="division_id" name="division_id" class="form-select">
                        <option value="">全ての部署</option>
                        <?php foreach ($divisions as $division) : ?>
                            <option value="<?= htmlspecialchars($division['DIVISION_ID']) ?>" <?= (($_GET['division_id'] ?? '') == $division['DIVISION_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($division['DIVISION_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        
        <form>
            <div class="mt-2 d-flex gap-2">
                <a href="human-insert.php" class="btn btn-success">
                    <i class="bi bi-person-plus-fill me-1"></i> 社員情報を登録する
                </a>
                <a href="application_list.php" class="btn btn-primary position-relative">
                    <i class="bi bi-card-checklist me-1"></i> 申請一覧を確認する
                    <?php if ($pending_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $pending_count ?>
                            <span class="visually-hidden">未承認の申請</span>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="managementMenuButton" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
                        <i class="bi bi-gear-fill me-1"></i> 管理メニュー
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="managementMenuButton">
                        <li>
                            <a class="dropdown-item" href="payroll_csv.php">
                                <i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i>給与データ出力
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="paid_leave_register.php">
                                <i class="bi bi-gift-fill me-2 text-warning"></i>有給付与
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="salary_management.php">
                                <i class="bi bi-cash-coin me-2 text-dark"></i>給与設定管理
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="master_division.php">
                                <i class="bi bi-building me-2 text-primary"></i>部署マスタ管理
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="master_job_position.php">
                                <i class="bi bi-person-badge me-2 text-info"></i>役職マスタ管理
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">社員番号</th>
                <th scope="col">氏名</th>
                <th scope="col">所属部署</th>
                <th scope="col">職位</th>
            </tr>
        </thead>
        <tbody id="employeeTableBody">
            </tbody>
        </table>

    <hr>
    
    <div class="container mt-5">
        <h3>全従業員の勤怠状況</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>勤怠ID</th>
                        <th>従業員番号</th>
                        <th>氏名</th>
                        <th>日付</th>
                        <th>出勤時刻</th>
                        <th>退勤時刻</th>
                        <th>ステータス</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="allAttendanceTableBody">
                    <tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mb-3 p-3 border rounded">
        <form>
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="display_mode_select" class="col-form-label">表示モード：</label>
                </div>
                <div class="col-auto">
                <select id="display_mode_select" name="edit" class="form-select" onchange="location = this.value;">
                        <option value="main.php">一般画面</option>
                        <option value="editer.php" selected>編集者画面</option>
                </select>
                </div>
                <div class="text-end mt-2">
                    <a href="main.php" class="btn btn-outline-secondary">メインページへ戻る</a>
                </div>
            </div>
        </form>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="human.js"></script>
<script src="live_search.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const allAttendanceTableBody = document.getElementById('allAttendanceTableBody');

        async function fetchAllAttendance() {
            try {
                const response = await fetch('attendance_history_all.php');
                if (!response.ok) {
                    throw new Error('ネットワークエラー');
                }
                const data = await response.json();
                
                allAttendanceTableBody.innerHTML = '';

                if (data.success && data.history.length > 0) {
                    data.history.forEach(record => {
                        const row = document.createElement('tr');
                        // 修正: 勤怠IDとステータスを表示するように変更
                        // ステータスが空の場合は自動判定して表示するロジックも追加可能です
                        let displayStatus = record.status;
                        if (!displayStatus) {
                            if (record.clock_in_time && !record.clock_out_time) {
                                displayStatus = '勤務中';
                            } else if (record.clock_in_time && record.clock_out_time) {
                                displayStatus = '退勤済';
                            } else {
                                displayStatus = '-';
                            }
                        }

                        row.innerHTML = `
                            <td>${record.attendance_id || '-'}</td>
                            <td>${record.employee_id}</td>
                            <td>${record.employee_name}</td>
                            <td>${record.date || '-'}</td>
                            <td>${record.clock_in_time || '未記録'}</td>
                            <td>${record.clock_out_time || '未記録'}</td>
                            <td>${displayStatus}</td>
                            <td>
                                ${record.attendance_id ? 
                                    `<a href="attendance_edit.php?id=${record.attendance_id}" class="btn btn-sm btn-primary">編集</a>` : 
                                    '-'
                                }
                            </td>
                        `;
                        allAttendanceTableBody.appendChild(row);
                    });
                } else {
                    // 列数(colspan)を合わせる
                    allAttendanceTableBody.innerHTML = `<tr><td colspan="8" class="text-center">勤怠記録がありません。</td></tr>`;
                }
            } catch (error) {
                console.error('エラー:', error);
                allAttendanceTableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">データの取得に失敗しました。</td></tr>`;
            }
        }

        fetchAllAttendance();
    });
</script>
</html>