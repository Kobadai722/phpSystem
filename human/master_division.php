<?php
session_start();
require_once '../config.php';

// メッセージ初期化
$msg = '';
$msg_type = '';

// --- POST処理（追加・編集・削除） ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action === 'add') {
            // 追加
            $name = $_POST['division_name'];
            if (!empty($name)) {
                $stmt = $PDO->prepare("INSERT INTO DIVISION (DIVISION_NAME) VALUES (?)");
                $stmt->execute([$name]);
                $msg = "部署「" . htmlspecialchars($name) . "」を追加しました。";
                $msg_type = 'success';
            }
        } elseif ($action === 'edit') {
            // 編集
            $id = $_POST['division_id'];
            $name = $_POST['division_name'];
            if (!empty($id) && !empty($name)) {
                $stmt = $PDO->prepare("UPDATE DIVISION SET DIVISION_NAME = ? WHERE DIVISION_ID = ?");
                $stmt->execute([$name, $id]);
                $msg = "部署名を更新しました。";
                $msg_type = 'success';
            }
        } elseif ($action === 'delete') {
            // 削除
            $id = $_POST['division_id'];
            if (!empty($id)) {
                // 削除実行（社員がいる場合はここでエラーになり catch ブロックへ飛ぶ）
                $stmt = $PDO->prepare("DELETE FROM DIVISION WHERE DIVISION_ID = ?");
                $stmt->execute([$id]);
                $msg = "部署を削除しました。";
                $msg_type = 'success';
            }
        }
    } catch (PDOException $e) {
        // エラーハンドリング (特に削除時の外部キー制約違反)
        if ($e->getCode() == '23000') {
            $msg = "エラー: この部署には社員が所属しているため削除できません。";
        } else {
            $msg = "データベースエラー: " . $e->getMessage();
        }
        $msg_type = 'danger';
    }
}

// --- データ一覧取得 ---
$divisions = $PDO->query("SELECT * FROM DIVISION ORDER BY DIVISION_ID")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>部署マスタ管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building"></i> 部署マスタ管理</h2>
            <a href="editer.php" class="btn btn-secondary">編集者画面へ戻る</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="bi bi-plus-circle"></i> 新規部署の登録
            </div>
            <div class="card-body">
                <form method="post" class="row g-3 align-items-end">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-8">
                        <label for="new_division_name" class="form-label">部署名</label>
                        <input type="text" class="form-control" id="new_division_name" name="division_name" placeholder="例: 営業部" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">追加する</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-list"></i> 登録済み部署一覧
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th>部署名</th>
                            <th style="width: 25%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisions as $div): ?>
                            <tr>
                                <td><?= htmlspecialchars($div['DIVISION_ID']) ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="division_id" value="<?= $div['DIVISION_ID'] ?>">
                                        <input type="text" name="division_name" class="form-control form-control-sm" value="<?= htmlspecialchars($div['DIVISION_NAME']) ?>" required>
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-save"></i> 保存</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('本当に「<?= htmlspecialchars($div['DIVISION_NAME']) ?>」を削除しますか？\n※所属社員がいる場合は削除できません。');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="division_id" value="<?= $div['DIVISION_ID'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> 削除</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>