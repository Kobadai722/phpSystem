<?php
/**
 * 売上データのバッチ処理を行う関数
 *  require_once で利用
 */

// 直接アクセスされた場合に処理を中断する
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('このファイルには直接アクセスできません。');
}

/**
 * 仕訳データから売上データを抽出し、SALES_ENTRIESテーブルを更新する。
 *
 * @param PDO $pdo データベース接続オブジェクト
 * @return string 処理結果のメッセージ
 */
function runSalesBatchProcess(PDO $pdo)
{
    try {
        // Step 1: 仕訳データから売上データを抽出
        $sql_select = $pdo->prepare('SELECT H.ENTRY_DATE, E.AMOUNT 
                                    FROM JOURNAL_ENTRIES AS E
                                    JOIN JOURNAL_HEADERS AS H ON E.HEADER_ID = H.ID
                                    WHERE E.ACCOUNT_ID = :account_id AND E.TYPE = :type
                                    ');
        $sql_select->bindValue(':account_id', 8, PDO::PARAM_INT);
        $sql_select->bindValue(':type', '借方', PDO::PARAM_STR);
        $sql_select->execute();
        $results = $sql_select->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($results)) {

            //テーブルが空でない場合のみTRUNCATEを実行する 
            // まず、現在のテーブルの行数を取得
            $count_stmt = $pdo->query("SELECT COUNT(*) FROM SALES_ENTRIES");
            $current_rows = (int)$count_stmt->fetchColumn();

            $pdo->beginTransaction();
            
            // データが1件以上存在する場合のみ、テーブルを空にする
            if ($current_rows > 0) {
                $pdo->exec("TRUNCATE TABLE SALES_ENTRIES");
            }
        
            $sql_insert = $pdo->prepare(
                "INSERT INTO SALES_ENTRIES (SALE_DATE, AMOUNT) VALUES (:sale_date, :amount)"
            );

            foreach ($results as $row) {
                $sql_insert->bindValue(':sale_date', $row['ENTRY_DATE'], PDO::PARAM_STR);
                $sql_insert->bindValue(':amount', $row['AMOUNT'], PDO::PARAM_INT);
                $sql_insert->execute();
            }
            $pdo->commit();
            return "バッチ処理が正常に完了しました。" . count($results) . "件のデータを登録・すべての売上を集計完了。";
        } else {
            return "処理対象の売上データがありませんでした。";
        }

    } catch (PDOException $e) {
        // エラーが発生した場合はロールバック
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return "バッチ処理中にエラーが発生しました: " . $e->getMessage();
    }
}

