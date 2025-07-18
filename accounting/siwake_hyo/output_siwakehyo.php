<?php
$page_title = '仕訳一覧表示';
$current_page = 'list';
require_once '../a_header_test.php';
?>
<?php
require_once '../../config.php';
require_once '../../header.php';
?>
  <!-- ページ全体を囲むコンテナ -->
  <div class="page-container">
    <!-- 左側: サイドバー -->
    <?php include_once 'accounting/sidebar.php'; ?>

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
                            h.ID, h.ENTRY_DATE, h.DESCRIPTION,
                            debit_acc.NAME AS debit_name, debit_entry.AMOUNT AS debit_amount,
                            credit_acc.NAME AS credit_name, credit_entry.AMOUNT AS credit_amount
                        FROM JOURNAL_HEADERS AS h
                        LEFT JOIN JOURNAL_ENTRIES AS debit_entry ON h.ID = debit_entry.HEADER_ID AND debit_entry.TYPE = '借方'
                        LEFT JOIN ACCOUNTS AS debit_acc ON debit_entry.ACCOUNT_ID = debit_acc.ID
                        LEFT JOIN JOURNAL_ENTRIES AS credit_entry ON h.ID = credit_entry.HEADER_ID AND credit_entry.TYPE = '貸方'
                        LEFT JOIN ACCOUNTS AS credit_acc ON credit_entry.ACCOUNT_ID = credit_acc.ID
                        ORDER BY h.ID");
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
      <!-- csv出力を予定 -->
      <a href="../siwake_hyo/input_siwakehyo.php" class="btn btn-primary mt-3">仕訳入力画面に戻る</a>
      <a href="../../main.php" class="btn btn-secondary mt-3">トップページに戻る</a>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>
