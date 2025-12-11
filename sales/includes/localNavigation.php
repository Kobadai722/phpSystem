<nav class="localNavigation">
    <ul>
        <li class="nav-item">
            <a class="nav-link" href="/main.php">
                <i class="bi bi-house-door-fill"></i> Home
            </a>
        </li>

        <li class="nav-item dropdown dropdown-center">
            <a class="nav-link dropdown-toggle" href="#" id="stockDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-box-seam"></i> 在庫管理
            </a>
            <ul class="dropdown-menu" aria-labelledby="stockDropdown">
                <li><a class="dropdown-item" href="/../sales/views/stock.php">商品一覧</a></li>
                <li><a class="dropdown-item" href="/../sales/views/stock-register.php">在庫追加</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown dropdown-center">
            <a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-box-seam"></i> 売上管理
            </a>
            <ul class="dropdown-menu" aria-labelledby="salesDropdown">
                <li><a class="dropdown-item" href="/../sales/views/management.php">ダッシュボード</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown dropdown-center">
            <a class="nav-link dropdown-toggle" href="#" id="orderDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-box-seam"></i> 注文管理
            </a>
            <ul class="dropdown-menu" aria-labelledby="orderDropdown">
                <li><a class="dropdown-item" href="/../sales/views/purchase.php">注文一覧</a></li>
                <li><a class="dropdown-item" href="/../sales/views/order_add.php">注文追加</a></li>
            </ul>
        </li>
    </ul>
</nav>
