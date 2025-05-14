<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>サインインページ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css" type="text/css">
  </head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
  <main class="form-signin w-100 m-auto">
    <form class="text-center" action="login.php" method="post">
      <img class="mb-4" src="/images/logo-type2.png" alt="" width="300" height="200" loading="lazy">
      <h1 class="h3 mb-3 fw-normal">サインインして下さい</h1>
      
      <div class="form-floating">
        <input type="text" class="form-control" id="floatingInput" placeholder="250000" name="user">
        <label for="floatingInput">社員コード</label>
      </div>
      <div class="form-floating">
        <input type="password" class="form-control" id="floatingPassword" placeholder="パスワード" name="pass">
        <label for="floatingPassword">パスワード</label>
      </div>
      <button class="btn btn-primary w-100 py-2" type="submit">サインイン</button>
      <p class="mt-5 mb-3 text-body-secondary">&copy; 2025</p>
   </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
  </body>

</html>