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
  <form action="submit_siwakehyo.php" method="post">
    <table>
      <tr>
        <!-- 仕訳ヘッダー-->
        <td>日付</td>
        <td>摘要</td>
        <!-- 仕訳明細-->
        <td>借方科目</td>
        <td>借方金額</td>
        <td>日付</td>
        <td>貸方科目</td>
        <td>貸方金額</td>
      </tr>
      <!-- 仕訳明細 -->
      <!--借方部分-->
      <tr>
        <td><input type="date" name="entry_date"></td><!-- 日付 -->

        <td><input type="text" name="description"></td> <!-- 摘要 -->

        <td>
          <select name="勘定科目" name="debit_account"> <!-- 借方科目 -->
            <?php
            //勘定科目の取得
            $sql = $PDO->prepare('SELECT NAME FROM ACCOUNTS');
            $sql->execute();
            $accounts = $sql->fetchAll(PDO::FETCH_ASSOC);

            // 取得したデータを表示
            foreach ($accounts as $account) {
              echo '<option value="' . $account['NAME'] . '">' . $account['NAME'] . '</option>';
            }
            ?>
        </td>

        <td><input type="Intl.NumberFormat" name="debit_amount"></td> <!-- 借方金額 -->

        <!--貸方部分-->
        <td><input type="date" name="entry_date"></td><!-- 日付 -->

        <td><select name="勘定科目" name="credit_account"><!-- 貸方科目 -->
            <?php
            foreach ($accounts as $account) {
              echo '<option value="' . $account['NAME'] . '">' . $account['NAME'] . '</option>';
            }
            ?>
        </td>

        <td><input type="number" name="credit_amount"></td> <!-- 貸方金額 -->

      </tr>
    </table>
    <br>
    <button type="submit">登録</button>
  </form>
</body>
</html>
