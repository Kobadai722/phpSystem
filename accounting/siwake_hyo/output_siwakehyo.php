<?php
$page_title = '仕訳一覧表示';
$current_page = 'list';
require_once __DIR__ . '/../a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';

?>
  <!-- ページ全体を囲むコンテナ -->
  <div class="page-container">
    <!-- 左側: サイドバー -->
    <?php require_once __DIR__ . '/../sidebar.php'; ?>

    <!-- 右側: メインコンテンツ -->
    <main class="main-content">
      <h1>📘 仕訳帳</h1>

      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
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
            <?php
            //勘定科目テーブル、仕訳ヘッダーテーブル、仕訳明細テーブルの結合
            $sql = $PDO->prepare("SELECT
                                      h.ID,
                                      h.ENTRY_DATE,
                                      h.DESCRIPTION,
                                      (SELECT ACCOUNT_ID FROM JOURNAL_ENTRIES WHERE HEADER_ID = h.ID AND TYPE = '借方') AS debit_name,
                                      e.DEBIT_AMOUNT,
                                      (SELECT NAME FROM ACCOUNTS WHERE ID = e.CREDIT_ACCOUNT_ID AND TYPE = '貸方') AS credit_name,
                                      e.CREDIT_AMOUNT
                                  FROM
                                      JOURNAL_HEADERS AS h
                                  LEFT JOIN
                                      JOURNAL_ENTRIES AS e
                                      ON h.ID = e.HEADER_ID
                                  ORDER BY
                                      h.ENTRY_DATE DESC, h.ID DESC;");
            $sql->execute();
            $results = $sql->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $row) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['ID']) . '</td>';
              echo '<td>' . htmlspecialchars($row['ENTRY_DATE']) . '</td>';
              echo '<td>' . htmlspecialchars($row['DESCRIPTION']) . '</td>';
              echo '<td>' . htmlspecialchars($row['debit_name'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($row['debit_amount'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($row['credit_name'] ?? '') . '</td>';
              echo '<td>' . htmlspecialchars($row['credit_amount'] ?? '') . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
      <a href="/accounting/siwake_hyo/input_siwakehyo.php" class="btn btn-primary mt-3">仕訳入力画面に戻る</a>
      <a href="../../main.php" class="btn btn-secondary mt-3">トップページに戻る</a>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>
