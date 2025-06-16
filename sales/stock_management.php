<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理・追加</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="stock_styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <main>
        <?php include 'localNavigation.php'; ?>
        
        <section class="content">
        <div class="table-responsive">
            <table class="table table-border table-hover table-smaller">
                <thead>
                    <tr>
                        <th scope="col">商品ID</th>
                        <th scope="col">商品名</th>
                        <th scope="col">単価</th>
                        <th scope="col">在庫数</th>
                        <th scope="col">商品区分</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
            </table>
        </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
</body>
<script src="inventory.js"></script>
</html>