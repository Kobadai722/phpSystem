<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>人事管理表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
    <h1><i class="bi bi-people-fill me-1 ms-3"></i> 人事管理表</h1>
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

                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#applicationModal">
                        <i class="fas fa-paper-plane me-1"></i> 各種申請
                    </button>
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
                <th scope="col">入社日</th>
                <th scope="col">緊急連絡先</th>
            </tr>
        </thead>
        <tbody id="employeeTableBody">
        </tbody>
    </table>

    <div class="mb-3 p-3 border rounded">
        <form>
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="display_mode_select" class="col-form-label">表示モード：</label>
                </div>
                <div class="col-auto">
                    <select id="display_mode_select" name="edit" class="form-select" onchange="location = this.value;">
                        <option value="main.php" selected>一般画面</option>
                        <option value="editer.php">編集者画面</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title" id="applicationModalLabel"><i class="fas fa-file-pen"></i> 各種申請フォーム</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="application_process.php" method="post">
                        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($_SESSION['employee_id'] ?? '') ?>">

                        <div class="mb-3">
                            <label for="applicationType" class="form-label fw-bold">申請種別 <span class="text-danger">*</span></label>
                            <select class="form-select" id="applicationType" name="type" required>
                                <option value="" selected disabled>選択してください</option>
                                <option value="paid_leave">有給休暇</option>
                                <option value="attendance_correction">打刻修正</option>
                                <option value="overtime">残業申請</option>
                                <option value="other">その他</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="targetDate" class="form-label fw-bold">対象日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="targetDate" name="target_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label fw-bold">申請理由・備考</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="例：私用のため、打刻忘れのため 等" required></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> 申請する</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="human.js"></script>
    <script src="live_search.js"></script>
</body>

</html>