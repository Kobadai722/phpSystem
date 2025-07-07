<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理システム</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/stock_styles.css">
</head>
<?php include '../../header.php'; ?>

<body onload="search()">
    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content">
            <div class="search mt-3">
                <div class="row g-2 w-100 align-items-center"><div class="col-md-auto position-relative">
                        <input type="text" id="searchInput" class="form-control pe-5" placeholder="商品名または商品IDで検索" oninput="toggleClearButton()" onkeydown="if(event.keyCode===13) search()">
                        <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y me-2 p-0" type="button" onclick="clearSearch()" id="clearButton" style="display: none;">
                            <i class="bi bi-x-circle-fill text-muted"></i>
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary" onclick="search()"><i class="bi bi-search me-2"></i>検索</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table id="inventoryTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>商品ID</th>
                            <th>商品名</th>
                            <th>在庫数</th>
                            <th>単価</th>
                            <th>商品区分</th>
                            <th>備考/説明</th> <th>操作</th>
                        </tr>
                    </thead>
                        <tbody>
                            </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="footer">
        </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
    <script src="../js/search.js"></script>
</body>
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">削除の確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>以下の商品を削除してもよろしいですか？</p>
                <p><strong>商品ID: </strong><span id="modalProductId"></span></p>
                <p><strong>商品名: </strong><span id="modalProductName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
                </div>
            </div>
        </div>
    </div>
</html>A