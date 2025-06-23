<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include 'localNavigation.php'; ?>

        <section class="content">
            <div class="bg-white p-3 shadow-sm rounded mb-3">
                <div class="row g-2">
                    <div class="col-md-2">
                        <select class="form-select form-select-sm">
                            <option>担当者</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm">
                            <option>株式会社サンプル</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="請求月">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="案件No | 名称">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm">
                            <option>部門</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-outline-secondary btn-sm">詳細検索</button>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" placeholder="納品日From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" placeholder="納品日To">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="ステータス">
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-primary btn-sm">条件リセット</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
</body>
</html>