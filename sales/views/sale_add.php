<?php
// sale_add.php

require_once '../../config.php'; 

// 商品リスト取得処理
$products = [];
try {
    $stmt = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log("商品リスト取得エラー: " . $e->getMessage());
}

// 担当者リスト取得処理
$employees = [];
try {
    $stmtEmployee = $PDO->prepare("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE ORDER BY EMPLOYEE_ID");
    $stmtEmployee->execute();
    $employees = $stmtEmployee->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $employees = [];
    error_log("担当者リスト取得エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規注文作成</title>
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
                <h2 class="mb-4">新しい注文の作成</h2>

                                <form id="orderAddForm" method="POST" novalidate>

                                        <div class="mb-3">
                        <label for="product_id" class="form-label">商品</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">選択してください</option>
                            <?php foreach ($products as $product): ?>
                                <option 
                                    value="<?php echo htmlspecialchars($product['PRODUCT_ID']); ?>"
                                    data-name="<?php echo htmlspecialchars($product['PRODUCT_NAME']); ?>"
                                    data-price="<?php echo htmlspecialchars($product['UNIT_SELLING_PRICE']); ?>"
                                >
                                    <?php echo htmlspecialchars($product['PRODUCT_NAME']); ?> 
                                    (ID: <?php echo htmlspecialchars($product['PRODUCT_ID']); ?>, 
                                    単価: <?php echo number_format($product['UNIT_SELLING_PRICE']); ?>円)
                                </option>
                            <?php endforeach; ?>
                    </div>

                                        <div class="mb-3">
                        <label for="order_quantity" class="form-label">注文数量</label>
                        <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" step="1" value="1">
                        <div class="invalid-feedback">注文数量を入力してください。</div>
                    </div>

                                        <div class="mb-3">
                        <label for="customer_id" class="form-label">顧客ID</label>
                        <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1" step="1">
                        <div class="form-text text-muted">有効な顧客IDを入力してください。</div>
                        <div class="invalid-feedback">顧客IDを入力してください。</div>
                    </div>

                                        <div class="mb-3">
                        <label for="employee_id" class="form-label">担当者</label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">選択してください</option>
                            <?php foreach ($employees as $emp): ?>
                                <option
                                    value="<?php echo htmlspecialchars($emp['EMPLOYEE_ID']); ?>"
                                    data-id="<?php echo htmlspecialchars($emp['EMPLOYEE_ID']); ?>"
                                    data-name="<?php echo htmlspecialchars($emp['NAME']); ?>"
                                >
                                    <?php echo htmlspecialchars($emp['NAME']); ?> (ID: <?php echo htmlspecialchars($emp['EMPLOYEE_ID']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">担当者を選択してください。</div>
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
                        <a href="management.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>キャンセル
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>

        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">注文内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>以下の内容で登録します。</p>

                    <table class="table table-bordered">
                        <tr><th>商品名</th><td id="confirmProductName"></td></tr>
                        <tr><th>単価</th><td id="confirmProductPrice"></td></tr>
                        <tr><th>数量</th><td id="confirmQuantity"></td></tr>
                        <tr><th>小計</th><td id="confirmSubtotal"></td></tr>
                        <tr><th>顧客ID</th><td id="confirmCustomerId"></td></tr>
                        <tr><th>担当者ID</th><td id="confirmEmployeeId"></td></tr>
                        <tr><th>担当者名</th><td id="confirmEmployeeName"></td></tr>
                        <tr><th>備考</th><td id="confirmNotes"></td></tr>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="confirmOrderBtn">注文を確定</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("orderAddForm");
    const productSelect = document.getElementById("product_id");
    const quantityInput = document.getElementById("order_quantity");
    const customerIdInput = document.getElementById("customer_id");
    const employeeSelect = document.getElementById("employee_id");
    const notesInput = document.getElementById("notes");

    const confirmModal = new bootstrap.Modal(document.getElementById("confirmModal"));
    const confirmBtn = document.getElementById("confirmOrderBtn");

    // 登録ボタン押下 → モーダル表示
    form.addEventListener("submit", (event) => {
        event.preventDefault(); // 直接送信しない

        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add("was-validated");
            return;
        }

        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const productName = selectedProduct.dataset.name;
        const unitPrice = Number(selectedProduct.dataset.price);
        const quantity = Number(quantityInput.value);
        const subtotal = unitPrice * quantity;

        const selectedEmployee = employeeSelect.options[employeeSelect.selectedIndex];
        const employeeName = selectedEmployee.dataset.name;
        const employeeId = selectedEmployee.value; // value属性からIDを取得

        // モーダルに値セット
        document.getElementById("confirmProductName").innerText = productName;
        document.getElementById("confirmProductPrice").innerText = unitPrice.toLocaleString() + "円";
        document.getElementById("confirmQuantity").innerText = quantity;
        document.getElementById("confirmSubtotal").innerText = subtotal.toLocaleString() + "円";
        document.getElementById("confirmCustomerId").innerText = customerIdInput.value;
        document.getElementById("confirmEmployeeId").innerText = employeeId;
        document.getElementById("confirmEmployeeName").innerText = employeeName;
        document.getElementById("confirmNotes").innerText = notesInput.value || "(なし)";

        confirmModal.show();
    });

    // モーダル → 注文確定
    confirmBtn.addEventListener("click", async () => {

        const formData = new FormData(form);

        try {
            // ★ 修正: fetch先を add_sale_api.php に変更
            const response = await fetch("add_sale_api.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert("注文を登録しました！");
                window.location.href = "management.php";
            } else {
                alert("登録に失敗しました： " + result.message);
            }

        } catch (error) {
            alert("通信エラーが発生しました。");
            console.error(error);
        }
    });

});
    </script>
</body>
</html>