<?php
$page_title = '仕訳入力フォーム';
$current_page = 'input';
require_once '../a_header.php';
?>
  <!-- ページ全体を囲むコンテナ -->
  <div class="page-container">
    <!-- 左側: サイドバー -->
    <?php require_once '../sidebar.php' ?>
    <!-- 右側: メインコンテンツ -->
    <main class="main-content">
      <div class="table-responsive">
        <h1><i class="bi bi-journal-text"></i> 仕訳入力</h1>
        <form action="submit_siwake.php" method="post">
          <table class="table table-bordered table-hover table-sm">
            <tr>
              <!-- 仕訳ヘッダー-->
              <td class="fw-bold">日付</td>
              <td class="fw-bold">摘要</td>
              <!-- 仕訳明細-->
              <td class="fw-bold">借方科目</td>
              <td class="fw-bold">借方金額</td>
              <td class="fw-bold">貸方科目</td>
              <td class="fw-bold">貸方金額</td>
            </tr>
            <!-- 仕訳明細 -->
            <!--借方部分-->
            <tr>
              <td><input type="date" name="entry_date" class="form-control" required></td><!-- 日付 -->
              <td><input type="text" name="description" class="form-control" required></td> <!-- 摘要 -->
              <td>
                <select name="debit_account" class="form-select" required> <!-- 借方科目 -->
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
              <td><input type="number" name="debit_amount" class="form-control" required></td> <!-- 借方金額 -->
              <td><select name="credit_account" class="form-select" class="form-control" required><!-- 貸方科目 -->
                  <?php
                  foreach ($accounts as $account) {
                    echo '<option value="' . $account['ID'] . '">' . $account['NAME'] . '</option>';
                  }
                  ?>
                </select>
              </td>
              <td><input type="number" name="credit_amount" class="form-control" required></td> <!-- 貸方金額 -->
            </tr>
          </table>
          <button type="submit" class="btn btn-primary">登録</button>
        </form>
      </div>
    </main>
  </div>
  <p><a href="../siwake_hyo/output_siwakehyo.php">仕訳一覧表示</a></p>
  <p><a href="../../main.php">トップページに戻る</a></p>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>
