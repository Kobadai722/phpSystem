<?php
require_once '../../config.php'; // データベース接続設定ファイルを読み込む
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文管理システム - 注文一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css"> </head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content">
            <div class="container-fluid">
                <h1 class="mb-4">注文一覧</h1>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">検索・フィルタリング</h5>
                        <form class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="orderId" class="form-label">注文ID</label>
                                <input type="text" class="form-control" id="orderId" placeholder="例: ORD001">
                            </div>
                            <div class="col-md-3">
                                <label for="customerName" class="form-label">顧客名</label>
                                <input type="text" class="form-control" id="customerName" placeholder="例: 山田太郎">
                            </div>
                            <div class="col-md-3">
                                <label for="paymentStatus" class="form-label">支払い状況</label>
                                <select class="form-select" id="paymentStatus">
                                    <option value="">全て</option>
                                    <option value="未払い">未払い</option>
                                    <option value="支払い済み">支払い済み</option>
                                    </select>
                            </div>
                            <div class="col-md-3">
                                <label for="deliveryStatus" class="form-label">配送状況</label>
                                <select class="form-select" id="deliveryStatus">
                                    <option value="">全て</option>
                                    <option value="未発送">未発送</option>
                                    <option value="発送済み">発送済み</option>
                                    </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="searchButton">
                                    <i class="bi bi-search"></i> 検索
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> リセット
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <a href="order_detail_edit.php?mode=new" class="btn btn-success" id="newOrderButton">
                        <i class="bi bi-plus-circle"></i> 新規注文登録
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered bg-white shadow-sm rounded">
                        <thead class="table-light">
                            <tr>
                                <th>注文ID</th>
                                <th>注文日時</th>
                                <th>顧客名</th>
                                <th>合計金額</th>
                                <th>支払い状況</th>
                                <th>配送状況</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <?php
                            try {
                                // SQLクエリを構築
                                // S_ORDER (注文表) と CUSTOMER (顧客表) を結合して、注文一覧に必要な情報を取得
                                // status カラムは、画面レイアウトに合わせて「支払い状況」と「配送状況」の両方に出力
                                $sql = "SELECT 
                                            so.order_id,
                                            so.order_datetime,
                                            c.customer_name,
                                            so.total_amount,
                                            so.status -- S_ORDER.statusを使用
                                        FROM 
                                            S_ORDER so
                                        LEFT JOIN 
                                            CUSTOMER c ON so.customer_id = c.customer_id
                                        ORDER BY 
                                            so.order_datetime DESC"; // 最新の注文から表示

                                $stmt = $PDO->prepare($sql);
                                $stmt->execute();
                                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($orders)) {
                                    echo '<tr><td colspan="7" class="text-center">表示する注文がありません。</td></tr>';
                                } else {
                                    foreach ($orders as $order) {
                                        // 日時のフォーマット
                                        $order_datetime_formatted = (new DateTime($order['order_datetime']))->format('Y/m/d H:i');
                                        // 合計金額のフォーマット
                                        $total_amount_formatted = '¥' . number_format($order['total_amount']);

                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($order['order_id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($order_datetime_formatted) . '</td>';
                                        echo '<td>' . htmlspecialchars($order['customer_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($total_amount_formatted) . '</td>';
                                        echo '<td>' . htmlspecialchars($order['status']) . '</td>'; // 支払い状況
                                        echo '<td>' . htmlspecialchars($order['status']) . '</td>'; // 配送状況 (現状は同じカラムを使用)
                                        echo '<td class="actions">';
                                        echo '<a href="order_detail_view.php?id=' . htmlspecialchars($order['order_id']) . '" class="btn btn-info btn-sm me-1">詳細</a>';
                                        echo '<a href="order_detail_edit.php?id=' . htmlspecialchars($order['order_id']) . '&mode=edit" class="btn btn-warning btn-sm">編集</a>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="7" class="text-center text-danger">データの取得中にエラーが発生しました: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/..." crossorigin="anonymous"></script>
    </body>
</html>