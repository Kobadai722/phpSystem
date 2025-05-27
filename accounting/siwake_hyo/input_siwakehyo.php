<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="siwakehyo.css" type="text/css">
  <title>仕訳入力フォーム</title>
</head>

<body>
  <?php
  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  ?>

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
      <!-- 仕訳明細 -->
      <!--借方部分-->
      <tr>
        <td><input type="date" name="entry_date" required></td><!-- 日付 -->

        <td><input type="text" name="description" required></td> <!-- 摘要 -->

        <td>
          <select name="debit_account" required> <!-- 借方科目 -->
            <?php
            //勘定科目の取得
            $sql = $PDO->prepare('SELECT * FROM ACCOUNTS');
            $sql->execute();
            $accounts = $sql->fetchAll(PDO::FETCH_ASSOC);
            // 取得したデータを表示
            foreach ($accounts as $account) {
              echo '<option value="' . $account['ID'] . '">' . $account['NAME'] . '</option>';
            }
            ?>
          </select>
        </td>
        <td><input type="number" name="debit_amount" required></td> <!-- 借方金額 -->
        <td><select name="credit_account" required><!-- 貸方科目 -->
            <?php
            foreach ($accounts as $account) {
              echo '<option value="' . $account['ID'] . '">' . $account['NAME'] . '</option>';
            }
            ?>
          </select>
        </td>
        <td><input type="number" name="credit_amount" required></td> <!-- 貸方金額 -->
      </tr>
    </table>
    <br>
    <button type="submit">登録</button>

  </form>
</body>
</html>
