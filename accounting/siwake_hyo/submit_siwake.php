<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">

  <title>仕訳登録完了</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<!-- ヘッダーの読み込み -->
<?php require_once '../../header.php'; ?>
  <h3>以下の内容で仕訳が登録されました</h3>
  <table class="table text-center">
    <tr>
      <th>日付</th>
      <th>摘要</th>
      <th>借方科目</th>
      <th>借方金額</th>
      <th>貸方科目</th>
      <th>貸方金額</th>
    </tr>
    <tr>
        <td><?php echo $_POST['entry_date']; ?></td>
        <td><?php echo $_POST['description']; ?></td>
        <td><?php echo $_POST['debit_account']; ?></td>
        <td><?php echo $_POST['debit_amount']; ?></td>
        <td><?php echo $_POST['credit_account']; ?></td>
        <td><?php echo $_POST['credit_amount']; ?></td>
    </tr>
  </table>

  <p><a href="../siwake_hyo/input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="../siwake_hyo/output_siwakehyo.php">仕訳一覧表示</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>

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
    $entry_date = $_POST['entry_date'];
    $description = $_POST['description'];
    $debit_account = $_POST['debit_account'];
    $debit_amount = $_POST['debit_amount'];
    $credit_account = $_POST['credit_account'];
    $credit_amount = $_POST['credit_amount'];

    // 金額が等しいかチェック (貸借平均の原理)
    if ($debit_amount !== $credit_amount) {
      echo '借方と貸方の金額が一致しません。<a href="../siwake_hyo/input_siwakehyo.php">戻る</a>';
      exit; // ここで処理を中断
    }


  // DB接続
  require_once '../../config.php';
  //仕訳ヘッダーの登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
  $sql->execute($entry_date, $description);
  //仕訳明細の登録
  // 勘定科目のIDを取得するためのSQL
  $header_id = $PDO->lastInsertId();
  //借方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $debit_account, $debit_amount, '借方']);

  //貸方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $credit_account, $credit_account, '貸方']);
  ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
