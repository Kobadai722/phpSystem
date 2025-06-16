<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理システム</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="stock_styles.css">
</head>
<?php include '../header.php'; ?>

<body onload="search()">
    <main>
        <?php include 'localNavigation.php'; ?>

        <section class="content">
            <div class="search mt-3">
                <div class="row g-2 w-100 align-items-center"><div class="col-md-auto position-relative">
                        <input type="text" id="searchInput" class="form-control pe-5" placeholder="商品名または商品IDで検索" oninput="toggleClearButton()">
                        <button type="button" class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2"
                        id="clearButton" onclick="clearSearch()" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-primary search-btn" type="button" onclick="search()">
                            <i class="bi bi-search me-2"></i>検索
                        </button>
                    </div>
                    <div class="col"></div>
                    <div class="col-md-auto text-end"><button onclick="location.href='/sales/stock_management.php'" class="btn btn-outline-secondary btn-sm me-3">在庫管理</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-border table-hover table-smaller">
                    <thead>
                        <tr>
                            <th scope="col">商品ID</th>
                            <th scope="col">商品名</th>
                            <th scope="col">単価</th>
                            <th scope="col">在庫数</th>
                            <th scope="col">商品区分</th>
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
    <script src="search.js"></script>
</body>
</html>