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
  ?>;
    <table>
        <caption>📘 仕訳帳（デモ）</caption>
        <thead>
          <tr>
            <th>日付</th>
            <th>摘要</th>
            <th>借方科目</th>
            <th>借方金額</th>
            <th>貸方科目</th>
            <th>貸方金額</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <?php
              // 日付の取得
                $sql = $PDO->prepare('SELECT ENTRY_DATE FROM JOURNAL_HEADERS');
                $sql->execute();
                foreach ($sql as $entry_date) {
                  echo $row['ENTRY_DATE'];
                }
              ?>
            </td>
            <td class="left">
              <?php
              // 摘要の取得
                $sql = $PDO->prepare('SELECT DESCRIPTION FROM JOURNAL_HEADERS');
                $sql->execute();
                foreach ($sql as $description) {
                  echo $row['DESCRIPTION'];
                }
              ?>
            </td>
            <td>現金</td>
            <td>¥100,000</td>
            <td>売上高</td>
            <td>¥100,000</td>
          </tr>
          <tr>
            <td>2025/04/22</td>
            <td class="left">事務用品購入</td>
            <td>消耗品費</td>
            <td>¥5,000</td>
            <td>現金</td>
            <td>¥5,000</td>
          </tr>
          <tr>
            <td>2025/04/22</td>
            <td class="left">給与支払</td>
            <td>給与手当</td>
            <td>¥200,000</td>
            <td>普通預金</td>
            <td>¥200,000</td>
          </tr>
        </tbody>
      </table>
<!--      <?php
  // ダミーデータ（将来DBから取得される想定）
  $entries = [
    ['date' => '2025/04/22', 'desc' => '商品販売', 'debit' => '現金', 'debit_amt' => '100000', 'credit' => '売上高', 'credit_amt' => '100000'],
    ['date' => '2025/04/22', 'desc' => '事務用品購入', 'debit' => '消耗品費', 'debit_amt' => '5000', 'credit' => '現金', 'credit_amt' => '5000'],
    ['date' => '2025/04/22', 'desc' => '給与支払', 'debit' => '給与手当', 'debit_amt' => '200000', 'credit' => '普通預金', 'credit_amt' => '200000']
  ];

  foreach ($entries as $entry) {
    echo "<tr>
            <td>{$entry['date']}</td>
            <td>{$entry['debit']}</td>
            <td>¥" . number_format($entry['debit_amt']) . "</td>
            <td>{$entry['credit']}</td>
            <td>¥" . number_format($entry['credit_amt']) . "</td>
            <td class='left'>{$entry['desc']}</td>
          </tr>";
        }
    ?>
-->

    </tbody>
  </table>

</body>
</html>
