<?php
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
error_reporting(E_ALL);

session_start();
?>
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
    /* デバッグ情報の表示
    if (isset($_SESSION['debug_messages']) && !empty($_SESSION['debug_messages'])) {
        echo '<div class="alert alert-info alert-dismissible fade show m-3" role="alert"><strong>デバッグ情報:</strong><pre>';
        foreach ($_SESSION['debug_messages'] as $msg) {
            echo htmlspecialchars($msg) . "\n";
        }
        echo '</pre><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['debug_messages']); /
    }
    */
    ?>
    <h1>人事管理表-編集者モード</h1>
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
            <div class="mt-2">
                <a href="human-insert.php" class="btn btn-success">社員情報を登録する</a>
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
            </div>
        </form>
    </div>

    </body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="human.js"></script>
<script src="live_search.js"></script> </html>