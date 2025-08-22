<?php
session_start();
require_once 'config.php';
// 変更箇所：出勤・退勤機能のためにattendance_api.phpをインクルードします
require_once 'human/api/attendance_api.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = "ゲスト";
$attendance_record = null;

try {
    // 変更箇所：ログインしている従業員名と当日の出勤データを取得します
    $stmt = $PDO->prepare("SELECT NAME FROM EMPLOYEE WHERE EMPLOYEE_ID = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($employee) {
        $employee_name = htmlspecialchars($employee['NAME']);
    }

    $today = date("Y-m-d");
    $stmt = $PDO->prepare("SELECT * FROM ATTENDANCE WHERE EMPLOYEE_ID = ? AND ATTENDANCE_DATE = ?");
    $stmt->execute([$employee_id, $today]);
    $attendance_record = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // エラーハンドリング
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メインページ</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main>
        <div class="user-info">
            <h1>ようこそ！<br><?= $employee_name ?>さん</h1>
        </div>
        <div class="main-content">
            <div class="top-row">
                <div class="card weather-card">
                    <div class="weather-info">
                        <h2>今日の天気</h2>
                        <img id="weather-icon" src="" alt="Weather Icon">
                        <p id="weather-description"></p>
                    </div>
                </div>
                <div class="card time-card">
                    <h2>現在時刻</h2>
                    <p id="current-time"></p>
                </div>
                <div class="card punch-card">
                    <h2>打刻</h2>
                    <div class="punch-buttons">
                        <div class="punch-in-button">
                            <a href="#" id="mainClockInBtn">出勤</a>
                        </div>
                        <div class="punch-out-button">
                            <a href="#" id="mainClockOutBtn">退勤</a>
                        </div>
                    </div>
                    <div id="statusMessage" class="mt-3"></div>
                </div>
            </div>
            <div class="bottom-row">
                <div class="card notice-card">
                    <h2>お知らせ</h2>
                    <ul>
                        <li><a href="#">新しいプロジェクトが開始されました。</a></li>
                        <li><a href="#">健康診断のご案内。</a></li>
                        <li><a href="#">サーバーメンテナンスのお知らせ。</a></li>
                    </ul>
                </div>
                <div class="card attendance-card">
                    <h2>勤怠表</h2>
                    <div class="attendance-status">
                        <p>今月の勤務時間: <span>160</span>時間</p>
                        <p>残り有給日数: <span>5</span>日</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 システム管理</p>
    </footer>
    <script src="weather.js"></script>
    <script src="background_changer.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="human/main-attendance.js"></script>
</body>
</html>