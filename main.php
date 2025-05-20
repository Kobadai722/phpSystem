<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TOPページ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="style.css" rel="stylesheet" />
</head>

<?php include 'header.php'; ?>
<body>
    <h2>ようこそ
        <?php
                session_start();
                echo $_SESSION['dname'];
            ?> さん</h2>
    <p><a href="./../accounting/siwake_hyo/siwakehyo_output.html">仕訳機能プロトタイプ</a></p>
    <p><a href="./../accounting/siwake_hyo/input_siwakehyo.php">仕訳機能Demo</a></p>
    <p><a href="./../customer/customer.php">顧客一覧</a></p>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
</script>
</html>
