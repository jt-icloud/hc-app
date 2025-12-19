<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi: Hanya Super Admin (ID: 1) yang bisa melihat log sistem
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../dashboard/index.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';

// --- LOGIKA EXPORT EXCEL ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Log_Sistem_HCAPP_" . date('Y-m-d') . ".xls");
    
    echo "ID\tUser\tAktivitas\tIP Address\tWaktu\n";
    
    $stmt_export = $pdo->query("SELECT l.*, u.name FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.id DESC");
    while ($row = $stmt_export->fetch()) {
        echo $row['id'] . "\t" . ($row['name'] ?? 'Sistem/Guest') . "\t" . $row['activity'] . "\t" . $row['ip_address'] . "\t" . $row['created_at'] . "\n";
    }
    exit();
}

// --- LOGIKA FILTER & SEARCH ---
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Membangun Query Dinamis
$query = "SELECT l.*, u.name FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (l.activity LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND DATE(l.created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

$query .= " ORDER BY l.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Sistem - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filter-container { background: #f8fafc; padding: 20px; border-radius: 15px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: end; }
        .btn-export { background: #16a34a; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-export:hover { background: #15803d; transform: translateY(-2px); }
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table th { background: #f1f5f9; padding: 12px; text-align: left; font-size: 13px; color: #64748b; text-transform: uppercase; }
        .log-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .ip-badge { background: #e2e8f0; color: #475569; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="color: var(--primary-blue); margin: 0;">Audit Log Sistem</h2>
                <a href="?export=excel" class="btn-export">
                    <i class="material-symbols-rounded">download</i> Export ke Excel
                </a>
            </div>

            <div class="filter-container">
                <form method="GET" class="filter-grid">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Cari Aktivitas/User</label>
                        <input type="text" name="search" class="form-control" placeholder="Ketik kata kunci..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button type="submit" class="btn-primary" style="padding: 10px;">Filter</button>
                        <a href="index.php" class="btn-primary" style="padding: 10px; background: #94a3b8; text-decoration: none; text-align: center;">Reset</a>
                    </div>
                </form>
            </div>

            <table class="log-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td style="color: #64748b;"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars($l['name'] ?? 'Guest/System') ?></strong></td>
                            <td><?= htmlspecialchars($l['activity']) ?></td>
                            <td><span class="ip-badge"><?= $l['ip_address'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="material-symbols-rounded" style="font-size: 48px;">search_off</i>
                                <p>Tidak ada data log yang ditemukan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>