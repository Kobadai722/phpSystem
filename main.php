<?php
session_start();

// リクエストがPOSTで、かつアクションが指定されているかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $employee_id = $_SESSION['id'];
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

    // データベースに記録する代わりに、ダミーの成功レスポンスを返す
    // 実際のデータベース接続コードは必要に応じてここに追加
    echo json_encode(['status' => 'success', 'message' => $status . 'が記録されました。']);

    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TOPページ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        .attendance-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: 50px;
        }

        .attendance-btn {
            width: 200px;
            margin: 10px;
        }

        #status-message {
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <h2>ようこそ
        <?php
        echo $_SESSION['dname'];
        ?> さん
    </h2>

    <div class="attendance-container">
        <h3>出勤・退勤</h3>
        <button id="checkInBtn" class="btn btn-primary attendance-btn">出勤</button>
        <button id="checkOutBtn" class="btn btn-danger attendance-btn">退勤</button>
        <div id="status-message" class="text-danger">通信エラーが発生しました</div>
    </div>
    
    <h1><a href="./../accounting/siwake_hyo/siwakehyo_output.html">仕訳機能プロトタイプ</a></h1>
    <a href="./../sales/stock.php">在庫管理システムDemo</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkInBtn = document.getElementById('checkInBtn');
            const checkOutBtn = document.getElementById('checkOutBtn');
            const statusMessage = document.getElementById('status-message');

            statusMessage.style.display = 'none';

            async function sendRequest(action) {
                statusMessage.style.display = 'block';
                statusMessage.textContent = '通信中...';
                statusMessage.className = 'text-info';

                try {
                    const response = await fetch('main.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=' + action
                    });

                    if (!response.ok) {
                        throw new Error('サーバーエラー');
                    }

                    const result = await response.json();

                    if (result.status === 'success') {
                        statusMessage.textContent = result.message;
                        statusMessage.className = 'text-success';
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    statusMessage.textContent = '通信エラーが発生しました: ' + error.message;
                    statusMessage.className = 'text-danger';
                }
            }

            checkInBtn.addEventListener('click', () => {
                sendRequest('check_in');
            });

            checkOutBtn.addEventListener('click', () => {
                sendRequest('check_out');
            });
        });
    </script>
</body>

</html>