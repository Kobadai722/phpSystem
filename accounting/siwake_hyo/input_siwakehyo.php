<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="siwakehyo.css" type="text/css">
  <title>仕訳入力</title>
</head>

<body>
  <?php
  session_start();
  // DB接続
  require_once '../../config.php';


  ?>
  <!-- 入力フォーム-->
  <form action="submit_siwake.php" method="post">
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
          <select name="勘定科目"><!-- 借方科目 -->
            <?php
            // 勘定科目の取得
            $sql = $PDO->prepare('SELECT ID, NAME FROM ACCOUNTS');
            $sql->execute();
            $accounts = $sql->fetch(PDO::FETCH_ASSOC);
            while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $row['ID'] . '">' . $row['NAME'] . '</option>';
            }
            ?>
          </select>
        </td>

        <td><input type="number" name="debit_amount"></td> <!-- 借方金額 -->

        <td>
          <select name="勘定科目"><!-- 貸方科目 -->
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
    <p><button type="submit">確定</button></p>
  </form>
  <p><a href="../../main.php">トップページに戻る</a></p>
  <p><a href="input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="../../index.php">ログアウト</a></p>
</body>

</html>
