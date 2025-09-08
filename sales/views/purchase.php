<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文管理システム - 注文一覧</title>
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
                <h1 class="mb-4">注文一覧</h1>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">検索・フィルタリング</h5>
                        <form class="row g-3 align-items-end" id="searchForm">
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
                                    <option value="発送済み">発送済み</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="searchButton">
                                    <i class="bi bi-search"></i> 検索
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2" id="resetButton">
                                    <i class="bi bi-arrow-counterclockwise"></i> リセット
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <a href="order_add.php?mode=new" class="btn btn-success" id="newOrderButton">
                        <i class="bi bi-plus-circle"></i> 新規注文登録
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered bg-white shadow-sm rounded">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">注文ID</th>
                                <th scope="col">注文日時</th>
                                <th scope="col">顧客名</th>
                                <th scope="col">合計金額</th>
                                <th scope="col">支払い状況</th>
                                <th scope="col">配送状況</th>
                                <th scope="col">操作</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/orders.js"></script>
</body>
</html>