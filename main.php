<?php
session_start();
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
<body class="bg-image">
    <div class="container-main">
        <div class="row">
            <div class="col-4 attendance-system">
                <p class="current-date"><?php echo date('Y年m月d日'); ?></p>
                <p class="time-display" id="realtime-time"></p>
                <p class="greeting">こんにちは<?php echo $_SESSION['dname']; ?>さん</p>
                <div class="button-container">
                    <div class="punch-in-button">
                        <a href="">出勤</a>
                    </div>
                    <div class="punch-out-button">
                        <a href="">退勤</a>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="info-area">
                    <div style="display: flex; align-items: center;">
                        <i class="bi bi-info-circle info-icon"></i>
                        <p class="info-text">熱中症対策に注意！こまめに水分補給を！</p>
                    </div>
                </div>
                <div class="service-menu">
                    <a href="/sales/views/stock.php">
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
                    <!--
                    <a href="kimu.html">
                        <img class="rocket-icon" src="/images/rocket.png"><i>人民管理</i>
                    </a>
                    -->
                </div>
                <div class="room-ava">
                    <i class="bi bi-house-gear">ブース空き状況</i>
                    <i class="bi bi-circle"></i>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4 attendance-system">
                <p class="weather-title">今日の札幌市の天気</p>
                <div id="weather-info">
                    <p>天気情報を読み込み中...</p>
                </div>
                
            </div>
        </div>
    </div>
    
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</script>
<script src="weather.js"></script>
<script>
    // リアルタイムで時刻を更新するJavaScript
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = `${hours}:${minutes}`;
        document.getElementById('realtime-time').textContent = timeString;
    }

    // ページロード時に一度時刻を設定
    updateTime();

    // 1秒ごとに時刻を更新
    setInterval(updateTime, 1000);
</script>
</body>
</html>