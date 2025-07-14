<?php
    $page_title = 'グラフ（売上データ）';
    $current_page = 'sales_graph';
    require_once '../a_header.php';

    // 売上の科目IDは8
    // 科目IDが8で、借方のものを抽出する
    //仕訳明細テーブルから（id、仕訳ヘッダーid、金額）抽出
        $sql = $PDO ->preopare('SELECT ID, JOURNAL_HEADER_ID, AMOUNT FROM WHERE ACCOUNT_ID = 8 AS S;') 
        // idと仕訳ヘッダーidで仕訳ヘッダーと結合

        // 結合した表から（取引日、金額）を抽出

    //TODO SALES_ENTRIES（日付、金額）を追加する。
        // $sql = $PDO ->preopare()








    //TODO DATE型 YY-MM-DD　→年、月をintに変換するメソッド
?>
  