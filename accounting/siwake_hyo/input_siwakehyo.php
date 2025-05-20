<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="siwakehyo.css" type="text/css">
</head>

<body>
  <?php
  session_start();
  // DB接続
  require_once '../../config.php';
  ?>
  <!-- 入力フォーム-->
  <form method="post">
    <table>
      <tr>
        <!-- 仕訳ヘッダー-->
        <td>日付</td>
        <td>摘要</td>
        <!-- 仕訳明細-->
        <td>借方科目</td>
        <td>借方金額</td>
        <td>貸方科目</td>
        <td>貸方金額</td>
      </tr>
      <tr>
        <td><input type="date" name="entry_date"></td><!-- 日付 -->

        <td><input type="text" name="description"></td> <!-- 摘要 -->

        <td>
        <select name="勘定科目" name="credit_account"><!-- 貸方科目 -->
            <?php
            while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $row['ID'] . '">' . $row['NAME'] . '</option>';
            }
            ?>
          </select>
        </td>

        <td><input type="number" name="debit_amount"></td> <!-- 借方金額 -->

        <td><input type="date" name="entry_date"></td><!-- 日付 -->

        <td><input type="text" name="description"></td> <!-- 摘要 -->

        <td>
          <select name="勘定科目" name="credit_account"><!-- 貸方科目 -->
            <?php
            while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $row['ID'] . '">' . $row['NAME'] . '</option>';
            }
            ?>
          </select>
        </td>
        <td><input type="number" name="credit_amount"></td> <!-- 貸方金額 -->
      </tr>
    </table>
    <br>
    <button type="submit">確定</button>
    </table>
  </form>

  <?php
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

  //貸方の登録
  $sql = $PDO->prepare('INSERT INTO JOURNAL_ENTRY (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES($header_id, ?, 貸方)');
  $sql->execute([$_POST['credit_account'], $_POST['credit_amount']]);
  ?>
