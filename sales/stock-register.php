<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい商品の追加</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    </head>
<body>
    <?php include '../header.php'; ?>
    <main>
        <?php include 'localNavigation.php'; ?>
        
        <section class="content py-4"> <div class="container">
                <h2 class="mb-4">新しい商品の追加</h2>
                
                <form action="add_product_process.php" method="POST">
                    
                    <div class="mb-3">
                        <label for="product_name" class="form-label">商品名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required maxlength="100">
                        <div class="form-text text-muted">最大100文字まで</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">単価 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="unit_price" name="unit_price" required min="0" step="1">
                        <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">初期在庫 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" required min="0" step="1">
                        <div class="form-text text-muted">0以上の整数を入力してください</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">商品区分ID <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="category_id" name="category_id" required min="1" placeholder="例: 1, 2, 3">
                        <div class="form-text text-muted">対応する商品区分IDを入力してください</div>
                    </div>

                    <div class="mb-3">
                        <label for="product_category" class="form-label">商品区分 <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_category" name="product_category" required>
                            <option value="">選択してください</option>
                            <option value="文房具">文房具</option>
                            <option value="食品">食品</option>
                            <option value="飲料">飲料</option>
                            <option value="その他">その他</option>
                            </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-save me-2"></i>登録する
                        </button>
                        <a href="stock_management.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
</body>
</html>