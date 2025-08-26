<?php
// このファイルを読み込む前に $current_page が定義されていることを想定
$current_page = $current_page ?? '';
?>

<!-- Bootstrap Offcanvas Component -->
<div class="offcanvas offcanvas-start sidebar-custom" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    
    <!-- サイドバーのヘッダー（タイトルと閉じるボタン） -->
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">メニュー</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="閉じる"></button>
    </div>
    
    <!-- サイドバーの本体（ナビゲーションリンク） -->
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>" href="/accounting/a_main.php">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'list') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/output_siwakehyo.php">
                    <i class="bi bi-journals"></i>
                    <span>仕訳一覧表示</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'input') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/input_siwakehyo.php">
                    <i class="bi bi-journal-text"></i>
                    <span>仕訳入力フォーム</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'graph') ? 'active' : ''; ?>" href="/accounting/graph/sale_list.php">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>売上高一覧</span>
                </a>
            </li>
        </ul>
    </div>
</div>
