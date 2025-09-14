<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit;
}

// === ▼ 出勤・退勤処理のPHPコードを追加 ▼ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $employee_id = $_SESSION['employee_id'];
    $timestamp = date('Y-m-d H:i:s');
    $status = '';

    if ($action === 'check_in') {
        $status = '出勤';
    } elseif ($action === 'check_out') {
        $status = '退勤';
    } else {
        echo json_encode(['status' => 'error', 'message' => '無効なアクションです。']);
        exit;
    }

    try {
        // データベースに記録する（`config.php`に`$PDO`が定義されている前提）
        $stmt = $PDO->prepare('INSERT INTO ATTENDANCE (EMPLOYEE_ID, TIMESTAMP, STATUS) VALUES (?, ?, ?)');
        $stmt->execute([$employee_id, $timestamp, $status]);
        echo json_encode(['status' => 'success', 'message' => $status . 'を記録しました。']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'データベースエラー: ' . $e->getMessage()]);
    }

    exit;
}
// === ▲ 出勤・退勤処理のPHPコードを追加 ▲ ===

$employee_name = $_SESSION['employee_name'] ?? "ゲスト";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TOPページ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet" />
</head>
<?php include 'header.php'; ?>
<body class="bg-image" id="mainBody">
    <div class="container-main">
        <div class="left-panel">
            <div class="attendance-system">
                <p class="current-date"><?= date('Y年m月d日') ?></p>
                <p class="time-display" id="realtime-time"></p>
                <p class="greeting">こんにちは、<span id="employeeName"><?= $employee_name ?></span>さん</p>
                <div class="button-container">
                    <div class="punch-in-button">
                        <a href="#" id="checkInBtn">出勤</a>
                    </div>
                    <div class="punch-out-button">
                        <a href="#" id="checkOutBtn">退勤</a>
                    </div>
                    </div>
                <div id="statusMessage" class="mt-3 text-center fw-bold fs-5" style="display: none;"></div>
            </div>
            <div class="weather-area">
                <p class="weather-title">今日の札幌市の天気</p>
                <div id="weather-info">
                    <p>天気情報を読み込み中...</p>
                </div>
            </div>
        </div>
        <div class="right-panel">
            <div class="info-area">
                <div style="display: flex; align-items: center;">
                    <i class="bi bi-info-circle info-icon"></i>
                    <p class="info-text">熱中症対策に注意！こまめに水分補給を！</p>
                </div>
            </div>
            <div class="service-menu">
                <a href="/sales/stock.php">
                    <i class="bi bi-truck">販売管理</i>
                </a>
                <a href="/accounting/a_main.php">
                    <i class="bi bi-cash-coin">会計管理</i>
                </a>
                <a href="/human/main.php">
                    <i class="bi bi-people">人事管理</i>
                </a>
                <a href="/customer/customer.php">
                    <i class="bi bi-file-person">顧客管理</i>
                </a>
            </div>
            <div class="room-ava">
                <p class="booth_status_title"><i class="bi bi-hourglass-split"></i>ブース空き状況</p>
                <div class="room-container">
                    <div class="room-card">
                        <div class="room-name">大会議室</div>
                        <div class="status used">
                            利用中
                        </div>
                        <div class="details">
                            <div class="time">15:00-16:00</div>
                            <div class="meeting-type">戦略会議</div>
                        </div>
                    </div>
                    <div class="room-card">
                        <div class="room-name">小会議室 A</div>
                        <div class="status available">
                            空室
                        </div>
                        <div class="details">
                            <div class="time">17:00-18:00</div>
                            <div class="meeting-type">定例MTG</div>
                        </div>
                    </div>
                    <div class="room-card">
                        <div class="room-name">小会議室 B</div>
                        <div class="status available">
                            空室
                        </div>
                        <div class="details">
                            <div class="time">16:00-17:00</div>
                            <div class="meeting-type">打ち合わせ</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="customize-area">
                <p class="customize_title"><i class="bi bi-image"></i>背景をカスタマイズする</p>
                <input type="file" name="test" accept="image/png, image/jpeg" id="backgroundInput">
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script src="weather.js"></script>
    <script src="background_changer.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeString = `${hours}:${minutes}`;
            document.getElementById('realtime-time').textContent = timeString;
        }

        updateTime();
        setInterval(updateTime, 1000);

        // === ▼ 出勤・退勤機能のJavaScriptを追加 ▼ ===
        document.addEventListener('DOMContentLoaded', () => {
            const checkInBtn = document.getElementById('checkInBtn');
            const checkOutBtn = document.getElementById('checkOutBtn');
            const statusMessage = document.getElementById('statusMessage');

            function showStatusMessage(message, type) {
                statusMessage.textContent = message;
                statusMessage.className = `mt-3 alert alert-${type} text-center fw-bold fs-5`;
                statusMessage.style.display = 'block';
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000); // 5秒後に非表示
            }

            async function sendRequest(action) {
                showStatusMessage('通信中...', 'info');

                try {
                    const response = await fetch('main.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=' + action
                    });

                    if (!response.ok) {
                        throw new Error('サーバーエラーが発生しました。');
                    }

                    const result = await response.json();

                    if (result.status === 'success') {
                        showStatusMessage(result.message, 'success');
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    showStatusMessage(`通信エラーが発生しました: ${error.message}`, 'danger');
                }
            }

            checkInBtn.addEventListener('click', (e) => {
                e.preventDefault();
                sendRequest('check_in');
            });

            checkOutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                sendRequest('check_out');
            });

            // 初期状態ではメッセージを非表示にする
            statusMessage.style.display = 'none';
        });
        // === ▲ 出勤・退勤機能のJavaScriptを追加 ▲ ===
    </script>
</body>

</html>