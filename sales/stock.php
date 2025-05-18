<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" 
        crossorigin="anonymous">
        <title>在庫管理システム</title>
        <link rel="stylesheet" href="stock_styles.css">
    </head>
    <body>
        <header class="header">
            <h1>在庫管理システム</h1>
        </header>
        
        
        <div class="globalNavigation"><!-- ここにグローバルナビゲーションを創る -->
            <p>グローバルナビゲーション</p>
        </div>

        <main>
            <nav class="localNavigation"> 
                <ul>
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">在庫一覧</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">入出庫管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">発注管理</a></li>
                </ul>
            </nav>

            <section class="content">
                <section class="search"><!-- コンテンツをグループ化 -->
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="商品名または商品IDで検索">
                        <button class="btn btn-primary" type="submit">検索</button>
                    </div>
                </section>
                    <table class="table table-striped table-bordered">
                        <thead><!-- 表の ヘッダー部分 を表す要素 -->
                            <tr>
                                <th>商品名</th>
                                <th>単価</th>
                                <th>在庫数</th>
                                <th>商品ID</th>
                                <th>商品区分</th>
                            </tr>
                        </thead>
                            <!-- theadタグとtbodyタグについてですね。 これは表の見出し部分と本体部分を区別するためのタグなんだよ。例えば、テーブルに複数の行がある場合、
                                theadタグによって表の上端の1行目を見出し部分として指定することができる。それに対して、tbodyタグはその下に続く行を本体部分として指定するためのタグだよ。 -->
                        <tbody><!-- 表の一連の行（ <tr> 要素）を内包し、その部分が表（ <table> ）の本体部分を構成することを表します -->
                            <!-- 在庫データの表をここに表示 -->
                        </tbody>
                    </table>
            </section>
        </main>
        <footer class="footer">
            <a href="./../main.php">メインに戻る
        <foote>  
        
    </body>


</html>