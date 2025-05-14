<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="index.css" type="text/css">
</head>

<body>
    <div class="header">

        <ul>
            <li>
                <a href="./logout.php">ログアウト</a>
            </li>
        </ul>
    </div>

    <h2>ようこそ
        <?php
                session_start();
                echo $_SESSION['dname'];
            ?> さん</h2>
    <p> <a href="./../accounting/siwake_hyo/siwakehyo_output.html">仕訳機能プロトタイプ</p>