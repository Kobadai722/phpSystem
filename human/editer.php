<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>人事管理表</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    </head>
    <?php include '../header.php'; ?>
    <body>
        <h1>人事管理表</h1>
<!-- 所属社員の検索欄 -->
        <form>
        <p>氏名：  <input type="text"  name="keyword">
                <input type="submit" value="検索"></p>
        <p>従業員番号： <input type="text"  name="keyword">
                    <input type="submit" value="検索"></p>
        </form>

<!-- 編集者ページの切り替え 後々CSSで右寄せ予定 -->
    <select name="edit">
        <option value="edit"><a href="editer.php">編集者画面に切り替える</a></option>
        <option value="nomal"><a href="main.php">一般画面に切り替える</a></option>
    </select>


    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
