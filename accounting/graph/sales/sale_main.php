<?php
  $page_title = "sale_main";
  require_once "../a_header.php";
  require_once "../config.php";
  require_once "../header.php";
?>
<div class="page-container">
  <!-- 表示する期間選択 -->
   <!-- 年の選択 -->
    
   <!-- 月の選択 -->



  <!--売上高一覧 -->
  <table>
    <thead>
      <tr>
        <th>売上日</th>
        <th>売上高</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $sql = $PDO->prepare("SELECT SALE_DATE, AMOUNT FROM SALES_ENTRIES");
      $sql->execute();
      $results = $sql->fetchAll(PDO::FETCH_ASSOC);
      foreach ($results as $row) {
        echo '<tr><td>'
          . htmlspecialchars($row['SALE_DATE'])
          . '</td><td>'
          . htmlspecialchars($row['AMOUNT'])
          . '</td></tr>';
      }
      ?>
    </tbody>
  </table>
  <?php require_once "sale_function.php";?>
  

