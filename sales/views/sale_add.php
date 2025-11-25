<?php

require_once '../../config.php'; 

// 商品リスト取得
$products = [];
try {
    $stmt = $PDO->prepare("SELECT PRODUCT_ID, PRODUCT_NAME, UNIT_SELLING_PRICE FROM PRODUCT ORDER BY PRODUCT_ID");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log("商品取得エラー: " . $e->getMessage());
}

// 担当者リスト取得
$employees = [];
try {
    $stmtEmployee = $PDO->prepare("SELECT EMPLOYEE_ID, NAME FROM EMPLOYEE ORDER BY EMPLOYEE_ID");
    $stmtEmployee->execute();
    $employees = $stmtEmployee->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $employees = [];
    error_log("担当者取得エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規売上登録</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">

</head>
<body>

<?php include '../../header.php'; ?>
<?php include '../includes/localNavigation.php'; ?>

<main class="content py-4">
    <div class="container">
        <h2 class="mb-4">新規売上登録</h2>

        <form id="orderAddForm" method="POST" novalidate>

            <!-- 商品選択 -->
            <div class="mb-3">
                <label for="product_id" class="form-label">商品</label>
                <select class="form-select" id="product_id" name="product_id" required>
                    <option value="">選択してください</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['PRODUCT_ID'] ?>"
                            data-name="<?= htmlspecialchars($p['PRODUCT_NAME']) ?>"
                            data-price="<?= htmlspecialchars($p['UNIT_SELLING_PRICE']) ?>"
                        >
                            <?= htmlspecialchars($p['PRODUCT_NAME']) ?>
                            (ID: <?= $p['PRODUCT_ID'] ?> / <?= number_format($p['UNIT_SELLING_PRICE']) ?>円)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">商品を選択してください。</div>
            </div>

            <!-- 数量 -->
            <div class="mb-3">
                <label for="order_quantity" class="form-label">数量</label>
                <input type="number" class="form-control" id="order_quantity" name="order_quantity" required min="1" value="1">
                <div class="invalid-feedback">数量を入力してください。</div>
            </div>

            <!-- 顧客ID -->
            <div class="mb-3">
                <label for="customer_id" class="form-label">顧客ID</label>
                <input type="number" class="form-control" id="customer_id" name="customer_id" required min="1">
                <div class="invalid-feedback">顧客IDを入力してください。</div>
            </div>

            <!-- 担当者 -->
            <div class="mb-3">
                <label for="employee_id" class="form-label">担当者</label>
                <select class="form-select" id="employee_id" name="employee_id" required>
                    <option value="">選択してください</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['EMPLOYEE_ID'] ?>"
                            data-id="<?= $e['EMPLOYEE_ID'] ?>"
                            data-name="<?= htmlspecialchars($e['NAME']) ?>"
                        >
                            <?= htmlspecialchars($e['NAME']) ?> (ID: <?= $e['EMPLOYEE_ID'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">担当者を選択してください。</div>
            </div>

            <!-- 備考（DBには入れない） -->
            <div class="mb-3">
                <label for="notes" class="form-label">備考</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-success btn-lg">売上を登録</button>
                <a href="management.php" class="btn btn-secondary btn-lg">戻る</a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("orderAddForm");

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }

        const formData = new FormData(form);

        // ★ 修正：add_sale_api.php に送信
        fetch("add_sale_api.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                window.location.href = "management.php";
            } else {
                alert("登録失敗: " + result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("通信エラーが発生しました。");
        });
    });

});
</script>

</body>
</html>
