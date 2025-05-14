<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>卒業用サーバ：ログイン</title>
    <link rel="stylesheet" href="index.css" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body class="text-center vsc-initialized" cz-shortcut-listen="true">
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

    <div>
        <div class="row w-100">
            <div class="col-md-6 mx-auto">
                <form class="form-signin text-center" action="login.php" method="post">
                    <img class="mb-4 mx-auto d-block" src="/images/logo-type2.png" alt="" width="300" height="300">
                    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
                    <label for="inputCode" class="sr-only">社員コード</label>
                    <input type="email" id="inputCode" class="form-control mb-2" placeholder="Employee Code" required="" autofocus="" name="user">
                    <label for="inputPassword" class="sr-only">パスワード</label>
                    <input type="password" id="inputPassword" class="form-control mb-3" placeholder="Password" required="" name="pass">
                    <button class="btn btn-lg btn-primary btn-block mb-3" type="submit">Sign in</button>
                    <p class="mt-5 mb-3 text-muted">©2025</p>
                </form>
            </div>
        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
