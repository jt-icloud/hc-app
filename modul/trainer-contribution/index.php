<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';

// =========================================
// 1. FILTER & SEARCH LOGIC
// =========================================
$search    = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

// Query Dasar: Menghitung total menit mengajar per Trainer per Activity & Skill
$query = "SELECT 
            tr.trainer_name, 
            o.org_name as department,
            a.activity_name,
            s.skill_name,
            SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes
          FROM trainings t
          JOIN master_trainers tr ON t.trainer_id = tr.id
          LEFT JOIN master_organizations o ON tr.org_id = o.id
          LEFT JOIN attr_activities a ON t.activity_id = a.id
          LEFT JOIN attr_skills s ON t.skill_id = s.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (tr.trainer_name LIKE ? OR o.org_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND t.training_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

$query .= " GROUP BY tr.id, a.id, s.id ORDER BY total_minutes DESC";

// =========================================
// 2. LOGIKA EXPORT EXCEL
// =========================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Trainer_Contribution_Report.xls");
    
    echo "Nama Trainer\tDepartemen\tActivities\tSkill\tDurasi Jam Mengajar\n";
    $stmt_export = $pdo->prepare($query);
    $stmt_export->execute($params);
    while ($row = $stmt_export->fetch()) {
        $hours = round($row['total_minutes'] / 60, 2);
        echo $row['trainer_name'] . "\t" . 
             $row['department'] . "\t" . 
             $row['activity_name'] . "\t" . 
             $row['skill_name'] . "\t" . 
             str_replace('.', ',', $hours) . " Jam\n";
    }
    exit();
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$contributions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Trainer Contribution - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filter-card { background: #f8fafc; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .contribution-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .contribution-table th { background: #0f172a; color: white; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; }
        .contribution-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .hour-badge { background: #eff6ff; color: #1d4ed8; padding: 5px 12px; border-radius: 20px; font-weight: 700; border: 1px solid #dbeafe; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h2 style="color: var(--primary-blue); margin: 0;">Trainer Contribution Report</h2>
                    <p style="font-size: 13px; color: #64748b;">Total jam mengajar trainer berdasarkan kategori</p>
                </div>
                <a href="?<?= $_SERVER['QUERY_STRING'] ?>&export=excel" class="btn-primary" style="background: #16a34a; width: auto; padding: 10px 20px;">
                    <i class="material-symbols-rounded" style="vertical-align: middle;">download</i> Export Excel
                </a>
            </div>

            <div class="filter-card">
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: end;">
                    <div class="form-group" style="margin:0">
                        <label>Cari Trainer / Dept</label>
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Nama...">
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
                        <button type="submit" class="btn-primary" style="padding: 10px;">Filter</button>
                        <a href="index.php" class="btn-primary" style="background:#94a3b8; text-decoration:none; text-align:center; padding: 10px;">Reset</a>
                    </div>
                </form>
            </div>

            <table class="contribution-table">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Trainer</th>
                        <th>Departemen</th>
                        <th>Activities</th>
                        <th>Skill</th>
                        <th style="text-align: center;">Total Mengajar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($contributions) > 0): ?>
                        <?php $no = 1; foreach($contributions as $c): 
                            $jam = round($c['total_minutes'] / 60, 1);
                        ?>
                        <tr>
                            <td style="color: #94a3b8;"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($c['trainer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['department'] ?? 'Internal') ?></td>
                            <td><span style="color: #64748b;"><?= $c['activity_name'] ?></span></td>
                            <td><span style="color: #64748b;"><?= $c['skill_name'] ?></span></td>
                            <td style="text-align: center;">
                                <span class="hour-badge"><?= $jam ?> Jam</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>