<?php
  //仕訳ヘッダーの登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
  $sql->execute([$_POST['entry_date'], $_POST['description']]);

  //仕訳明細の登録
  //ヘッダーIDの取得
  $sql = $PDO->prepare('SELECT ID FROM JOURNAL_HEADERS WHERE ENTRY_DATE = ? AND DESCRIPTION = ?');
  $sql->execute([$_POST['entry_date'], $_POST['description']]);
  $header_id = $PDO->lastInsertId();

  //借方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, 借方)');
  $sql->execute([$header_id, $_POST['debit_account'], $_POST['debit_amount']]);

  //貸方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES(?, ?, ?, ?)');
  $sql->execute([$header_id, $_POST['credit_account'], $_POST['credit_amount'], '貸方']);
  ?>
