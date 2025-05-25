<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>仕訳帳（デモ）</title>
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
            $sql = $PDO->prepare('SELECT ID, ENTRY_DATE, DESCRIPTION FROM JOURNAL_HEADERS INNER JOIN JOURNAL_ENTRIES ON JOURNAL_HEADERS.ID = JOURNAL_ENTRIES.HEADER_ID AS SIWAKEHYO' );
            $sql->execute();
            // 取得したデータを配列に格納
            $entry = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($entries as $entry) {
                    echo '<td>' . $entry['ID'] . '</td>'; // 仕訳番号
                    echo '<td>' . $entry['ENTRY_DATE'] . '</td>'; // 日付
                    echo '<td>' . $entry['DESCRIPTION'] . '</td>'; // 摘要
            }
            $sql = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM SIWAKEHYO INNER JOIN ACCOUNTS ON JOURNAL_HEADERS.ACCOUNT_ID = ACCOUNTS.ID AS SIWAKEHYO_FULL WHERE TYPE = "借方"');
            $sql->execute();

            // 取得したデータを表示
            $debit_entry = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($entries as $debit_entry) {
                echo '<td>' . $entries['DEBIT_ACCOUNT'] . '</td>'; // 借方科目
                echo '<td>' . $entries['DEBIT_AMOUNT'] . '</td>'; // 借方金額
              }
            $sql = $PDO->prepare('SELECT ACCOUNTS.NAME, JOURNAL_ENTRIES.AMOUNT FROM SIWAKEHYO_FULL WHERE TYPE = "貸方"');
            $sql->execute();

            // 取得したデータを表示
            foreach ($entries as $credit_entry) {
                    echo '<td>' . $entries['CREDIT_ACCOUNT'] . '</td>'; // 貸方科目
                    echo '<td>' . $entries['CREDIT_AMOUNT'] . '</td>'; // 貸方金額
            }
            ?>
          </tr>
        </tbody>';
    </table>
</body>
</html>
