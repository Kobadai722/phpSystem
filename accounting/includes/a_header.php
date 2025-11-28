<!DOCTYPE html>
<html lang="ja">
<head>
    <?php session_start();?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">

    <title>
        <?php
        // $page_titleにタイトルを入れる
        if (isset($page_title)) {
            echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');
        } else {
            echo '私のウェブサイト';
        }
        ?>
    </title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="accounting/css/siwake.css">
    <link rel="stylesheet" href="accounting/css/sidebar.css">
    

</head>
<body>
    <?php
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../header.php';
    ?>


