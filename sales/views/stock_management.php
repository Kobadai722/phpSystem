<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在庫管理・追加</title>

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
        
        <section class="content">
            <div class="search mt-3">
                <div class="row g-2 w-100 align-items-center">
                    <div class="col-md-auto position-relative">
                        <input type="text" id="searchInput" class="form-control pe-5" placeholder="商品名または商品IDで検索" oninput="toggleClearButton()">
                        <button type="button" class="btn btn-sm btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2"
                            id="clearButton" onclick="clearSearch()" style="display: none;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                        <div class="col-md-auto">
                            <button class="btn btn-primary search-btn" type="button" onclick="search()">
                            <i class="bi bi-search me-2"></i>検索
                            </button>
                        </div>
                    < class="col"></
                <div class="col-md-auto text-end">
                    </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mb-3">
            <a href="stock-register.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>商品追加
            </a>
        </div>

        <div class="table-responsive">
                <table class="table table-border table-hover table-smaller">
                    <thead>
                        <tr>
                            <th scope="col">商品ID</th>
                            <th scope="col">商品名</th>
                            <th scope="col">在庫数</th>
                            <th scope="col">単価</th>
                            <th scope="col">商品区分</th> 
                            <th scope="col"></th> 
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </section>
    </main>
    
</body>
<div class="modal fade modal-lg" id="addConfirmModal" tabindex="-1" aria-labelledby="addConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addConfirmModalLabel">商品情報の変更</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="my-4">変更する商品の詳細情報を入力してください。</h5>
                <input type="hidden" id="editProductId" name="product_id">

                <div class="mb-3">
                    <label for="currentProductName" class="form-label">現在の商品の名前:</label>
                    <span id="currentProductName" class="form-control-plaintext"></span>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">新しい商品名 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required maxlength="20">
                    <div class="invalid-feedback">
                        商品名を入力してください
                    </div>
                </div>

                <div class="mb-3">
                    <label for="currentStockQuantity" class="form-label">現在の在庫数:</label>
                    <span id="currentStockQuantity" class="form-control-plaintext"></span>
                </div>
                <div class="mb-3">
                    <label for="stockQuantity" class="form-label">新しい在庫数 <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stockQuantity" name="stockQuantity" required min="0">
                    <div class="invalid-feedback">
                        在庫数を入力してください
                    </div>
                </div>

                <div class="mb-3">
                    <label for="currentUnitPrice" class="form-label">現在の単価:</label>
                    <span id="currentUnitPrice" class="form-control-plaintext"></span>
                </div>
                <div class="mb-3">
                    <label for="unitPrice" class="form-label">新しい単価 <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="unitPrice" name="unitPrice" required min="0" step="0.01">
                    <div class="invalid-feedback">
                        単価を入力してください
                    </div>
                </div>

                <div class="mb-3">
                    <label for="currentProductCategory" class="form-label">現在の商品区分:</label>
                    <span id="currentProductCategory" class="form-control-plaintext"></span>
                </div>
                <div class="mb-3">
                    <label for="productCategory" class="form-label">新しい商品区分 <span class="text-danger">*</span></label>
                    <select class="form-select" id="productCategory" name="productCategory" required>
                        <option value="">選択してください</option>
                        <option value="1">作業用品</option>
                        <option value="2">オフィス用品</option>
                        <option value="3">医療・衛生用品</option>
                        <option value="4">IT・デジタル機器</option>
                        <option value="5">建築・土木資材</option>
                    </select>
                    <div class="invalid-feedback">
                        商品区分を選択してください
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="saveConfirmButton">保存</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script src="../js/inventory.js"></script>
</html>