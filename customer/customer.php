<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../style.css" rel="stylesheet" />
    <link href="customer.css" rel="stylesheet" />
    <style>
        /* ページネーションで高さが変わらないようにテーブルの高さを固定 */
        .table tbody {
            display: block;
            height: 720px; /* 20行分の目安の高さ */
            overflow-y: auto;
        }
        .table thead, .table tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
    </style>
</head>
<?php include '../header.php'; ?>
<body>
    <main>
        <h2>顧客一覧</h2>
        <div class="text-end">
            <button type="button" class="btn btn-primary mb-4" onclick="location.href='customer-register.php'"><i class="bi bi-plus-lg"></i>追加</button>
        </div>
        <div class="accordion mb-4" id="accordionExample">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="bi bi-search"></i>検索
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="gray-box">
                            <form action="customer-output.php" method="post" class="mb-4">
                                <div class="mb-2">
                                    <label for="customerid" class="form-label">顧客ID</label>
                                    <input type="text" name="customerid" id="customerid" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label for="name" class="form-label">氏名</label>
                                    <input type="text" name="name" id="name" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label for="cell_number" class="form-label">電話番号</label>
                                    <input type="text" name="cell_number" id="cell_number" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label for="mail" class="form-label">メールアドレス</label>
                                    <input type="text" name="mail" id="mail" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label for="post_code" class="form-label">郵便番号</label>
                                    <input type="text" name="post_code" id="post_code" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">住所</label>
                                    <input type="text" name="address" id="address" class="form-control">
                                </div>
                                <input type="submit" value="検索" class="btn btn-primary">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">顧客ID</th>
                    <th scope="col">氏名</th>
                    <th scope="col">電話番号</th>
                    <th scope="col">メールアドレス</th>
                    <th scope="col">郵便番号</th>
                    <th scope="col">住所</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../config.php';
                $limit = 20;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $start = ($page - 1) * $limit;

                $count_stmt = $PDO->query("SELECT COUNT(*) FROM CUSTOMER");
                $total_results = $count_stmt->fetchColumn();
                $total_pages = ceil($total_results / $limit);

                $stmt = $PDO->prepare("SELECT * FROM CUSTOMER LIMIT :start, :limit");
                $stmt->bindParam(':start', $start, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($results as $row) {
                ?>
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
                <?php
                }
                ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="customer.php?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </main>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">削除の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    本当に <strong id="customerNameToDelete"></strong> を削除しますか？
                </div>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var customerId = button.getAttribute('data-id');
            var customerName = button.getAttribute('data-name');
            var modalBodyName = deleteModal.querySelector('#customerNameToDelete');
            var modalInputId = deleteModal.querySelector('#customerIdToDelete');

            modalBodyName.textContent = customerName;
            modalInputId.value = customerId;
        });
    </script>
</body>
</html>