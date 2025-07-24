<?php
$page_title = '仕訳登録画面';
$current_page = 'submit';
require_once __DIR__ . '/../a_header.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../header.php';


// =================================================================
// 1. 入力値のチェック
// =================================================================
if (
    empty($_POST['entry_date']) || empty($_POST['description']) ||
    empty($_POST['debit_account']) || empty($_POST['debit_amount']) ||
    empty($_POST['credit_account']) || empty($_POST['credit_amount']) 
) {
    die('すべての項目を入力してください。<a href="input_siwake.php">戻る</a>');
}

$entry_date = $_POST['entry_date'];
$description = $_POST['description'];
$debit_account_id = $_POST['debit_account'];
$debit_amount = $_POST['debit_amount'];
$credit_account_id = $_POST['credit_account'];
$credit_amount = $_POST['credit_amount'];

// 金額が等しいかチェック (貸借平均の原理)
if ($debit_amount != $credit_amount) {
    die('借方と貸方の金額が一致しません。<a href="input_siwakehyo.php">戻る</a>');
}

// =================================================================
// 2. データベース登録処理
// =================================================================
try {
       // トランザクション開始
        $PDO->beginTransaction();

       // Step 1: 仕訳ヘッダーの登録
        $sql_header = $PDO->prepare('INSERT INTO JOURNAL_HEADERS (ENTRY_DATE, DESCRIPTION) VALUES(?, ?)');
        $sql_header->execute([$entry_date, $description]);
        
       // 登録したヘッダーのIDを取得
        $header_id = $PDO->lastInsertId();
       // Step 2 & 3: 仕訳明細（借方・貸方）を登録
        // 借方の仕訳明細を登録
        $sql_debit =  $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (JOURNAL_HEADER_ID, ACCOUNT_ID, TYPE, AMOUNT) VALUES (?, ?, ?, ?)');
        $sql_debit->execute([$header_id, $debit_account_id, '借方', $debit_amount]);
         // 貸方の仕訳明細を登録
        $sql_credit = $PDO->prepare('INSERT INTO JOURNAL_ENTRIES (JOURNAL_HEADER_ID, ACCOUNT_ID, TYPE, AMOUNT) VALUES (?, ?, ?, ?)');
        $sql_credit->execute([$header_id, $credit_account_id, '貸方', $credit_amount]);
        
       // すべての登録が成功したらコミット
        $PDO->commit();
    
    // =================================================================
    // 3. 表示用に勘定科目名を取得
    // =================================================================
    $account_names = [];
    $sql_accounts = $PDO->prepare('SELECT ID, NAME FROM ACCOUNTS WHERE ID IN (?, ?)');
    $sql_accounts->execute([$debit_account_id, $credit_account_id]);
    foreach ($sql_accounts->fetchAll(PDO::FETCH_ASSOC) as $acc) {
        $account_names[$acc['ID']] = $acc['NAME'];
    }


    // =================================================================
    // 4. 登録完了メッセージの表示
    // =================================================================
    ?>
    <h3>以下の内容で仕訳が登録されました</h3>
    <table class="table text-center table-bordered">
        <thead class="thead-light">
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
                <td><?php echo htmlspecialchars($entry_date, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($account_names[$debit_account_id] ?? '不明', ENT_QUOTES, 'UTF-8'); ?></td>
                <td>¥<?php echo number_format($debit_amount); ?></td>
                <td><?php echo htmlspecialchars($account_names[$credit_account_id] ?? '不明', ENT_QUOTES, 'UTF-8'); ?></td>
                <td>¥<?php echo number_format($credit_amount); ?></td>
            </tr>
        </tbody>
    </table>

<?php
} catch (PDOException $e) {
    // エラーが発生したらロールバック
    $PDO->rollBack();
    die("データベース登録中にエラーが発生しました: " . $e->getMessage());
}

// =================================================================
// 5. 売上高科目の場合、バッチ処理を実行
// =================================================================

if ($debit_account_id == 8) {
    echo '<div class="alert alert-info">売上データが検出されたため、売上集計データを更新します...</div>';
    // バッチ処理の部品ファイルを読み込む
    require_once '../batch_process_sales.php';
    // バッチ処理関数を実行し、結果を表示
    $batch_message = runSalesBatchProcess($PDO);
    echo '<div class="alert alert-success">' . htmlspecialchars($batch_message, ENT_QUOTES, 'UTF-8') . '</div>';
}
?>

<hr>
<p><a href="/accounting/siwake_hyo/input_siwakehyo.php">続けて仕訳を入力する</a></p>
<p><a href="/accounting/siwake_hyo/output_siwakehyo.php">仕訳一覧表示</a></p>
<p><a href="../../main.php">トップページに戻る</a></p>

<?php 
// フッターの読み込み
// require_once '../a_footer.php'; 
?>
