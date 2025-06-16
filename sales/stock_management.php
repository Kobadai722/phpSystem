<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理追加・削除</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <main>
        <nav class="localNavigation">
            <ul>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-house-door-fill"></i> Home</a>
                </li>
                <li class="nav-item dropdown dropdown-center">
                    <a class="nav-link dropdown-toggle" href="#" id="stockDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-box-seam"></i> 在庫管理
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="stockDropdown">
                        <li><a class="dropdown-item" href="#">商品一覧</a></li>
                        <li><a class="dropdown-item" href="/../sales/stock_management.php">在庫追加</a></li>
                        <li><a class="dropdown-item" href="#">在庫履歴</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-bar-chart-line-fill"></i> 売上管理</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-cart-check-fill"></i> 発注管理</a>
                </li>
            </ul>
        </nav>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
</body>
</html>