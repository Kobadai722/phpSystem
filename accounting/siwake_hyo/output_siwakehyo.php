<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>仕訳帳</title>
  <link rel="stylesheet" href="../css/siwakehyo.css" type="text/css">
</head>

  <?php
  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  include '../../header.php'; // ヘッダーの読み込み
  ?>

<body>

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
        <?php
        //一覧表示のための事前準備
        //仕訳ヘッダー表と仕訳明細表を結合

        // 仕訳ヘッダー
        $sql = $PDO->prepare('SELECT JOURNAL_HEADERS.ID, ENTRY_DATE, DESCRIPTION FROM JOURNAL_HEADERS INNER JOIN JOURNAL_ENTRIES ON JOURNAL_HEADERS.ID = JOURNAL_ENTRIES.HEADER_ID');
        $sql->execute();
        $entries = $sql->fetchALL(PDO::FETCH_ASSOC);
        foreach ($entries as $entry) {
          echo '<tr>';
          $sql2 = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM JOURNAL_ENTRIES INNER JOIN ACCOUNTS ON JOURNAL_ENTRIES.ACCOUNT_ID = ACCOUNTS.ID WHERE JOURNAL_ENTRIES.HEADER_ID = ? AND JOURNAL_ENTRIES.TYPE = ?');
          $sql2->execute([$entry['ID'], '借方']);
          $debit_entry = $sql2->fetchALL(PDO::FETCH_ASSOC);
          $sql3 = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM JOURNAL_ENTRIES INNER JOIN ACCOUNTS ON JOURNAL_ENTRIES.ACCOUNT_ID = ACCOUNTS.ID WHERE JOURNAL_ENTRIES.HEADER_ID = ? AND JOURNAL_ENTRIES.TYPE = ?');
          $sql3->execute([$entry['ID'], '貸方']);
          $credit_entry = $sql3->fetchALL(PDO::FETCH_ASSOC);
          foreach ($debit_entry as $debit) {
            foreach ($credit_entry as $credit) {
              echo '<td>' . $entry['ID'] . '</td>'; // 仕訳番号
              echo '<td>' . $entry['ENTRY_DATE'] . '</td>'; // 日付
              echo '<td>' . $entry['DESCRIPTION'] . '</td>'; // 摘要
              echo '<td>' . $debit['NAME'] . '</td>'; // 借方科目
              echo '<td>' . $debit['AMOUNT'] . '</td>'; // 借方金額
              echo '<td>' . $credit['NAME'] . '</td>'; // 貸方科目
              echo '<td>' . $credit['AMOUNT'] . '</td>'; // 貸方金額
              echo '</tr>';
            }
          }
        }
        ?>
    </tbody>
  </table>
  <p><a href="../siwake_hyo/input_siwakehyo.php">仕訳入力画面に戻る</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>
</body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>


</html>
