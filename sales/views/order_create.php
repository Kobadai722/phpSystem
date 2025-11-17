<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>売上登録 | 在庫管理システム</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/stock_styles.css">
</head>

<body>
    <?php include '../../header.php'; ?>

    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content mt-3">

            <h3 class="mb-3">売上登録</h3>

            <!-- 登録フォーム -->
            <div class="card p-4 shadow-sm" style="max-width: 500px;">
                <form action="../actions/order_store.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label">商品ID</label>
                        <input type="number" class="form-control" name="ORDER_TARGET_ID" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">金額</label>
                        <input type="number" class="form-control" name="PRICE" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">担当者ID</label>
                        <input type="number" class="form-control" name="EMPLOYEE_ID" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-2"></i>登録する
                    </button>

                </form>
            </div>

        </section>
    </main>

    <footer class="footer"></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>
</html>
