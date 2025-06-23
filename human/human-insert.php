<?php
session_start(); 
require_once '../config.php';

// フォームがPOSTされた場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $division_id = $_POST['division_id'] ?? null;
    $job_position_id = $_POST['job_position_id'] ?? null;
    $email = $_POST['email'] ?? '';
    $emergency_cell_number = $_POST['emergency_cell_number'] ?? '';
    $joining_date = $_POST['joining_date'] ?? '';
    $post_code = $_POST['post_code'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($name) || empty($division_id) || empty($job_position_id)) {
        $_SESSION['error_message'] = "氏名、所属部署、職位は必須項目です。";
        header('Location: human-insert.php');
        exit;
    }

    try {
        $sql = "INSERT INTO EMPLOYEE (NAME, DIVISION_ID, JOB_POSITION_ID, EMAIL, EMERGENCY_CELL_NUMBER, JOINING_DATE, POST_CODE, ADDRESS)
                VALUES (:name, :division_id, :job_position_id, :email, :emergency_cell_number, :joining_date, :post_code, :address)";
        $stmt = $PDO->prepare($sql);

        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':division_id', $division_id, PDO::PARAM_INT);
        $stmt->bindValue(':job_position_id', $job_position_id, PDO::PARAM_INT);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':emergency_cell_number', $emergency_cell_number, PDO::PARAM_STR);
        $stmt->bindValue(':joining_date', !empty($joining_date) ? $joining_date : null, PDO::PARAM_STR);
        $stmt->bindValue(':post_code', $post_code, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "社員「" . htmlspecialchars($name) . "」さんを登録しました。";
            header('Location: editer.php'); 
            exit;
        } else {
            $_SESSION['error_message'] = "社員情報の登録に失敗しました。";
        }
    } catch (PDOException $e) {
        error_log("Employee insertion error: " . $e->getMessage());
        $_SESSION['error_message'] = "データベースエラーにより登録に失敗しました。管理者に連絡してください。";
    }

    header('Location: human-insert.php');
    exit;
}

$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>社員情報登録 - 人事管理表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include '../header.php'; ?>
<body>
    <div class="container py-4">
        <h1>社員情報登録</h1>

        <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

        <form action="human-insert.php" method="post" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-md-6"><label for="name" class="form-label">氏名 <span class="text-danger">*</span></label><input type="text" class="form-control" id="name" name="name" required></div>
                <div class="col-md-6"><label for="email" class="form-label">メールアドレス</label><input type="email" class="form-control" id="email" name="email"></div>
                <div class="col-md-6">
                    <label for="division_id" class="form-label">所属部署 <span class="text-danger">*</span></label>
                    <select class="form-select" id="division_id" name="division_id" required>
                        <option value="" selected disabled>選択してください...</option>
                        <option value="1">営業部</option>
                        <option value="2">開発部</option>
                        <option value="3">人事部</option>
                        <option value="4">総務部</option>
                        <option value="5">経理部</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="job_position_id" class="form-label">職位 <span class="text-danger">*</span></label>
                    <select class="form-select" id="job_position_id" name="job_position_id" required>
                        <option value="" selected disabled>選択してください...</option>
                        <option value="1">部長</option>
                        <option value="2">課長</option>
                        <option value="3">係長</option>
                        <option value="4">主任</option>
                        <option value="5">一般社員</option>
                    </select>
                </div>
                <div class="col-md-6"><label for="joining_date" class="form-label">入社日</label><input type="date" class="form-control" id="joining_date" name="joining_date"></div>
                <div class="col-md-6"><label for="emergency_cell_number" class="form-label">緊急連絡先</label><input type="tel" class="form-control" id="emergency_cell_number" name="emergency_cell_number"></div>
                <div class="col-md-6"><label for="post_code" class="form-label">郵便番号</label><input type="text" class="form-control" id="post_code" name="post_code" placeholder="例: 123-4567"></div>
                <div class="col-12"><label for="address" class="form-label">住所</label><input type="text" class="form-control" id="address" name="address" placeholder="例: 東京都千代田区..."></div>
            </div>
            <hr class="my-4">
            <button class="btn btn-primary" type="submit">登録する</button>
            <a href="editer.php" class="btn btn-secondary">キャンセル</a>
        </form>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>
