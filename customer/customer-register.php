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
                <input type="text" class="form-control" id="name" name="name" required maxlength="50">
                <div class="invalid-feedback">
                    企業名を入力してください。
                </div>
            </div>

            <div class="mb-3">
                <label for="cell_number" class="form-label">電話番号</label>
                <input type="tel" class="form-control" id="cell_number" name="cell_number" maxlength="13">
                <div class="invalid-feedback" id="cell_number_error">
                    正しい形式の電話番号を入力してください。
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
                    <input type="text" class="form-control" id="post_code" name="post_code" required maxlength="8" onkeyup="AjaxZip3.zip2addr(this,'','address','address');">
                    <button type="button" class="btn btn-outline-secondary" onclick="AjaxZip3.zip2addr('post_code','','address','address');">自動入力</button>
                </div>
                <div class="invalid-feedback" id="post_code_error">
                    郵便番号はXXX-XXXXの形式で入力してください。
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        (function() {
            'use strict'

            var form = document.getElementById('customerForm');
            var submitButton = document.getElementById('submitButton');
            var confirmRegisterButton = document.getElementById('confirmRegister');
            var confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            var resultModalEl = document.getElementById('resultModal');
            var resultModal = new bootstrap.Modal(resultModalEl);
            var cellNumberInput = document.getElementById('cell_number');
            var postCodeInput = document.getElementById('post_code');

            // 入力値を半角に変換し、不要な文字を削除する関数
            function formatInput(value) {
                return value.replace(/[ーあ-んA-Za-zＡ-Ｚａ-ｚ０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                }).replace(/[^0-9]/g, '');
            }

            // 電話番号をフォーマットする関数
            cellNumberInput.addEventListener('input', function(e) {
                let value = formatInput(e.target.value);
                if (value.length > 11) value = value.slice(0, 11);

                if (value.length > 3 && value.length <= 7) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                } else if (value.length > 7) {
                    value = value.slice(0, 3) + '-' + value.slice(3, 7) + '-' + value.slice(7);
                }
                e.target.value = value;
            });

            // 郵便番号をフォーマットする関数
            postCodeInput.addEventListener('input', function(e) {
                let value = formatInput(e.target.value);
                if (value.length > 7) value = value.slice(0, 7);

                if (value.length > 3) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                }
                e.target.value = value;
            });

            submitButton.addEventListener('click', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');

                if (form.checkValidity()) {
                    document.getElementById('modalName').textContent = document.getElementById('name').value;
                    document.getElementById('modalCellNumber').textContent = cellNumberInput.value || '未入力';
                    document.getElementById('modalPostCode').textContent = postCodeInput.value;
                    document.getElementById('modalMail').textContent = document.getElementById('mail').value;
                    document.getElementById('modalAddress').textContent = document.getElementById('address').value;
                    confirmModal.show();
                }
            });

            confirmRegisterButton.addEventListener('click', function() {
                const formData = new FormData(form);

                fetch('customer-register-check.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        confirmModal.hide();
                        const resultModalBody = document.getElementById('resultModalBody');
                        if (data.success) {
                            resultModalBody.innerHTML = '<p class="text-success">' + data.message + '</p>';
                            form.reset();
                            form.classList.remove('was-validated');
                        } else {
                            resultModalBody.innerHTML = '<p class="text-danger">' + data.message + '</p>';
                        }
                        resultModal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        confirmModal.hide();
                        const resultModalBody = document.getElementById('resultModalBody');
                        resultModalBody.innerHTML = '<p class="text-danger">登録中にエラーが発生しました。</p>';
                        resultModal.show();
                    });
            });

            resultModalEl.addEventListener('hidden.bs.modal', function (event) {
                const successMessage = resultModalEl.querySelector('.text-success');
                if (successMessage) {
                    window.location.href = 'customer.php';
                }
            });
        })();
    </script>
</body>
</html>