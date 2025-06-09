<?php
session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>顧客情報登録</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <script src="js/ajaxzip3.js" charset="UTF-8"></script>
    <link href="../style.css" rel="stylesheet" />
    <style>

        .form-label {
            font-weight: bold;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<?php include '../header.php'; ?>
<body>
    <main class="container">
        <h2 class="my-4">顧客情報登録</h2>
        <form action="check.php" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">氏名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="10">
                <div class="invalid-feedback">
                    氏名を入力してください。
                </div>
            </div>

            <div class="mb-3">
                <label for="cell_number" class="form-label">電話番号</label>
                <input type="tel" class="form-control" id="cell_number" name="cell_number" maxlength="20" pattern="[0-9]{10,20}" title="電話番号は数字のみで入力してください（10桁以上20桁以内）。">
                <div class="invalid-feedback">
                    有効な電話番号を入力してください。
                </div>
            </div>

            <div class="mb-3">
                <label for="mail" class="form-label">メールアドレス <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="mail" name="mail" required maxlength="64">
                <div class="invalid-feedback">
                    有効なメールアドレスを入力してください。
                </div>
            </div>

            <div class="mb-3">
                <label for="post_code" class="form-label">郵便番号 <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="post_code" name="post_code" required maxlength="7" onKeyUp="AjaxZip3.zip2addr(this,'','address','address');">
                    <button type="button" class="btn btn-outline-secondary" onclick="AjaxZip3.zip2addr('post_code','','address','address');">自動入力</button>
                    <div class="invalid-feedback">
                        郵便番号を入力してください。
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">住所 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address" required maxlength="64">
                <div class="invalid-feedback">
                    住所を入力してください。
                </div>
            </div>

            <button type="submit" class="btn btn-primary">登録</button>
        </form>
    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Bootstrapのバリデーションスクリプト
    (function () {
        'use strict'

        // 全てのカスタムバリデーションを適用するフォームを取得
        var forms = document.querySelectorAll('.needs-validation')

        // ループして、各フォームにバリデーションを適用
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
</html>