<?php
// sidebar.phpを読み込む前に $current_page が定義されていることを想定
// 未定義の場合にエラーが出ないように、デフォルト値を設定
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
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>" href="/accounting/a_main.php">
                    <i class="bi bi-house-door-fill"></i>
                    <span class="nav-link-text">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'list') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/output_siwakehyo.php">
                    <i class="bi bi-journals"></i>
                    <span class="nav-link-text">仕訳一覧表示</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'input') ? 'active' : ''; ?>" href="/accounting/siwake_hyo/input_siwakehyo.php">
                    <i class="bi bi-journal-text"></i>
                    <span class="nav-link-text">仕訳入力フォーム</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'graph') ? 'active' : ''; ?>" href="#">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span class="nav-link-text">グラフでも作ろうかな</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
