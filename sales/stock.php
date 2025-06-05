<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"><!-- アイコンのブートストラップ -->
        <title>在庫管理システム</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="stock_styles.css">
    </head>
    <?php include '../header.php'; ?>
    <body>
        <main>
            <nav class="localNavigation"> 
                <ul >
                    <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-house-door-fill"></i> Home</a>
                    </li>
                        <li class="nav-item dropdown dropdown-center">
                        <a class="nav-link dropdown-toggle" href="#" id="stockDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam"></i> 在庫管理
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="stockDropdown">
                            <li><a class="dropdown-item" href="#">商品一覧</a></li>
                            <li><a class="dropdown-item" href="#">在庫追加</a></li>
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

            <section class="content">
                <section class="search mt-3"><!-- コンテンツをグループ化 -->
                        <form method="POST" action="search.php" class="d-flex">
                        <input type="text" name="keyword" class="form-control" placeholder="商品名または商品IDで検索" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                        <button class="btn btn-primary search-btn" type="submit" style="white-space: nowrap;">検索</button>
                        </form>
                </section>
                        
                <div class="table-responsive">
                    <table class="table  table-border table-hover table-smaller">
                        <thead><!-- 表の ヘッダー部分 を表す要素 -->
                            <tr class="#">
                                <th scope="col">商品ID</th>
                                <th scope="col">商品名</th>
                                <th scope="col">単価</th>
                                <th scope="col">在庫数</th>
                                <th scope="col">商品区分</th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php
                                    require_once '../config.php';
                                    $sql = " SELECT P.PRODUCT_ID,P.PRODUCT_NAME,P.UNIT_SELLING_PRICE,S.STOCK_QUANTITY,K.PRODUCT_KUBUN_NAME
                                    FROM PRODUCT P
                                    LEFT JOIN STOCK S ON P.PRODUCT_ID = S.PRODUCT_ID
                                    LEFT JOIN PRODUCT_KUBUN K ON P.PRODUCT_KUBUN_ID = K.PRODUCT_KUBUN_ID";
                                    if (!empty($keyword)) {
                                        $sql .= " WHERE P.PRODUCT_ID LIKE :keyword OR P.PRODUCT_NAME LIKE :keyword";
                                        }

                                    $stmt = $PDO->prepare($sql);

                                    if (!empty($keyword)) {
                                        $stmt->bindValue(':keyword', '%' . $keyword . '%');
                                        }
                                        $stmt->execute();

                                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($results as $row) {
                                ?>
                                
                                <tr>
                                <td scope="row"><?=$row['PRODUCT_ID']?></td>
                                <td><?=$row['PRODUCT_NAME']?></td>
                                <td><?=$row['UNIT_SELLING_PRICE']?></td>
                                <td><?=$row['STOCK_QUANTITY']?></td>
                                <td><?=$row['PRODUCT_KUBUN_NAME']?></td>
                                </tr>
                            <?php
                                }
                            ?>
                            
                            <!-- theadタグとtbodyタグについてですね。 これは表の見出し部分と本体部分を区別するためのタグなんだよ。例えば、テーブルに複数の行がある場合、
                                theadタグによって表の上端の1行目を見出し部分として指定することができる。それに対して、tbodyタグはその下に続く行を本体部分として指定するためのタグだよ。 -->
                        <!-- 表の一連の行（ <tr> 要素）を内包し、その部分が表（ <table> ）の本体部分を構成することを表します -->
                            <!-- 在庫データの表をここに表示 -->
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
        <footer class="footer">
            
        </footer>  
        
    </body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
</script>
</html>
