<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>卒業用サーバ：ログイン</title>
    <link rel="stylesheet" href="index.css" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <div class="center">
        <form action="login.php" method="post">
            <table border="0" id="login">
                <tr>
                    <td colspan="2">
                        <h1>卒論用サーバー：ログイン</h1>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">ユーザー名とパスワードを入力してください</td>
                </tr>
                <tr>
                    <td>社員コード:<input type="text" name="user" placeholder="Employee Code"></td>
                </tr>
                <tr>
                    <td>パスワード:<input type="password" name="pass"></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="submit" value="ログイン">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
