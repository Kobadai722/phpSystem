<?php
// DB接続とデータ取得、エラー処理
require_once '../../config.php';

// 注文ID取得とバリデーション
$order_id = $_GET['id'] ?? '';

if (empty($order_id)) {
    // 注文IDがない場合
    echo '<p class="text-danger text-center mt-5">注文IDが指定されていません。</p>';
    exit;
}

$errors = []; // エラーメッセージ格納用配列
$error_html = ''; // 表示用エラーHTML

// ----------------------------------------------------------------------------------
// POST送信（更新処理）の実行
// ----------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. バリデーションとデータの取得
    $new_datetime = trim($_POST['order_datetime'] ?? '');
    // 数値としてフィルタリング。無効な場合は false になる
    $new_amount = filter_var($_POST['total_amount'] ?? '', FILTER_VALIDATE_INT); 
    $new_customer_id = trim($_POST['customer_id'] ?? '');
    $new_status = trim($_POST['status'] ?? '');
    
    if (empty($new_datetime) || !strtotime($new_datetime)) {
        $errors[] = '注文日時が不正です。正しい日付と時刻を入力してください。';
    }

    if ($new_amount === false || $new_amount < 0) {
        $errors[] = '合計金額は0以上の整数で入力してください。';
    }

    if (empty($new_customer_id)) {
        $errors[] = '顧客が選択されていません。';
    }
    
    // ステータスの有効性をチェック (今回は['未払い', '支払い済み', 'キャンセル']に限定)
    $valid_statuses = ['未払い', '支払い済み', 'キャンセル'];
    if (!in_array($new_status, $valid_statuses)) {
        $errors[] = '無効な支払い状況が指定されました。';
    }

    // 2. エラーがない場合、データベースを更新
    if (empty($errors)) {
        try {
            $update_sql = "UPDATE S_ORDER 
                           SET ORDER_DATETIME = :datetime, 
                               TOTAL_AMOUNT = :amount, 
                               CUSTOMER_ID = :customer_id, 
                               STATUS = :status 
                           WHERE ORDER_ID = :order_id";

            $update_stmt = $PDO->prepare($update_sql);
            $update_stmt->bindValue(':datetime', $new_datetime, PDO::PARAM_STR);
            $update_stmt->bindValue(':amount', $new_amount, PDO::PARAM_INT);
            $update_stmt->bindValue(':customer_id', $new_customer_id, PDO::PARAM_STR);
            $update_stmt->bindValue(':status', $new_status, PDO::PARAM_STR);
            $update_stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR); 

            $update_stmt->execute();

            // 更新成功後、詳細ページにリダイレクトして終了
            header('Location: order_detail.php?id=' . urlencode($order_id));
            exit;

        } catch (PDOException $e) {
            $errors[] = 'データベース更新エラーが発生しました。時間を置いて再度お試しください。';
        }
    }
}
// ----------------------------------------------------------------------------------
// 既存データの取得（POSTエラー時も最新の情報をフォームに反映するため、必ず実行）
// ----------------------------------------------------------------------------------
try {
    // 注文情報取得SQL
    $sql = "SELECT o.ORDER_ID, o.ORDER_DATETIME, o.TOTAL_AMOUNT, o.STATUS, o.CUSTOMER_ID, c.NAME AS CUSTOMER_NAME
            FROM S_ORDER o
            LEFT JOIN CUSTOMER c ON o.CUSTOMER_ID = c.CUSTOMER_ID
            WHERE o.ORDER_ID = :order_id";

    $stmt = $PDO->prepare($sql);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_STR);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo '<p class="text-danger text-center mt-5">指定された注文が見つかりません。</p>';
        exit;
    }
    
    // 顧客リストの取得 (セレクトボックス用)
    $stmt_customers = $PDO->query("SELECT CUSTOMER_ID, NAME FROM CUSTOMER ORDER BY CUSTOMER_ID");
    $customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<p class="text-danger text-center mt-5">データベースエラーが発生しました。</p>';
    exit;
}

// ----------------------------------------------------------------------------------
// POSTエラー時のフォーム値の上書きとエラーHTMLの生成
// ----------------------------------------------------------------------------------
if (!empty($errors)) {
    // フォームに再入力された値を保持するため、取得した$orderデータを上書き
    $order['ORDER_DATETIME'] = $new_datetime;
    $order['TOTAL_AMOUNT'] = $new_amount;
    $order['CUSTOMER_ID'] = $new_customer_id;
    $order['STATUS'] = $new_status;
    
    // エラー表示用のHTMLを生成
    $error_html = '<div class="alert alert-danger" role="alert"><strong>入力エラー:</strong><ul>';
    foreach ($errors as $error) {
        $error_html .= '<li>' . htmlspecialchars($error) . '</li>';
    }
    $error_html .= '</ul></div>';
}

// ----------------------------------------------------------------------------------
// HTML出力
// ----------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文編集 - <?php echo htmlspecialchars($order['ORDER_ID']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content">
            <div class="container-fluid">
                <h1 class="mb-4">注文編集商品ID: <?php echo htmlspecialchars($order['ORDER_ID']); ?></h1>
                
                <?php echo $error_html; // エラーメッセージの表示 ?>

                <form action="order_edit.php?id=<?php echo urlencode($order['ORDER_ID']); ?>" method="POST">
                
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">基本情報</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tbody>
                                <tr>
                                    <th class="col-md-3">注文ID</th>
                                    <td><?php echo htmlspecialchars($order['ORDER_ID']); ?> </td>
                                </tr>
                                <tr>
                                    <th>注文日時</th>
                                    <td>
                                        <input type="datetime-local" class="form-control" name="order_datetime" 
                                            value="<?php echo htmlspecialchars(date('Y-m-d\TH:i:s', strtotime($order['ORDER_DATETIME']))); ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>顧客名</th>
                                    <td>
                                        <select class="form-select" name="customer_id" required>
                                            <?php foreach ($customers as $customer): ?>
                                            <option value="<?php echo htmlspecialchars($customer['CUSTOMER_ID']); ?>"
                                                <?php if ($customer['CUSTOMER_ID'] == $order['CUSTOMER_ID']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($customer['NAME']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>合計金額</th>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">¥</span>
                                            <input type="number" step="1" min="0" class="form-control" name="total_amount" 
                                                value="<?php echo htmlspecialchars($order['TOTAL_AMOUNT']); ?>" required>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>支払い状況</th>
                                    <td>
                                        <select class="form-select" name="status" required>
                                            <?php 
                                            $statuses = ['未払い', '支払い済み', 'キャンセル'];
                                            foreach ($statuses as $status): 
                                            ?>
                                            <option value="<?php echo htmlspecialchars($status); ?>"
                                                <?php if ($status == $order['STATUS']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($status); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-start mt-4 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> 変更を保存
                    </button>
                    <a href="order_detail.php?id=<?php echo urlencode($order['ORDER_ID']); ?>" class="btn btn-secondary ms-2">
                        <i class="bi bi-x-circle"></i> キャンセル
                    </a>
                </div>
                </form> </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>