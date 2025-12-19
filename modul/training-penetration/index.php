<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';

// =========================================
// 1. FILTER & SEARCH
// =========================================
$search    = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

// Query Dasar: Ambil Departemen dan Total Karyawan Aktif
$query_org = "SELECT id, org_name, 
              (SELECT COUNT(*) FROM employees WHERE org_id = master_organizations.id AND status = 'Active') as total_emp
              FROM master_organizations WHERE 1=1";

if (!empty($search)) {
    $query_org .= " AND org_name LIKE " . $pdo->quote("%$search%");
}
$query_org .= " ORDER BY org_name ASC";
$orgs = $pdo->query($query_org)->fetchAll();

// Persiapan Data Penetrasi (Karyawan Unik yang Training)
$penetration_data = [];
$params = [];
$date_filter = "";

if (!empty($date_from) && !empty($date_to)) {
    $date_filter = " AND t.training_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

$query_trained = "SELECT e.org_id, COUNT(DISTINCT e.id) as trained_count
                  FROM training_participants tp
                  JOIN trainings t ON tp.training_id = t.id
                  JOIN employees e ON tp.employee_id = e.id
                  WHERE e.status = 'Active' $date_filter
                  GROUP BY e.org_id";

$stmt_trained = $pdo->prepare($query_trained);
$stmt_trained->execute($params);
while($row = $stmt_trained->fetch()) {
    $penetration_data[$row['org_id']] = $row['trained_count'];
}

// =========================================
// 2. LOGIKA EXPORT EXCEL
// =========================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Training_Penetration_Report.xls");
    
    echo "Department\tTotal Karyawan\tSudah Training\tPenetration (%)\n";
    foreach ($orgs as $o) {
        $total = $o['total_emp'];
        $trained = $penetration_data[$o['id']] ?? 0;
        $pct = ($total > 0) ? round(($trained / $total) * 100, 2) : 0;
        echo $o['org_name'] . "\t" . $total . "\t" . $trained . "\t" . str_replace('.', ',', $pct) . "%\n";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Training Penetration - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filter-card { background: #f8fafc; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .penetration-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .penetration-table th { background: #1e293b; color: white; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        .penetration-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .penetration-table tfoot { background: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0; }
        .progress-bar-bg { background: #e2e8f0; width: 100px; height: 8px; border-radius: 10px; display: inline-block; margin-right: 10px; }
        .progress-bar-fill { background: var(--primary-blue); height: 100%; border-radius: 10px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--primary-blue); margin: 0;">Training Penetration Report</h2>
                    <p style="font-size: 13px; color: #64748b;">Persentase jangkauan pelatihan per departemen</p>
                </div>
                <a href="?<?= $_SERVER['QUERY_STRING'] ?>&export=excel" class="btn-primary" style="background: #16a34a; width: auto; padding: 10px 20px;">
                    <i class="material-symbols-rounded" style="vertical-align: middle;">download</i> Export Excel
                </a>
            </div>

            <div class="filter-card">
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: end;">
                    <div class="form-group" style="margin:0">
                        <label>Cari Departemen</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Nama Dept...">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Mulai Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button type="submit" class="btn-primary">Apply</button>
                        <a href="index.php" class="btn-primary" style="background:#94a3b8; text-decoration:none; text-align:center;">Reset</a>
                    </div>
                </form>
            </div>

            <table class="penetration-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th style="text-align: center;">Total Karyawan</th>
                        <th style="text-align: center;">Sudah Training (Unique)</th>
                        <th style="text-align: center;">Penetration (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sum_total = 0; 
                    $sum_trained = 0;
                    foreach($orgs as $o): 
                        $total = $o['total_emp'];
                        $trained = $penetration_data[$o['id']] ?? 0;
                        $pct = ($total > 0) ? round(($trained / $total) * 100, 1) : 0;
                        
                        $sum_total += $total;
                        $sum_trained += $trained;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($o['org_name']) ?></strong></td>
                        <td style="text-align: center;"><?= $total ?></td>
                        <td style="text-align: center;"><?= $trained ?></td>
                        <td style="text-align: center;">
                            <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div></div>
                            <span style="font-weight: 700;"><?= $pct ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTAL / AGREGAT</td>
                        <td style="text-align: center;"><?= $sum_total ?></td>
                        <td style="text-align: center;"><?= $sum_trained ?></td>
                        <td style="text-align: center;">
                            <?php $total_pct = ($sum_total > 0) ? round(($sum_trained / $sum_total) * 100, 1) : 0; ?>
                            <span style="font-size: 16px; color: var(--primary-blue);"><?= $total_pct ?>%</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>