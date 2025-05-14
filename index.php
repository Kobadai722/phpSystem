<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>卒業用サーバ：ログイン</title>
    <link rel="stylesheet" href="index.css" type="text/css">
    <link rel="canonical" href="https://getbootstrap.jp/docs/5.3/examples/sign-in/">
    <link rel="stylesheet" href="./Signin Template for Bootstrap_files/bootstrap.min.css">
    <link href="./Signin Template for Bootstrap_files/signin.css" rel="stylesheet">
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

    <div class="container d-flex justify-content-center align-items-center vh-100">
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

    <a id="skippy" class="sr-only sr-only-focusable" href="https://getbootstrap.jp/docs/4.3/examples/sign-in/#content">
        <div class="container">
            <span class="skiplink-text">Skip to main content</span>
        </div>
    </a>
    <form class="form-signin">
        <img class="mb-4" src="/images/logo-type2.png" alt="" width="300" height="300">
        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required="" autofocus="">
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required="">
        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me"> Remember me
            </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
    </form>
    <script src="./Signin Template for Bootstrap_files/jquery-3.3.1.slim.min.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>
        window.jQuery || document.write('<script src="/docs/4.3/assets/js/vendor/jquery-slim.min.js"><\/script>')
    </script>
    <script src="./Signin Template for Bootstrap_files/bootstrap.bundle.min.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/anchor.min.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/clipboard.min.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/bs-custom-file-input.min.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/application.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/search.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <script src="./Signin Template for Bootstrap_files/ie-emulation-modes-warning.js.&#12480;&#12454;&#12531;&#12525;&#12540;&#12489;"></script>
    <div style="z-index: 2147483647 !important; padding: 0px !important; margin: 0px !important; border-radius: 8px !important; position: fixed !important; color-scheme: normal !important; 
    box-shadow: rgba(0, 0, 0, 0.15) 0px 2px 4px 0px, rgba(0, 0, 0, 0.18) 0px 8px 15px 6px !important; border: 1px solid rgba(0, 0, 0, 0.2) !important; overflow: hidden !important; backdrop-filter: blur(20px) !important; background-color: rgba(230, 230, 230, 0.8) !important; 
    top: 433px !important; left: -999999px !important; opacity: 0 !important; visibility: hidden !important;"><template shadowrootmode="open"><iframe src="chrome-extension://pejdijmoenmkgeppbflobdenhhabjlaj/completion_list.html?username=&amp;colorScheme=undefined&amp;screenX=0&amp;screenY=0&amp;effectiveWindowWidth=1924.6499999999999" frameborder="0" style="width: 9001px !important; height: 100% !important; display: block !important;"></iframe></template></div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
