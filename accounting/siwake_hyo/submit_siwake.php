<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">

  <title>仕訳登録完了</title>
</head>
<body>
<h3>以下の内容で仕訳が登録されました</h3>
  <?php
    // 入力チェック
    if (
      empty($_POST['entry_date']) ||
      empty($_POST['description']) ||
      empty($_POST['debit_account']) ||
      empty($_POST['debit_amount']) ||
      empty($_POST['credit_account']) ||
      empty($_POST['credit_amount'])
  ) {
      echo 'すべての項目を入力してください。<a href="../siwake_hyo/input_siwakehyo.php">戻る</a>';
      exit;
  }

  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  //仕訳ヘッダーの登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
  $sql->execute([$_POST['entry_date'], $_POST['description']]);
  //仕訳明細の登録
  // 勘定科目のIDを取得するためのSQL
  $header_id = $PDO->lastInsertId();
  //借方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $_POST['debit_account'], $_POST['debit_amount'], '借方']);

  //貸方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $_POST['credit_account'], $_POST['credit_amount'], '貸方']);
  ?>

  <p><a href="../siwake_hyo/input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="../siwake_hyo/output_siwakehyo.php">仕訳一覧表示</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>
</body>
</html>
