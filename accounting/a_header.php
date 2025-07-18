<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
        <?php
        // $page_titleにタイトルを入れる。nullの場合 ”私のウェブサイト” を表示
        if (isset($page_title)) {
            echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' | 私のウェブサイト';
        } else {
            echo '私のウェブサイト';
        }
        ?>
    </title>
  <!-- Bootstrap (主にテーブルなどのコンポーネント用) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- 独自のレイアウトCSS -->
  <link rel="stylesheet" href="../css/siwake.css">
  <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>