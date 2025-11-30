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
    
    // CSVヘッダー行（給与情報を追加）
    fputcsv($output, [
        '社員ID', 
        '氏名', 
        '部署', 
        '日付', 
        '出勤時刻', 
        '退勤時刻', 
        '実働時間(h)', 
        'ステータス',
        '給与形態',    // 追加
        '設定金額',    // 追加
        '概算日給'     // 追加
    ]);

    // データ取得SQL（SALARIESテーブルを結合）
    $sql = "
        SELECT 
            e.EMPLOYEE_ID, 
            e.NAME, 
            d.DIVISION_NAME,
            a.ATTENDANCE_DATE, 
            a.ATTENDANCE_TIME, 
            a.LEAVE_TIME, 
            a.STATUS,
            s.AMOUNT AS SALARY_AMOUNT,
            s.TYPE AS SALARY_TYPE
        FROM EMPLOYEE e
        LEFT JOIN DIVISION d ON e.DIVISION_ID = d.DIVISION_ID
        LEFT JOIN ATTENDANCE a ON e.EMPLOYEE_ID = a.EMPLOYEE_ID
        LEFT JOIN SALARIES s ON e.EMPLOYEE_ID = s.EMPLOYEE_ID  -- 給与テーブルを結合
        WHERE DATE_FORMAT(a.ATTENDANCE_DATE, '%Y-%m') = :target_month
        ORDER BY e.EMPLOYEE_ID, a.ATTENDANCE_DATE
    ";
    
    $stmt = $PDO->prepare($sql);
    $stmt->execute(['target_month' => $month_param]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // 実働時間の計算（休憩控除あり）
        $duration = 0;
        $daily_pay = 0; // 日給計算用

        if (!empty($row['ATTENDANCE_TIME']) && !empty($row['LEAVE_TIME'])) {
            $start = new DateTime($row['ATTENDANCE_TIME']);
            $end = new DateTime($row['LEAVE_TIME']);
            $interval = $start->diff($end);
            
            // 拘束時間（時間単位）
            $hours = $interval->h + ($interval->i / 60);
            
            // 休憩時間の自動控除
            $break_time = 0;
            if ($hours > 8) {
                $break_time = 1.0;
            } elseif ($hours > 6) {
                $break_time = 0.75;
            }
            
            // 実働時間
            $duration = $hours - $break_time;
            if ($duration < 0) { $duration = 0; }
            
            // 小数点第2位まで丸める
            $duration = round($duration, 2);

            // --- 概算日給の計算 ---
            if ($row['SALARY_TYPE'] === 'hourly' && $row['SALARY_AMOUNT']) {
                // 時給の場合：実働時間 × 時給
                $daily_pay = floor($duration * $row['SALARY_AMOUNT']);
            } elseif ($row['SALARY_TYPE'] === 'monthly') {
                // 月給の場合：日割り計算せず「-」とするか、0とする（今回は視認性のため文字列で出力）
                $daily_pay = '-';
            }
        }

        // 給与形態の日本語化
        $salary_type_label = '';
        if ($row['SALARY_TYPE'] === 'monthly') $salary_type_label = '月給';
        if ($row['SALARY_TYPE'] === 'hourly') $salary_type_label = '時給';

        // 行の出力
        fputcsv($output, [
            $row['EMPLOYEE_ID'],
            $row['NAME'],
            $row['DIVISION_NAME'],
            $row['ATTENDANCE_DATE'],
            $row['ATTENDANCE_TIME'],
            $row['LEAVE_TIME'],
            $duration,
            $row['STATUS'],
            $salary_type_label,                 // 給与形態
            $row['SALARY_AMOUNT'],              // 設定金額
            $daily_pay                          // 概算日給
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
                    ※ 「時給」設定の社員については、実働時間に基づいた概算日給が出力されます。<br>
                    ※ 「月給」設定の社員の日給欄は「-」となります。
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>