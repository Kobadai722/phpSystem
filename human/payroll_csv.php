<?php
session_start();
require_once '../config.php';

// 権限チェック（必要に応じて追加してください）
// if (!isset($_SESSION['is_admin'])) { ... }

// 対象年月の取得（デフォルトは今月）
$month_param = $_GET['month'] ?? date('Y-m');

// CSVダウンロード処理
if (isset($_GET['download'])) {
    $filename = "attendance_data_{$month_param}.csv";
    
    // ヘッダー設定
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename={$filename}");
    
    // 出力ストリームを開く
    $output = fopen('php://output', 'w');
    
    // BOMを出力（Excelでの文字化け対策）
    fwrite($output, "\xEF\xBB\xBF");
    
    // CSVヘッダー行
    fputcsv($output, ['社員ID', '氏名', '部署', '日付', '出勤時刻', '退勤時刻', '実働時間(h)', 'ステータス']);

    // データ取得SQL
    $sql = "
        SELECT 
            e.EMPLOYEE_ID, 
            e.NAME, 
            d.DIVISION_NAME,
            a.ATTENDANCE_DATE, 
            a.ATTENDANCE_TIME, 
            a.LEAVE_TIME, 
            a.STATUS
        FROM EMPLOYEE e
        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        LEFT JOIN ATTENDANCE a ON e.EMPLOYEE_ID = a.EMPLOYEE_ID
        WHERE DATE_FORMAT(a.ATTENDANCE_DATE, '%Y-%m') = :target_month
        ORDER BY e.EMPLOYEE_ID, a.ATTENDANCE_DATE
    ";
    
    $stmt = $PDO->prepare($sql);
    $stmt->execute(['target_month' => $month_param]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // 実働時間の計算
        $duration = '';
        if (!empty($row['ATTENDANCE_TIME']) && !empty($row['LEAVE_TIME'])) {
            $start = new DateTime($row['ATTENDANCE_TIME']);
            $end = new DateTime($row['LEAVE_TIME']);
            $interval = $start->diff($end);
            // 時間単位（小数点あり）で計算: 例 8時間30分 -> 8.5
            $duration = $interval->h + ($interval->i / 60);
            $duration = round($duration, 2); // 小数点第2位まで
        }

        // 行の出力
        fputcsv($output, [
            $row['EMPLOYEE_ID'],
            $row['NAME'],
            $row['DIVISION_NAME'],
            $row['ATTENDANCE_DATE'],
            $row['ATTENDANCE_TIME'],
            $row['LEAVE_TIME'],
            $duration,
            $row['STATUS']
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>給与連携データ出力</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../header.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-csv"></i> 給与計算用データ出力</h2>
            <a href="editer.php" class="btn btn-secondary">編集者画面へ戻る</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="" method="get" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label for="month" class="form-label fw-bold">対象月を選択</label>
                        <input type="month" id="month" name="month" class="form-control" value="<?= htmlspecialchars($month_param) ?>" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="download" value="1" class="btn btn-primary">
                            <i class="fas fa-download"></i> CSVダウンロード
                        </button>
                    </div>
                </form>
                <p class="mt-3 text-muted">
                    ※ 選択した月の全社員の勤怠データをCSV形式で出力します。<br>
                    ※ 実働時間は「退勤時刻 - 出勤時刻」で簡易計算されます（休憩時間は考慮されていません）。
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>