<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>仕訳入力フォーム</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="../style.css" rel="stylesheet" />
  <link rel="stylesheet" href="siwakehyo.css" type="text/css">
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

    <p><a href="../siwake_hyo/output_siwakehyo.php">仕訳一覧表示</a></p>
    <p><a href="../../main.php">トップページに戻る</a></p>
  </form>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
