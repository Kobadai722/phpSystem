<?php
    $page_title = 'グラフ（売上データ）';
    $current_page = 'sales_graph';
    require_once '../a_header.php';

    // 売上の科目IDは8
    // 科目IDが8で、借方のものを抽出する
    //仕訳明細テーブルから（id、仕訳ヘッダーid、金額）抽出
    // idと仕訳ヘッダーidで仕訳ヘッダーと結合
        $sql = $PDO ->preopare('SELECT H.ENTRY_DATE, S.AMOUNT 
                                FROM JOURNAL_ENTRIES 
                                AS S
                                JOIN JOURNAL_HEADERS AS H ON S.JOURNAL_HEADER_ID = H.ID
                                WHERE ACCOUNT_ID = 8 
                                AND TYPE = "借方"');
        // 結合した表から（取引日、金額）を抽出
        $sql ->execute();
        $results = $sql ->fetchAll(PDO::FETCH_ASSOC);
    //TODO SALES_ENTRIES（日付、金額）を追加する。
        // $sql = $PDO ->preopare()








    //TODO DATE型 YY-MM-DD　→年、月をintに変換するメソッド    
    function convertDateToYearMonth($dateString) {
        $timestamp = strtotime($dateString);
        $year = (int)date('Y', $timestamp);
        $month = (int)date('m', $timestamp);
        return ['year' => $year, 'month' => $month];
    };
    
?>
  