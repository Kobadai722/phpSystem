<?php
session_start();
require_once '../config.php';

// Handle AJAX registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_customer') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => '不明なエラーが発生しました。'];

    $name = trim($_POST['name'] ?? '');
    $cell_number = trim($_POST['cell_number'] ?? '');
    $mail = trim($_POST['mail'] ?? '');
    $post_code = trim($_POST['post_code'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Server-side validation
    if (empty($name) || empty($mail) || empty($post_code) || empty($address)) {
        $response['message'] = '必須項目 (企業名, メールアドレス, 郵便番号, 住所) が入力されていません。';
        echo json_encode($response);
        exit;
    }
    if (mb_strlen($name) > 10) {
        $response['message'] = '企業名は10文字以内で入力してください。';
        echo json_encode($response);
        exit;
    }
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = '無効なメールアドレスです。';
        echo json_encode($response);
        exit;
    }
    if (!empty($cell_number) && !preg_match('/^[0-9]{10,20}$/', $cell_number)) {
        $response['message'] = '電話番号は10桁から20桁の数字で入力してください。';
        echo json_encode($response);
        exit;
    }
    if (!preg_match('/^[0-9]{7}$/', $post_code)) {
        $response['message'] = '郵便番号は7桁の数字で入力してください。';
        echo json_encode($response);
        exit;
    }
    if (mb_strlen($address) > 64) { // Assuming max length is 64, adjust if different
        $response['message'] = '住所は64文字以内で入力してください。';
        echo json_encode($response);
        exit;
    }


    try {
        $sql = "INSERT INTO CUSTOMER (NAME, CELL_NUMBER, MAIL, POST_CODE, ADDRESS) VALUES (:name, :cell_number, :mail, :post_code, :address)";
        $stmt = $PDO->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':cell_number', $cell_number, PDO::PARAM_STR);
        $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindParam(':post_code', $post_code, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = '顧客情報が正常に登録されました。';
        } else {
            $response['message'] = 'データベースへの登録に失敗しました。';
        }
    } catch (PDOException $e) {
        $response['message'] = 'データベースエラー: ' . $e->getMessage(); // Consider logging detailed errors instead of exposing to client in production
    }
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>顧客情報登録</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <button type="button" class="btn btn-primary" id="confirmRegisterButtonInModal">登録</button> <!-- IDを変更またはこのボタン自体をJSで生成 -->
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

        // 全てのカスタムバリデーションを適用するフォームを取得
        var form = document.querySelector('.needs-validation'); // 1つのフォームなのでquerySelectorを使用
        var submitButton = document.getElementById('submitButton');
        var confirmModalElement = document.getElementById('confirmModal');
        var confirmModalInstance = new bootstrap.Modal(confirmModalElement);
        // var confirmRegisterButton = document.getElementById('confirmRegisterButtonInModal'); // HTMLから初期ボタンを取得する場合
        var modalTitle = confirmModalElement.querySelector('.modal-title');
        var modalBody = confirmModalElement.querySelector('.modal-body');
        var modalFooter = confirmModalElement.querySelector('.modal-footer');

        if (submitButton) { // Ensure submitButton exists
            submitButton.addEventListener('click', function (event) {
            // フォームのバリデーションを実行
            if (!form.checkValidity()) {
                event.preventDefault(); // デフォルトのモーダル表示をキャンセル
                event.stopPropagation(); // イベントの伝播を停止
                form.classList.add('was-validated'); // バリデーションエラー表示
            } else {
                // バリデーションが成功した場合、モーダルにデータをセットして表示
                // モーダルを常に確認状態にリセット
                modalTitle.textContent = '入力内容の確認';

                // HTML内のspan ID (modalNameなど) を直接更新する場合
                // document.getElementById('modalName').textContent = document.getElementById('name').value;
                // document.getElementById('modalCellNumber').textContent = document.getElementById('cell_number').value || '未入力';
                // document.getElementById('modalMail').textContent = document.getElementById('mail').value;
                // document.getElementById('modalPostCode').textContent = document.getElementById('post_code').value;
                // document.getElementById('modalAddress').textContent = document.getElementById('address').value;

                // または、modalBody.innerHTML で内容を完全に再構築する場合 (現在のコードに近い)
                modalBody.innerHTML = `
                    <p>以下の内容で登録します。よろしいですか？</p>
                    <p><strong>企業名:</strong> ${document.getElementById('name').value}</p>
                    <p><strong>電話番号:</strong> ${document.getElementById('cell_number').value || '未入力'}</p>
                    <p><strong>メールアドレス:</strong> ${document.getElementById('mail').value}</p>
                    <p><strong>郵便番号:</strong> ${document.getElementById('post_code').value}</p>
                    <p><strong>住所:</strong> ${document.getElementById('address').value}</p>
                `;
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-primary" id="confirmRegisterInternal">登録</button>
                `;
                // Re-attach listener to the new button if ID is reused or use a more robust way
                document.getElementById('confirmRegisterInternal').addEventListener('click', handleConfirmRegister);

                confirmModalInstance.show(); // モーダルを表示
            });
        }

        // モーダル内の「登録」ボタンがクリックされたらフォームを送信
        function handleConfirmRegister() {
            var formData = new FormData(form);
            formData.append('action', 'register_customer');

            fetch('customer-register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    modalTitle.textContent = '登録完了';
                    modalBody.innerHTML = '<p>' + data.message + '</p>';
                    modalFooter.innerHTML = '<button type="button" class="btn btn-primary" id="closeSuccessModal">閉じる</button>';
                    document.getElementById('closeSuccessModal').addEventListener('click', function() {
                        confirmModalInstance.hide();
                        form.reset();
                        form.classList.remove('was-validated');
                        window.location.href = 'customer.php'; // Redirect to customer list
                    });
                } else {
                    alert('登録エラー: ' + (data.message || '不明なエラーが発生しました。'));
                    confirmModalInstance.hide(); // Hide modal to allow form correction
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('通信エラー: 登録処理中に問題が発生しました。');
                confirmModalInstance.hide();
            });
        }

        // ページロード時にバリデーションクラスをリセット（任意）
        if (form) { // Ensure form exists
            form.classList.remove('was-validated');
        }
    })()
</script>
</html>