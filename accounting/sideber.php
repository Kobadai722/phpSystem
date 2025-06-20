<!-- 左側: サイドバー -->
<?php
// sidebar.phpを読み込む前に $current_page が定義されていることを想定
// 未定義の場合にエラーが出ないように、デフォルト値を設定
$current_page = $current_page ?? '';
?>
<nav class="sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <!-- $current_pageが'home'ならactiveクラスを、さもなくば空文字を出力 -->
                <a class="nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>" href="/phpSystem/main.php">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'list') ? 'active' : ''; ?>" href="/phpSystem/accounting/siwake_hyo/output_siwakehyo.php">
                    <i class="bi bi-journals"></i> 仕訳一覧表示
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'input') ? 'active' : ''; ?>" href="/phpSystem/accounting/siwake_hyo/input_siwakehyo.php">
                    <i class="bi bi-journal-text"></i> 仕訳入力フォーム
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'graph') ? 'active' : ''; ?>" href="#">
                    <i class="bi bi-bar-chart-line-fill"></i> グラフでも作ろうかな
                </a>
            </li>
        </ul>
    </div>
</nav>
