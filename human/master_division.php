<?php
session_start();
require_once '../config.php';

// エラーを画面に表示する設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

$msg = '';
$msg_type = '';

// --- 1. DB構造の確認用デバッグ表示 ---
echo "<div style='background:#f8f9fa; padding:10px; border-bottom:1px solid #ccc;'>";
echo "<strong>▼ デバッグ情報 (確認後、削除してください) ▼</strong><br>";
try {
    // テーブルのカラム情報を取得して表示
    $columns = $PDO->query("SHOW COLUMNS FROM DIVISION")->fetchAll(PDO::FETCH_ASSOC);
    echo "<strong>DIVISIONテーブルのカラム構造:</strong><pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "カラム情報の取得失敗: " . $e->getMessage() . "<br>";
}
echo "</div>";
// -------------------------------------

// --- POST処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action === 'add') {
            $name = $_POST['division_name'];
            if (!empty($name)) {
                // INSERT実行
                $stmt = $PDO->prepare("INSERT INTO DIVISION (DIVISION_NAME) VALUES (?)");
                $stmt->execute([$name]);
                $msg = "部署「" . htmlspecialchars($name) . "」を追加しました。ID: " . $PDO->lastInsertId();
                $msg_type = 'success';
            }
        } elseif ($action === 'edit') {
            $id = $_POST['division_id'];
            $name = $_POST['division_name'];
            if (!empty($id) && !empty($name)) {
                $stmt = $PDO->prepare("UPDATE DIVISION SET DIVISION_NAME = ? WHERE DIVISION_ID = ?");
                $stmt->execute([$name, $id]);
                $msg = "部署名を更新しました。";
                $msg_type = 'success';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['division_id'];
            if (!empty($id)) {
                $stmt = $PDO->prepare("DELETE FROM DIVISION WHERE DIVISION_ID = ?");
                $stmt->execute([$id]);
                $msg = "部署を削除しました。";
                $msg_type = 'success';
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $msg = "エラー: この部署には社員が所属しているため削除できません。";
        } else {
            $msg = "DBエラー: " . $e->getMessage();
        }
        $msg_type = 'danger';
    }
}

// --- データ一覧取得 ---
try {
    // SQL実行
    $sql = "SELECT * FROM DIVISION ORDER BY DIVISION_ID"; // ここでエラーが出ている可能性があります
    $divisions = $PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>データ取得エラー: " . $e->getMessage() . "</div>";
    $divisions = [];
}
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
                <small class="text-muted ms-3">取得件数: <?= count($divisions) ?> 件</small>
            </div>
            <div class="card-body p-0">
                <?php if(!empty($divisions)): ?>
                    <div class="p-2 bg-light border-bottom">
                        <small>取得データのキー確認: <?= implode(', ', array_keys($divisions[0])) ?></small>
                    </div>
                <?php endif; ?>

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
                                <td><?= htmlspecialchars($div['DIVISION_ID'] ?? $div['ID'] ?? '不明') ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="division_id" value="<?= $div['DIVISION_ID'] ?? $div['ID'] ?? '' ?>">
                                        <input type="text" name="division_name" class="form-control form-control-sm" value="<?= htmlspecialchars($div['DIVISION_NAME'] ?? $div['NAME'] ?? '') ?>" required>
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-save"></i> 保存</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('本当に削除しますか？');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="division_id" value="<?= $div['DIVISION_ID'] ?? $div['ID'] ?? '' ?>">
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