<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';

// =========================================
// 1. FILTER TAHUN
// =========================================
$filter_year = $_GET['year'] ?? date('Y');

// =========================================
// 2. QUERY UTAMA: MENGHITUNG RATA-RATA JAM
// =========================================
/**
 * Logika:
 * 1. Hitung total menit training per departemen per bulan
 * 2. Ambil jumlah karyawan per departemen
 * 3. Hitung: (Total Menit / 60) / Jumlah Karyawan
 */

// A. Ambil Jumlah Karyawan per Departemen (Denominator)
$emp_counts = [];
$st_emp = $pdo->query("SELECT org_id, COUNT(*) as total FROM employees WHERE status = 'Active' GROUP BY org_id");
while($row = $st_emp->fetch()) {
    $emp_counts[$row['org_id']] = $row['total'];
}

// B. Ambil Data Departemen
$orgs = $pdo->query("SELECT * FROM master_organizations ORDER BY org_name ASC")->fetchAll();

// C. Ambil Data Training Menit per Bulan
$query_hours = "SELECT 
                    e.org_id, 
                    MONTH(t.training_date) as bln, 
                    SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes
                FROM training_participants tp
                JOIN trainings t ON tp.training_id = t.id
                JOIN employees e ON tp.employee_id = e.id
                WHERE YEAR(t.training_date) = ?
                GROUP BY e.org_id, bln";
$stmt_hours = $pdo->prepare($query_hours);
$stmt_hours->execute([$filter_year]);
$hours_data = $stmt_hours->fetchAll();

// Olah data ke dalam array matrix [org_id][bulan]
$matrix = [];
foreach ($hours_data as $hd) {
    $matrix[$hd['org_id']][$hd['bln']] = $hd['total_minutes'];
}

// =========================================
// 3. LOGIKA EXPORT EXCEL
// =========================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Average_Training_Hours_$filter_year.xls");
    
    echo "Department\tJan\tFeb\tMar\tApr\tMei\tJun\tJul\tAgu\tSep\tOkt\tNov\tDes\n";
    foreach ($orgs as $o) {
        echo $o['org_name'];
        $count = $emp_counts[$o['id']] ?? 0;
        for ($m = 1; $m <= 12; $m++) {
            $mins = $matrix[$o['id']][$m] ?? 0;
            $avg = ($count > 0) ? round(($mins / 60) / $count, 2) : 0;
            echo "\t" . str_replace('.', ',', $avg);
        }
        echo "\n";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Average Training - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .matrix-table { width: 100%; border-collapse: collapse; font-size: 13px; background: white; }
        .matrix-table th { background: #f8fafc; color: #64748b; padding: 12px 8px; text-align: center; border: 1px solid #e2e8f0; font-size: 11px; }
        .matrix-table td { padding: 12px 8px; border: 1px solid #e2e8f0; text-align: center; }
        .matrix-table td:first-child { text-align: left; font-weight: 600; background: #f8fafc; width: 200px; }
        .matrix-table tr:last-child { background: #f1f5f9; font-weight: 800; color: var(--primary-blue); }
        .avg-value { font-weight: 700; color: #1e293b; }
        .zero-value { color: #cbd5e1; font-weight: 400; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--primary-blue); margin: 0;">Average Training Hours</h2>
                    <p style="font-size: 13px; color: #64748b; margin-top: 5px;">Rata-rata jam pelatihan per karyawan per departemen</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <form method="GET" style="display: flex; gap: 5px; align-items: center;">
                        <label style="font-size: 13px; font-weight: 600;">Tahun:</label>
                        <select name="year" class="form-control" onchange="this.form.submit()" style="width: 100px;">
                            <?php for($y=date('Y'); $y>=2020; $y--): ?>
                                <option value="<?= $y ?>" <?= ($filter_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                    <a href="?year=<?= $filter_year ?>&export=excel" class="btn-primary" style="background: #16a34a; width: auto; padding: 10px 20px;">
                        <i class="material-symbols-rounded" style="vertical-align: middle;">download</i> Export
                    </a>
                </div>
            </div>

            <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                <table class="matrix-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <?php 
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            foreach($months as $m) echo "<th>$m</th>";
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_month_avg = array_fill(1, 12, 0); 
                        foreach($orgs as $o): 
                            $emp_count = $emp_counts[$o['id']] ?? 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($o['org_name']) ?> <br> <small style="font-weight: 400; color: #94a3b8;"><?= $emp_count ?> Employees</small></td>
                            <?php for($m=1; $m<=12; $m++): 
                                $minutes = $matrix[$o['id']][$m] ?? 0;
                                $avg = ($emp_count > 0) ? round(($minutes / 60) / $emp_count, 2) : 0;
                                $total_month_avg[$m] += $avg;
                            ?>
                                <td class="<?= ($avg > 0) ? 'avg-value' : 'zero-value' ?>">
                                    <?= ($avg > 0) ? $avg : '0' ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr>
                            <td>OVERALL AVERAGE</td>
                            <?php for($m=1; $m<=12; $m++): 
                                $overall = round($total_month_avg[$m] / (count($orgs) ?: 1), 2);
                            ?>
                                <td><?= $overall ?></td>
                            <?php endfor; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; background: #fefce8; border: 1px solid #fef08a; padding: 15px; border-radius: 10px; font-size: 12px; color: #854d0e;">
                <strong>Keterangan Rumus:</strong><br>
                (Total Jam Training Departemen pada bulan tersebut) / (Jumlah Karyawan Aktif di Departemen tersebut).
            </div>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>