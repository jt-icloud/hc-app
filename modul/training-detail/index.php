<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';

// =========================================
// 1. LOGIKA FILTER & SEARCH
// =========================================
$search    = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';
$title_f   = $_GET['title_filter'] ?? '';

// Query Dasar (Join Semua Tabel Terkait)
$query = "SELECT 
            e.employee_id, e.full_name, o.org_name,
            t.title, t.held_by, t.training_date, t.start_time, t.finish_time, t.fee, t.is_certified,
            a.activity_name, s.skill_name, tr.trainer_name
          FROM training_participants tp
          JOIN trainings t ON tp.training_id = t.id
          JOIN employees e ON tp.employee_id = e.id
          LEFT JOIN master_organizations o ON e.org_id = o.id
          LEFT JOIN attr_activities a ON t.activity_id = a.id
          LEFT JOIN attr_skills s ON t.skill_id = s.id
          LEFT JOIN master_trainers tr ON t.trainer_id = tr.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (e.full_name LIKE ? OR e.employee_id LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if (!empty($title_f)) {
    $query .= " AND t.title LIKE ?";
    $params[] = "%$title_f%";
}
if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND t.training_date BETWEEN ? AND ?";
    $params[] = $date_from; $params[] = $date_to;
}

$query .= " ORDER BY t.training_date DESC, e.full_name ASC";

// =========================================
// 2. LOGIKA EXPORT EXCEL
// =========================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Training_Detail_" . date('Ymd') . ".xls");
    
    echo "NIK\tNama Karyawan\tOrganization\tTraining Title\tTrainer\tHeld By\tActivities\tSkill\tDate\tStart\tFinish\tFee\tCertification\n";
    
    $stmt_export = $pdo->prepare($query);
    $stmt_export->execute($params);
    while ($row = $stmt_export->fetch()) {
        echo $row['employee_id'] . "\t" . 
             $row['full_name'] . "\t" . 
             $row['org_name'] . "\t" . 
             $row['title'] . "\t" . 
             $row['trainer_name'] . "\t" . 
             $row['held_by'] . "\t" . 
             $row['activity_name'] . "\t" . 
             $row['skill_name'] . "\t" . 
             $row['training_date'] . "\t" . 
             $row['start_time'] . "\t" . 
             $row['finish_time'] . "\t" . 
             $row['fee'] . "\t" . 
             $row['is_certified'] . "\n";
    }
    exit();
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Training Detail - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filter-panel { background: #f8fafc; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 12px; background: white; }
        .report-table th { background: #1e293b; color: white; padding: 12px 8px; text-align: left; font-weight: 600; text-transform: uppercase; white-space: nowrap; }
        .report-table td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; }
        .badge-cert { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 5px; font-weight: 700; font-size: 10px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--primary-blue); margin: 0;">Training Detail Report</h2>
                <a href="?<?= $_SERVER['QUERY_STRING'] ?>&export=excel" class="btn-primary" style="background: #16a34a; width: auto; padding: 10px 20px;">
                    <i class="material-symbols-rounded" style="vertical-align: middle;">download</i> Export Excel
                </a>
            </div>

            <div class="filter-panel">
                <form method="GET" class="filter-row">
                    <div class="form-group" style="margin:0">
                        <label>Cari Nama/NIK</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Keyword...">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Judul Training</label>
                        <input type="text" name="title_filter" class="form-control" value="<?= htmlspecialchars($title_f) ?>" placeholder="Filter Judul...">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button type="submit" class="btn-primary" style="padding: 10px;">Apply</button>
                        <a href="index.php" class="btn-primary" style="background:#94a3b8; padding:10px; text-decoration:none; text-align:center;">Reset</a>
                    </div>
                </form>
            </div>

            <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Employee Name</th>
                            <th>Organization</th>
                            <th>Training Title</th>
                            <th>Trainer</th>
                            <th>Held By</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Fee</th>
                            <th>Cert.</th>
                            <th>Activity</th>
                            <th>Skill</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($data) > 0): foreach($data as $row): ?>
                        <tr>
                            <td><?= $row['employee_id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                            <td><?= $row['org_name'] ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['trainer_name']) ?></td>
                            <td><?= htmlspecialchars($row['held_by']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['training_date'])) ?></td>
                            <td><?= substr($row['start_time'],0,5) ?> - <?= substr($row['finish_time'],0,5) ?></td>
                            <td><?= number_format($row['fee'], 0, ',', '.') ?></td>
                            <td>
                                <?php if($row['is_certified'] == 'Yes'): ?>
                                    <span class="badge-cert">YES</span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['activity_name'] ?></td>
                            <td><?= $row['skill_name'] ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="12" style="text-align:center; padding:50px; color:#94a3b8;">Tidak ada data ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>