<?php
session_start();
require_once '../config.php';

// 検索条件の取得
$search_id = $_GET['customer_id'] ?? '';
$search_name = $_GET['name'] ?? '';
$search_tel = $_GET['cell_number'] ?? '';
$search_mail = $_GET['mail'] ?? '';

// ベースとなるSQL
$sql = "SELECT * FROM CUSTOMER WHERE 1=1";
$params = [];

// 検索条件の組み立て
if (!empty($search_id)) {
    $sql .= " AND CUSTOMER_ID = ?";
    $params[] = $search_id;
}
if (!empty($search_name)) {
    $sql .= " AND NAME LIKE ?";
    $params[] = '%' . $search_name . '%';
}
if (!empty($search_tel)) {
    $sql .= " AND CELL_NUMBER LIKE ?";
    $params[] = '%' . $search_tel . '%';
}
if (!empty($search_mail)) {
    $sql .= " AND MAIL LIKE ?";
    $params[] = '%' . $search_mail . '%';
}

// ページネーション設定
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// 総件数を取得
$count_sql = preg_replace('/SELECT \* FROM/', 'SELECT COUNT(*) FROM', $sql);
$count_stmt = $PDO->prepare($count_sql);
$count_stmt->execute($params);
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

// 表示データの取得
$sql .= " LIMIT :start, :limit";
$stmt = $PDO->prepare($sql);
// パラメータのバインド
foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
    <style>
        .table tbody { display: block; height: 720px; overflow-y: auto; }
        .table thead, .table tbody tr { display: table; width: 100%; table-layout: fixed; }
        .table td { word-wrap: break-word; word-break: break-all; }
    </style>
</head>
<?php include '../header.php'; ?>
<body>
    <main>
        <h2>顧客一覧</h2>
        <div class="text-end">
            <button type="button" class="btn btn-primary mb-4" onclick="location.href='customer-register.php'"><i class="bi bi-plus-lg"></i>追加</button>
        </div>
        
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-2">
                <label for="customer_id" class="form-label">顧客ID</label>
                <input type="text" name="customer_id" id="customer_id" class="form-control" value="<?= htmlspecialchars($search_id) ?>">
            </div>
            <div class="col-md-3">
                <label for="name" class="form-label">企業名</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($search_name) ?>">
            </div>
            <div class="col-md-3">
                <label for="cell_number" class="form-label">電話番号</label>
                <input type="text" name="cell_number" id="cell_number" class="form-control" value="<?= htmlspecialchars($search_tel) ?>">
            </div>
            <div class="col-md-3">
                <label for="mail" class="form-label">メールアドレス</label>
                <input type="text" name="mail" id="mail" class="form-control" value="<?= htmlspecialchars($search_mail) ?>">
            </div>
            <div class="col-md-1 align-self-end">
                <button type="submit" class="btn btn-primary">検索</button>
            </div>
        </form>
        <hr>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">顧客ID</th>
                    <th scope="col">氏名</th>
                    <th scope="col">電話番号</th>
                    <th scope="col">メールアドレス</th>
                    <th scope="col">郵便番号</th>
                    <th scope="col">住所</th>
                    <th scope="col">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td scope="row"><?= htmlspecialchars($row['CUSTOMER_ID']) ?></td>
                        <td><?= htmlspecialchars($row['NAME']) ?></td>
                        <td><?= htmlspecialchars($row['CELL_NUMBER']) ?></td>
                        <td><?= htmlspecialchars($row['MAIL']) ?></td>
                        <td><?= htmlspecialchars($row['POST_CODE']) ?></td>
                        <td><?= htmlspecialchars($row['ADDRESS']) ?></td>
                        <td>
                            <a href="customer-edit.php?id=<?= $row['CUSTOMER_ID'] ?>" class="btn btn-primary btn-sm">編集</a>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= htmlspecialchars($row['CUSTOMER_ID']) ?>" data-name="<?= htmlspecialchars($row['NAME']) ?>">
                                削除
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>&amp;customer_id=<?= htmlspecialchars($search_id) ?>&amp;name=<?= htmlspecialchars($search_name) ?>&amp;cell_number=<?= htmlspecialchars($search_tel) ?>&amp;mail=<?= htmlspecialchars($search_mail) ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </main>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">削除の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">本当に <strong id="customerNameToDelete"></strong> を削除しますか？</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <form id="deleteForm" action="customer-delete.php" method="post">
                        <input type="hidden" name="customer_id" id="customerIdToDelete">
                        <button type="submit" class="btn btn-danger">削除</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var customerId = button.getAttribute('data-id');
            var customerName = button.getAttribute('data-name');
            deleteModal.querySelector('#customerNameToDelete').textContent = customerName;
            deleteModal.querySelector('#customerIdToDelete').value = customerId;
        });
    </script>
</body>
</html>