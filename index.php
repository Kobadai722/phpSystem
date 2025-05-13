<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>卒業用サーバ：ログイン</title>
    <link rel="stylesheet" href="index.css" type="text/css">
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
                    <td>ユーザー名:<input type="text" name="user" ></td>
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

</html>
