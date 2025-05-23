<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">

  <title>仕訳登録完了</title>
</head>
<body>
<h3>以下の内容で仕訳が登録されました</h3>

  <?php
  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  //仕訳ヘッダーの登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
  $sql->execute([$_POST['entry_date'], $_POST['description']]);

  //仕訳明細の登録
  //ヘッダーIDの取得
  $sql = $PDO->prepare('SELECT ID FROM JOURNAL_HEADERS WHERE ENTRY_DATE = ? AND DESCRIPTION = ?');
  $sql->execute([$_POST['entry_date'], $_POST['description']]);
  $header_id = $PDO->lastInsertId();

  //借方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $_POST['debit_account'], $_POST['debit_amount'], '借方']);

  //貸方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $_POST['credit_account'], $_POST['credit_amount'], '貸方']);
  ?>

  <p><a href="input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="output_siwakehyo.php">仕訳一覧表示</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>
</body>
</html>
