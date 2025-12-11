<?php
/**
 * 自動仕訳登録機能（売上連携用）
 * 販売管理システムなどで売上が確定した際に呼び出され、
 * 会計システムの仕訳テーブルにデータを自動登録します。
 */

/**
 * 売上仕訳を登録する関数
 *
 * @param PDO $pdo データベース接続オブジェクト（トランザクション用）
 * @param string $date 取引日 (YYYY-MM-DD)
 * @param int $amount 取引金額
 * @param string $description 摘要（例: "株式会社〇〇様 売上"）
 * @return bool 成功した場合はtrue
 * @throws Exception エラー発生時
 */
function registerSalesJournal(PDO $pdo, string $date, int $amount, string $description): bool
{
    // 勘定科目のID設定（環境に合わせて変更してください）
    // 今回はデモデータに合わせて設定します
    $DEBIT_ACCOUNT_ID = 1;  // 借方: 現金（または売掛金）
    $CREDIT_ACCOUNT_ID = 8; // 貸方: 売上高

    try {
        // ※呼び出し元ですでにトランザクションが開始されていることを想定する場合、
        // ここでのbeginTransactionは不要ですが、単独で動く安全性も考慮して
        // トランザクションの状態を確認することも可能です。
        // 今回は、親元の処理と「一連の流れ」として扱いたいので、
        // 親元でトランザクションをかけている前提で、ここではINSERTのみを行います。

        // 1. 仕訳ヘッダーの登録
        $sql_header = "INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES (:date, :desc)";
        $stmt_header = $pdo->prepare($sql_header);
        $stmt_header->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt_header->bindValue(':desc', $description, PDO::PARAM_STR);
        $stmt_header->execute();

        // 登録したヘッダーIDを取得
        $header_id = $pdo->lastInsertId();

        // 2. 仕訳明細（借方：現金/売掛金）の登録
        $sql_entry = "INSERT INTO JOURNAL_ENTRIES (HEADER_ID, ACCOUNT_ID, AMOUNT, TYPE) VALUES (:header_id, :account_id, :amount, :type)";
        $stmt_entry = $pdo->prepare($sql_entry);
        
        // 借方
        $stmt_entry->bindValue(':header_id', $header_id, PDO::PARAM_INT);
        $stmt_entry->bindValue(':account_id', $DEBIT_ACCOUNT_ID, PDO::PARAM_INT);
        $stmt_entry->bindValue(':amount', $amount, PDO::PARAM_INT);
        $stmt_entry->bindValue(':type', '借方', PDO::PARAM_STR);
        $stmt_entry->execute();

        // 3. 仕訳明細（貸方：売上高）の登録
        // 貸方
        $stmt_entry->bindValue(':header_id', $header_id, PDO::PARAM_INT);
        $stmt_entry->bindValue(':account_id', $CREDIT_ACCOUNT_ID, PDO::PARAM_INT);
        $stmt_entry->bindValue(':amount', $amount, PDO::PARAM_INT);
        $stmt_entry->bindValue(':type', '貸方', PDO::PARAM_STR);
        $stmt_entry->execute();

        require_once __DIR__ . '/batch_process_sales.php';
        return true;

    } catch (Exception $e) {
        // エラーが発生した場合は、呼び出し元に例外を投げる
        // 呼び出し元でロールバックしてもらうため
        throw $e;
    }
}