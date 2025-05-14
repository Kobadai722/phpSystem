<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>サインインページ</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT">
  //CSSの設定など
  <!-- CSSの設定ファイル -->
  <link rel="stylesheet" href="style.css">
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
  <main class="form-signin w-100 m-auto">
    <form class="text-center">
      <img class="mb-4" src="../images/bootstrap-logo.svg" alt="" width="72" height="57" loading="lazy">
      <h1 class="h3 mb-3 fw-normal">サインインして下さい</h1>
      
      <div class="form-floating">
        <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
        <label for="floatingInput">Emailアドレス</label>
      </div>
      <div class="form-floating">
        <input type="password" class="form-control" id="floatingPassword" placeholder="パスワード">
        <label for="floatingPassword">パスワード</label>
      </div>

      <div class="form-check text-start my-3">
        <input class="form-check-input" type="checkbox" value="remember-me" id="checkDefault">
        <label class="form-check-label" for="checkDefault">
          状態を記憶する
        </label>
      </div>
      <button class="btn btn-primary w-100 py-2" type="submit">サインイン</button>
      <p class="mt-5 mb-3 text-body-secondary">&copy; 2017-2025</p>
   </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  //JavaScriptプラグインの設定など
</body>

</html>