<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>在庫管理システム</title>
    </head>
    <body>
        <header>
            <h1>在庫管理システム</hi>
        </header>

        <nav> 
            <ul> <!-- 順序無しリスト -->
                <li>home</li><!-- ナビゲーション　パンくずリスト的な-->
                <li>在庫一覧</li>
                <li>入出庫管理</li>
                <li>発注管理</li>
            </ul>
        </nav>
        <main>
            <section class="search"><!-- コンテンツをグループ化 -->
                <input type="text" placeholder="商品名または商品IDで検索">
                <button type="submit">検索</button>
            </section>
            <section>
                <table>
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
    </body>
</html>