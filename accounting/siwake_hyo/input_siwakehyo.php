<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="siwakehyo.css" type="text/css">
    </head>
<body>
  <table>
    <tr>
      <!-- 仕訳ヘッダー-->
      <td>日付</td>
      <td>摘要</td>
      <!-- 仕訳明細-->
      <td>貸方科目</td>
      <td>貸方金額</td>
      <td>借方科目</td>
      <td>借方金額</td>
    </tr>
    <tr>
      <td><input type="date" name="entry_date"></td><!-- 日付 -->

      <td><input type="text" name="description"></td> <!-- 摘要 -->

      <td>
        <select name="勘定科目" name="debit_account"> <!-- 借方科目 -->
          <option value="">売掛金</option>
      </td>

      <td><input type="number" name="debit_amount"></td>  <!-- 借方金額 -->

      <td><input type="date" name="entry_date"></td><!-- 日付 -->

      <td><input type="text" name="description"></td> <!-- 摘要 -->

      <td><select name="勘定科目" name="credit_account"><!-- 貸方科目 -->
            <option value="">売掛金</option></td>

      <td><input type="number" name="credit_amount"></td>  <!-- 貸方金額 -->



    </tr>
  </table>
  <?php
    session_start();
    // DB接続
    require_once 'config.php';
    //仕訳ヘッダーの登録
    $sql = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
    $sql->execute([$_POST['entry_date'], $_POST['description']]);
    //仕訳明細の登録
    //ヘッダーIDの取得
    $sql = $PDO->prepare('SELECT ID FROM JOURNAL_HEADERS WHERE ENTRY_DATE = ? AND DESCRIPTION = ?');
    $sql->execute([$_POST['entry_date'], $_POST['description']]);
    $header_id = $pdo->lastInsertId();
    
    //借方の登録
    $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES($header_id, ?, 借方)');
    $sql->execute([$_POST['debit_account'], $_POST['debit_amount']]);
