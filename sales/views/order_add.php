<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しい注文の追加</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>
        
        <section class="content py-4">
            <div class="container">
                <h2 class="mb-4">新しい注文の追加</h2>
                
                <form id="orderAddForm" method="POST"> 
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">商品名 (PRODUCT_ID)</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">選択してください</option>
                            <option value="1">商品A (金額: )</option>
                            <option value="2">商品B (金額: )</option>
                            <option value="3">商品C (金額: )</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_quantity" class="form-label">注文数量</label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1" value="1">
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">顧客ID</label>
                        <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" step="1" value="1">
                        <div class="form-text text-muted">有効な顧客IDを入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">備考</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="255"></textarea>
                        <div class="form-text text-muted">最大255文字まで</div>
                    </div>
                    

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="submitFormBtn"> 
                            <i class="bi bi-cart-plus me-2"></i>注文を登録する
                        </button>
                        <a href="order_management.php" class="btn btn-secondary btn-lg">
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
    <script>
        document.getElementById('orderAddForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const form = event.currentTarget;
            const btn = document.getElementById('submitFormBtn');
            
            // HTML5の必須入力チェック（残します）
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '処理中...';

            const formData = new FormData(form);

            // try/catchを削除した最小限のFetch API処理
            const response = await fetch('../api/add_order_api.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                alert('成功: ' + data.message);
                form.reset();
                form.classList.remove('was-validated');
            } else {
                alert('失敗: ' + data.message);
            }

            // 終了処理
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>注文を登録する';
        });
    </script>
</body>
</html>