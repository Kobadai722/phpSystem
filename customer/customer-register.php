<?php
session_start();
require_once '../config.php'; // データベース接続情報
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
    <link href="customer.css" rel="stylesheet" />
</head>
<?php include '../header.php'; ?>
<body>
    <main class="container">
        <h2 class="my-4">顧客情報登録</h2>
        <form id="customerForm" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">企業名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="10">
                <div class="invalid-feedback">
                    企業名を入力してください。
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

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal" id="submitButton">登録</button>
        </form>
    </main>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">入力内容の確認</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>以下の内容で登録します。よろしいですか？</p>
                    <p><strong>企業名:</strong> <span id="modalName"></span></p>
                    <p><strong>電話番号:</strong> <span id="modalCellNumber"></span></p>
                    <p><strong>メールアドレス:</strong> <span id="modalMail"></span></p>
                    <p><strong>郵便番号:</strong> <span id="modalPostCode"></span></p>
                    <p><strong>住所:</strong> <span id="modalAddress"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="confirmRegister">登録</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">登録結果</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Bootstrapのバリデーションスクリプト
    (function () {
        'use strict'

        var form = document.querySelector('.needs-validation');
        var submitButton = document.getElementById('submitButton');
        var confirmRegisterButton = document.getElementById('confirmRegister');
        var confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        var resultModal = new bootstrap.Modal(document.getElementById('resultModal')); // 追加

        submitButton.addEventListener('click', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                document.getElementById('modalName').textContent = document.getElementById('name').value;
                document.getElementById('modalCellNumber').textContent = document.getElementById('cell_number').value || '未入力';
                document.getElementById('modalMail').textContent = document.getElementById('mail').value;
                document.getElementById('modalPostCode').textContent = document.getElementById('post_code').value;
                document.getElementById('modalAddress').textContent = document.getElementById('address').value;

                confirmModal.show();
            }
        });

        confirmRegisterButton.addEventListener('click', function () {
            // FormDataオブジェクトを作成してフォームデータを取得
            const formData = new FormData(form);

            // 送信先を customer-register-check.php に変更
            fetch('customer-register-check.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // JSONレスポンスをパース
            .then(data => {
                confirmModal.hide(); // 確認モーダルを閉じる
                const resultModalBody = document.getElementById('resultModalBody');
                if (data.success) {
                    resultModalBody.innerHTML = '<p class="text-success">' + data.message + '</p>';
                    form.reset(); // フォームをリセット
                    form.classList.remove('was-validated'); // バリデーション状態をリセット
                } else {
                    resultModalBody.innerHTML = '<p class="text-danger">' + data.message + '</p>';
                }
                resultModal.show(); // 結果モーダルを表示
            })
            .catch(error => {
                console.error('Error:', error);
                confirmModal.hide();
                const resultModalBody = document.getElementById('resultModalBody');
                resultModalBody.innerHTML = '<p class="text-danger">登録中にエラーが発生しました。</p>';
                resultModal.show();
            });
        });

        form.classList.remove('was-validated');

    })()
</script>
</html>