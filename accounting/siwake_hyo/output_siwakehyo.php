<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>仕訳帳</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/phpSystem/accounting/css/siwake.css">
</head>

<body>
  <?php
  // DB接続
  require_once '../../config.php';
  // セッション開始
  session_start();
  include '../../header.php'; // ヘッダーの読み込み
  ?>
  <h1>📘 仕訳帳</h1>
  <table class="table">
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
    <tbody>
      <main>
        <nav class="localNavigation">
          <ul>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="bi bi-house-door-fill"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="bi bi-box-seam"></i> 在庫管理</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="bi bi-bar-chart-line-fill"></i> 売上管理</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="bi bi-cart-check-fill"></i> 発注管理</a>
            </li>
          </ul>
        </nav>
        <table class="table table-border table-hover table-smaller">
          <?php
          $sql = $PDO->prepare("SELECT
          h.ID,
          h.ENTRY_DATE,
          h.DESCRIPTION,
          debit_acc.NAME AS debit_name, -- 借方勘定科目名
          debit_entry.AMOUNT AS debit_amount, -- 借方金額
          credit_acc.NAME AS credit_name, -- 貸方勘定科目名
          credit_entry.AMOUNT AS credit_amount -- 貸方金額
          FROM
          JOURNAL_HEADERS AS h
          LEFT JOIN
          JOURNAL_ENTRIES AS debit_entry ON h.ID = debit_entry.HEADER_ID AND debit_entry.TYPE = '借方'
          LEFT JOIN
          ACCOUNTS AS debit_acc ON debit_entry.ACCOUNT_ID = debit_acc.ID
          LEFT JOIN
          JOURNAL_ENTRIES AS credit_entry
          ON h.ID = credit_entry.HEADER_ID
          AND credit_entry.TYPE = '貸方'
          LEFT JOIN
          ACCOUNTS AS credit_acc
          ON credit_entry.ACCOUNT_ID = credit_acc.ID
          ORDER BY h.ENTRY_DATE, h.ID;");
          $sql->execute();
          $results = $sql->fetchAll(PDO::FETCH_ASSOC);
          // 取得した結果をループで表示する
          foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['ID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['ENTRY_DATE']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DESCRIPTION']) . '</td>';
            echo '<td>' . htmlspecialchars($row['debit_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['debit_amount']) . '</td>';
            echo '<td>' . htmlspecialchars($row['credit_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['credit_amount']) . '</td>';
            echo '</tr>';
          }
          ?>
        </table>
      </main>
    </tbody>
    <p><a href="../siwake_hyo/input_siwakehyo.php">仕訳入力画面に戻る</a></p>
    <p><a href="../../main.php">トップページに戻る</a></p>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>
