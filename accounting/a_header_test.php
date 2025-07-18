<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- サイトの基準URLを定義 -->
    <base href="/PHPSYSTEM/">

    <title>
        <?php
        if (isset($page_title)) {
            echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' | 私のウェブサイト';
        } else {
            echo '私のウェブサイト';
        }
        ?>
    </title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha344-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- 独自のCSSのパスを<base>タグからの相対パスに修正 -->
    <link rel="stylesheet" href="accounting/css/siwake.css">
    <link rel="stylesheet" href="accounting/css/sidebar.css">
    <link rel="stylesheet" href="accounting/css/a_main.css">

    <!-- ▲▲▲ ここまで修正 ▲▲▲ -->
</head>
<body>
    <?php
  require_once '../../config.php';
  // セッション開始
  session_start();
  require_once '../../header.php'; // ヘッダーの読み込み
    ?>