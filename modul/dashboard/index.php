<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: ../auth/login.php"); 
    exit(); 
}

$layout_dir = __DIR__ . '/../layout/';

// --- 1. DEFINISI VARIABEL BULAN (Diletakkan di atas agar tidak Undefined) ---
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// --- 2. LOGIKA FILTER ---
$filter_month = $_GET['month'] ?? date('m'); // Bisa 'all' atau '01'-'12'
$filter_year  = $_GET['year'] ?? date('Y');

try {
    // --- 3. QUERY KPI CARDS ---
    // Cek apakah filter 'Semua Bulan' atau bulan spesifik
    $month_condition = ($filter_month === 'all') ? "" : "AND MONTH(training_date) = ?";
    $params_kpi = ($filter_month === 'all') ? [$filter_year] : [(int)$filter_month, $filter_year];

    // A. Total Jam & Realisasi Training
    $sql_kpi = "SELECT 
                    COUNT(id) as total_realized,
                    SUM(TIMESTAMPDIFF(MINUTE, start_time, finish_time)) as total_mins
                FROM trainings 
                WHERE YEAR(training_date) = ? $month_condition";
    
    $stmt_kpi = $pdo->prepare($sql_kpi);
    $stmt_kpi->execute($params_kpi);
    $kpi = $stmt_kpi->fetch(PDO::FETCH_ASSOC);

    $total_hours = round(($kpi['total_mins'] ?? 0) / 60, 1);
    $realized_count = $kpi['total_realized'] ?? 0;

    // B. Total Karyawan & Average
    $total_employees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn() ?: 1;
    $avg_training_hours = round($total_hours / $total_employees, 2);

    // --- 4. DATA TRAINING PENETRATION ---
    $month_pen_condition = ($filter_month === 'all') ? "" : "AND MONTH(t.training_date) = ?";
    $params_pen = ($filter_month === 'all') ? [$filter_year] : [(int)$filter_month, $filter_year];

    $sql_pen = "SELECT 
                    o.id, o.org_name,
                    (SELECT COUNT(*) FROM employees WHERE org_id = o.id AND status = 'Active') as total_emp,
                    (SELECT COUNT(DISTINCT tp.employee_id) 
                     FROM training_participants tp 
                     JOIN trainings t ON tp.training_id = t.id 
                     JOIN employees e ON tp.employee_id = e.id
                     WHERE e.org_id = o.id AND YEAR(t.training_date) = ? $month_pen_condition) as trained_emp
                FROM master_organizations o 
                ORDER BY o.org_name ASC";
    
    $stmt_pen = $pdo->prepare($sql_pen);
    $stmt_pen->execute($params_pen);
    $penetration_list = $stmt_pen->fetchAll(PDO::FETCH_ASSOC);

    // --- 5. DATA GRAFIK (TREN BULANAN) ---
    $chart_data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    $sql_chart = "SELECT MONTH(training_date) as bln, SUM(TIMESTAMPDIFF(MINUTE, start_time, finish_time)) as mins 
                  FROM trainings WHERE YEAR(training_date) = ? GROUP BY bln";
    $stmt_chart = $pdo->prepare($sql_chart);
    $stmt_chart->execute([$filter_year]);
    while($row = $stmt_chart->fetch(PDO::FETCH_ASSOC)) {
        $idx = (int)$row['bln'] - 1;
        if ($idx >= 0 && $idx <= 11) $chart_data[$idx] = round($row['mins'] / 60, 1);
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .kpi-card { background: white; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; }
        .kpi-title { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 10px; }
        .kpi-value { font-size: 28px; font-weight: 800; color: var(--primary-blue); }
        .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .chart-box, .side-box { background: white; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; }
        .pen-item { margin-bottom: 15px; }
        .pen-info { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; }
        .pen-bar { background: #f1f5f9; height: 8px; border-radius: 10px; overflow: hidden; }
        .pen-fill { background: var(--primary-blue); height: 100%; transition: width 0.6s ease; }
        .quick-links { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
        .link-item { background: #f8fafc; padding: 12px; border-radius: 10px; text-decoration: none; color: #1e293b; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; border: 1px solid #e2e8f0; }
        .link-item:hover { background: var(--primary-blue); color: white; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="margin: 0; color: var(--primary-blue);">Executive Summary</h2>
                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    Periode: <?= ($filter_month === 'all') ? "Tahun Full $filter_year" : ($months[(int)$filter_month] ?? '') . " $filter_year" ?>
                </p>
            </div>
            
            <form method="GET" style="display: flex; gap: 10px; background: white; padding: 8px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <select name="month" class="form-control" style="width: 150px;">
                    <option value="all" <?= ($filter_month === 'all') ? 'selected' : '' ?>>-- Semua Bulan --</option>
                    <?php foreach($months as $num => $name): ?>
                        <option value="<?= str_pad($num, 2, '0', STR_PAD_LEFT) ?>" <?= ((int)$filter_month == $num && $filter_month !== 'all') ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="form-control" style="width: 100px;">
                    <?php for($y=date('Y'); $y>=2024; $y--): ?>
                        <option value="<?= $y ?>" <?= ($filter_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-primary" style="width: auto; padding: 0 15px;">Filter</button>
            </form>
        </div>

        <div class="dashboard-grid">
            <div class="kpi-card">
                <span class="kpi-title">Total Jam Training</span>
                <span class="kpi-value"><?= number_format($total_hours, 1) ?> <small style="font-size: 14px;">Hrs</small></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-title">Avg. Hours / Karyawan</span>
                <span class="kpi-value"><?= number_format($avg_training_hours, 2) ?> <small style="font-size: 14px;">Hrs</small></span>
            </div>
            <div class="kpi-card">
                <span class="kpi-title">Sesi Terealisasi</span>
                <span class="kpi-value"><?= $realized_count ?> <small style="font-size: 14px;">Sesi</small></span>
            </div>
            <div class="kpi-card" style="background: var(--primary-blue); color: white;">
                <span class="kpi-title" style="color: rgba(255,255,255,0.7);">Total Karyawan Aktif</span>
                <span class="kpi-value" style="color: white;"><?= $total_employees ?></span>
            </div>
        </div>

        <div class="main-grid">
            <div class="chart-box">
                <h4 style="margin: 0 0 20px 0;">Trend Bulanan Jam Pelatihan (<?= $filter_year ?>)</h4>
                <div style="height: 350px;">
                    <canvas id="hoursChart"></canvas>
                </div>
            </div>

            <div class="side-box">
                <h4 style="margin: 0 0 20px 0;">Penetrasi Training</h4>
                <div style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                    <?php foreach($penetration_list as $pen): 
                        $pct = ($pen['total_emp'] > 0) ? round(($pen['trained_emp'] / $pen['total_emp']) * 100) : 0;
                    ?>
                    <div class="pen-item">
                        <div class="pen-info">
                            <span><?= htmlspecialchars($pen['org_name']) ?></span>
                            <span style="font-weight: 700;"><?= $pct ?>%</span>
                        </div>
                        <div class="pen-bar"><div class="pen-fill" style="width: <?= $pct ?>%;"></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h4 style="margin: 30px 0 15px 0;">Menu Akses Cepat</h4>
                <div class="quick-links">
                    <a href="../training-data/index.php" class="link-item"><i class="material-symbols-rounded">add_box</i> Input Training</a>
                    <a href="../training-detail/index.php" class="link-item"><i class="material-symbols-rounded">list_alt</i> Detail Laporan</a>
                    <a href="../employee-data/index.php" class="link-item"><i class="material-symbols-rounded">groups</i> Data Karyawan</a>
                    <a href="../log-system/index.php" class="link-item"><i class="material-symbols-rounded">history</i> Audit Logs</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('hoursChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jam Training',
                    data: <?= json_encode($chart_data) ?>,
                    borderColor: '#0052CC',
                    backgroundColor: 'rgba(0, 82, 204, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>