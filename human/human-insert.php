<?php
session_start(); 
require_once '../config.php';

// フォームがPOSTされた場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NAME = $_POST['NAME'] ?? '';
    $DIVISION_ID = $_POST['DIVISION_ID'] ?? null;
    $JOB_POSITION_ID = $_POST['JOB_POSITION_ID'] ?? null;
    $EMAIL = $_POST['EMAIL'] ?? '';
    $EMERGENCY_CELL_NUMBER = $_POST['EMERGENCY_CELL_NUMBER'] ?? '';
    $JOINING_DATE = $_POST['JOINING_DATE'] ?? '';
    $POST_CODE = $_POST['POST_CODE'] ?? '';
    $ADDRESS = $_POST['ADDRESS'] ?? '';

    if (empty($NAME) || empty($DIVISION_ID) || empty($JOB_POSITION_ID)) {
        $_SESSION['error_message'] = "氏名、所属部署、職位は必須項目です。";
        header('Location: human-insert.php');
        exit;
    }

    try {
        $sql = "INSERT INTO EMPLOYEE (NAME, DIVISION_ID, JOB_POSITION_ID, EMAIL, EMERGENCY_CELL_NUMBER, JOINING_DATE, POST_CODE, ADDRESS)
                VALUES (:NAME, :DIVISION_ID, :JOB_POSITION_ID, :EMAIL, :EMERGENCY_CELL_NUMBER, :JOINING_DATE, :POST_CODE, :ADDRESS)";
        $stmt = $PDO->prepare($sql);

        $stmt->bindValue(':NAME', $NAME, PDO::PARAM_STR);
        $stmt->bindValue(':DIVISION_ID', $DIVISION_ID, PDO::PARAM_INT);
        $stmt->bindValue(':JOB_POSITION_ID', $JOB_POSITION_ID, PDO::PARAM_INT);
        $stmt->bindValue(':EMAIL', $EMAIL, PDO::PARAM_STR);
        $stmt->bindValue(':EMERGENCY_CELL_NUMBER', $EMERGENCY_CELL_NUMBER, PDO::PARAM_STR);
        $stmt->bindValue(':JOINING_DATE', !empty($JOINING_DATE) ? $JOINING_DATE : null, PDO::PARAM_STR);
        $stmt->bindValue(':POST_CODE', $POST_CODE, PDO::PARAM_STR);
        $stmt->bindValue(':ADDRESS', $ADDRESS, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "社員「" . htmlspecialchars($NAME) . "」さんを登録しました。";
            header('Location: editer.php'); 
            exit;
        } else {
            $_SESSION['error_message'] = "社員情報の登録に失敗しました。";
        }
    } catch (PDOException $e) {
        // error_log("Employee insertion error: " . $e->getMessage());
        //$_SESSION['error_message'] = "データベースエラーにより登録に失敗しました。管理者に連絡してください。";
        // デバッグ用に、エラーメッセージを画面に直接表示する
        error_log("Employee insertion error: " . $e->getMessage());
$_SESSION['error_message'] = "データベースエラーにより登録に失敗しました。管理者に連絡してください。: " . $e->getMessage(); // エラーメッセージを追加
header('Location: human-insert.php'); // エラー時に元のフォームに戻る
exit;
    }

    header('Location: human-insert.php');
    exit;
}

// 部署リストを取得
$stmt_divisions = $PDO->query("SELECT DIVISION_ID, DIVISION_NAME FROM DIVISION ORDER BY DIVISION_ID");
$divisions = $stmt_divisions->fetchAll(PDO::FETCH_ASSOC);

// 職位リストを取得
$stmt_jobs = $PDO->query("SELECT JOB_POSITION_ID, JOB_POSITION_NAME FROM JOB_POSITION ORDER BY JOB_POSITION_ID");
$job_positions = $stmt_jobs->fetchAll(PDO::FETCH_ASSOC);

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
                <div class="col-md-6"><label for="NAME" class="form-label">氏名 <span class="text-danger">*</span></label><input type="text" class="form-control" id="NAME" name="NAME" required></div>
                <div class="col-md-6"><label for="EMAIL" class="form-label">メールアドレス</label><input type="email" class="form-control" id="EMAIL" name="EMAIL"></div>
                <div class="col-md-6">
                    <label for="DIVISION_ID" class="form-label">所属部署 <span class="text-danger">*</span></label>
                    <select class="form-select" id="DIVISION_ID" name="DIVISION_ID" required>
                        <option value="" selected disabled>選択してください...</option>
                        <?php foreach ($divisions as $division): ?>
                            <option value="<?= htmlspecialchars($division['DIVISION_ID']) ?>"><?= htmlspecialchars($division['DIVISION_NAME']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="JOB_POSITION_ID" class="form-label">職位 <span class="text-danger">*</span></label>
                    <select class="form-select" id="JOB_POSITION_ID" name="JOB_POSITION_ID" required>
                        <option value="" selected disabled>選択してください...</option>
                        <?php foreach ($job_positions as $job): ?>
                            <option value="<?= htmlspecialchars($job['JOB_POSITION_ID']) ?>"><?= htmlspecialchars($job['JOB_POSITION_NAME']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label for="JOINING_DATE" class="form-label">入社日</label><input type="date" class="form-control" id="JOINING_DATE" name="JOINING_DATE"></div>
                <div class="col-md-6"><label for="EMERGENCY_CELL_NUMBER" class="form-label">緊急連絡先</label><input type="tel" class="form-control" id="EMERGENCY_CELL_NUMBER" name="EMERGENCY_CELL_NUMBER"></div>
                <div class="col-md-6"><label for="POST_CODE" class="form-label">郵便番号</label><input type="text" class="form-control" id="POST_CODE" name="POST_CODE" placeholder="例: 123-4567"></div>
                <div class="col-12"><label for="ADDRESS" class="form-label">住所</label><input type="text" class="form-control" id="ADDRESS" name="ADDRESS" placeholder="例: 東京都千代田区..."></div>
            </div>
            <hr class="my-4">
            <button class="btn btn-primary" type="submit">登録する</button>
            <a href="editer.php" class="btn btn-secondary">キャンセル</a>
        </form>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>
