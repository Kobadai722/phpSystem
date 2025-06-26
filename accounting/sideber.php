<?php
// sidebar.phpを読み込む前に $current_page が定義されていること
$current_page = $current_page ?? '';
?>
<!-- サイドバー本体 -->
<nav class="sidebar">
    <!-- 開閉ボタン -->
    <div class="sidebar-toggle">
        <button id="sidebar-toggle-btn" aria-label="サイドバーを開閉する">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- メニューリスト -->
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>" href="/accounting/a_main.php">
                <div class="nav-link-content">
                    <i class="bi bi-house-door-fill"></i>
                    <span class="nav-link-text">Home</span>
                </div>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'list') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/output_siwakehyo.php">
                <div class="nav-link-content">
                    <i class="bi bi-journals"></i>
                    <span class="nav-link-text">仕訳一覧表示</span>
                </div>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'input') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/input_siwakehyo.php">
                <div class="nav-link-content">
                    <i class="bi bi-journal-text"></i>
                    <span class="nav-link-text">仕訳入力フォーム</span>
                </div>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'graph') ? 'active' : ''; ?>" href="#">
                <div class="nav-link-content">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span class="nav-link-text">グラフでも作ろうかな</span>
                </div>
            </a>
        </li>
    </ul>
</nav>
