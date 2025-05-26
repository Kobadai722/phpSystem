<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>仕訳帳</title>
  <link rel="stylesheet" href="siwakehyo.css" type="text/css">
</head>

<body>
  <?php
  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  ?>
  <table>
    <caption>📘 仕訳帳（デモ）</caption>
    <thead>
      <tr>
        <th>仕訳番号</th>
        <th>日付</th>
        <th>摘要</th>
        <th>借方科目</th>
        <th>借方金額</th>
        <th>貸方科目</th>
        <th>貸方金額</th>
      </tr>
    </thead>
    <!-- 一覧表示のためのデータ取得 -->
    <tbody>
      <tr>
        <?php
        //一覧表示のための事前準備
        //仕訳ヘッダー表と仕訳明細表を結合
        $sql = $PDO->prepare('SELECT ID, ENTRY_DATE, DESCRIPTION FROM JOURNAL_HEADERS INNER JOIN JOURNAL_ENTRIES ON JOURNAL_HEADERS.ID = JOURNAL_ENTRIES.HEADER_ID');
        $sql->execute();
        // 取得したデータを配列に格納
        $entries = $sql->fetch(PDO::FETCH_ASSOC);
        foreach ($entries as $entry) {
          echo '<td>' . $entry['ID'] . '</td>'; // 仕訳番号
          echo '<td>' . $entry['ENTRY_DATE'] . '</td>'; // 日付
          echo '<td>' . $entry['DESCRIPTION'] . '</td>'; // 摘要
        }
        $sql = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM JOURNAL_ENTRIES INNER JOIN ACCOUNTS ON JOURNAL_ENTRIES.ACCOUNT_ID = ACCOUNTS.ID WHERE JOURNAL_ENTRIES.ID = ? AND TYPE = "借方"');
        $sql->execute([$entry['ID']]);

        // 取得したデータを表示
        $debit_entry = $sql->fetch(PDO::FETCH_ASSOC);
        foreach ($debit_entry as $entry) {
          echo '<td>' . $entry['NAME'] . '</td>'; // 借方科目
          echo '<td>' . $entry['AMOUNT'] . '</td>'; // 借方金額
        }
        $sql = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM JOURNAL_ENTRIES INNER JOIN ACCOUNTS ON JOURNAL_ENTRIES.ACCOUNT_ID = ACCOUNTS.ID WHERE JOURNAL_ENTRIES.ID = ? AND TYPE = "貸方"');
        $sql->execute([$entry['ID']]);

        // 取得したデータを表示
        foreach ($credit_entry as $entry) {
          echo '<td>' . $entry['NAME'] . '</td>'; // 貸方科目
          echo '<td>' . $entry['AMOUNT'] . '</td>'; // 貸方金額
        }
        ?>
      </tr>
    </tbody>;
  </table>
  <p><a href="../siwake_hyo/input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>

  <footer>
    <p>© 2025 <img class="mb-4" src="/images/logo-type2.png" alt="" width="300" height="auto" loading="lazy"></p>

</body>

</html>
