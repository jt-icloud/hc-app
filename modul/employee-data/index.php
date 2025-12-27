<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';
$msg = "";

// =========================================
// 1. LOGIKA EXPORT EXCEL (GET)
// =========================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Employee_Data_" . date('Ymd') . ".xls");
    
    echo "NIK\tNama Lengkap\tOrganization\tPosition\tLevel\tStatus\n";
    
    $sql_export = "SELECT e.*, o.org_name, p.position_name, l.level_name 
                   FROM employees e 
                   LEFT JOIN master_organizations o ON e.org_id = o.id 
                   LEFT JOIN master_job_positions p ON e.position_id = p.id 
                   LEFT JOIN master_job_levels l ON e.level_id = l.id 
                   ORDER BY e.full_name ASC";
    $stmt_export = $pdo->query($sql_export);
    while ($row = $stmt_export->fetch()) {
        echo $row['employee_id'] . "\t" . 
             $row['full_name'] . "\t" . 
             $row['org_name'] . "\t" . 
             $row['position_name'] . "\t" . 
             $row['level_name'] . "\t" . 
             $row['status'] . "\n";
    }
    exit();
}

// =========================================
// 2. LOGIKA AKSI (POST) - SIMPAN, UPDATE, IMPORT
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // A. Simpan/Update Manual
    if (isset($_POST['save_employee'])) {
        $id = $_POST['id'] ?? null;
        $emp_id = $_POST['employee_id'];
        $name = $_POST['full_name'];
        $org = $_POST['org_id'];
        $pos = $_POST['position_id'];
        $lvl = $_POST['level_id'];
        $status = $_POST['status'];

        try {
            if ($id) {
                $sql = "UPDATE employees SET employee_id=?, full_name=?, org_id=?, position_id=?, level_id=?, status=? WHERE id=?";
                $params = [$emp_id, $name, $org, $pos, $lvl, $status, $id];
            } else {
                $sql = "INSERT INTO employees (employee_id, full_name, org_id, position_id, level_id, status) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$emp_id, $name, $org, $pos, $lvl, $status];
            }
            $pdo->prepare($sql)->execute($params);
            write_log($pdo, "Mengelola data karyawan: $name");
            header("Location: index.php?msg=success");
            exit();
        } catch (PDOException $e) { $msg = $e->getMessage(); }
    }

    // B. Import CSV
    if (isset($_POST['import_csv'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        if ($file) {
            $handle = fopen($file, "r");
            $header = fgetcsv($handle, 1000, ","); // Lewati baris judul
            
            // Ambil mapping master data untuk efisiensi
            $org_map = $pdo->query("SELECT id, org_name FROM master_organizations")->fetchAll(PDO::FETCH_KEY_PAIR);
            $pos_map = $pdo->query("SELECT id, position_name FROM master_job_positions")->fetchAll(PDO::FETCH_KEY_PAIR);
            $lvl_map = $pdo->query("SELECT id, level_name FROM master_job_levels")->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $stmt = $pdo->prepare("INSERT INTO employees (employee_id, full_name, org_id, position_id, level_id, status) 
                                   VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
                                   full_name=VALUES(full_name), org_id=VALUES(org_id), 
                                   position_id=VALUES(position_id), level_id=VALUES(level_id), status=VALUES(status)");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) < 6) continue;
                
                $emp_id = $data[0];
                $full_name = $data[1];
                $org_id = array_search($data[2], $org_map) ?: null;
                $pos_id = array_search($data[3], $pos_map) ?: null;
                $lvl_id = array_search($data[4], $lvl_map) ?: null;
                $status = in_array($data[5], ['Active', 'Inactive']) ? $data[5] : 'Active';
                
                $stmt->execute([$emp_id, $full_name, $org_id, $pos_id, $lvl_id, $status]);
            }
            fclose($handle);
            write_log($pdo, "Melakukan import data karyawan via CSV");
            header("Location: index.php?msg=imported");
            exit();
        }
    }
}

// =========================================
// 3. LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$id]);
    write_log($pdo, "Menghapus data karyawan ID: $id");
    header("Location: index.php?msg=deleted");
    exit();
}

// =========================================
// 4. PENGAMBILAN DATA
// =========================================
$orgs = $pdo->query("SELECT * FROM master_organizations ORDER BY org_name ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM master_job_positions ORDER BY position_name ASC")->fetchAll();
$levels = $pdo->query("SELECT * FROM master_job_levels ORDER BY level_name ASC")->fetchAll();

$employees = $pdo->query("SELECT e.*, o.org_name, p.position_name, l.level_name 
                          FROM employees e 
                          LEFT JOIN master_organizations o ON e.org_id = o.id 
                          LEFT JOIN master_job_positions p ON e.position_id = p.id 
                          LEFT JOIN master_job_levels l ON e.level_id = l.id 
                          ORDER BY e.full_name ASC")->fetchAll();

$edit_data = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $st->execute([$_GET['edit']]);
    $edit_data = $st->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Employee Data - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .form-card { background: #f8fafc; padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .data-table th { background: #f1f5f9; padding: 12px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="color: var(--primary-blue); margin: 0;">Employee Data Management</h2>
                <div class="btn-group">
                    <button onclick="document.getElementById('importBox').style.display='block'" class="btn-primary" style="background: #6366f1; width: auto; padding: 10px 20px;">
                        <i class="material-symbols-rounded" style="vertical-align: middle;">upload_file</i> Import CSV
                    </button>
                    <a href="?export=excel" class="btn-primary" style="background: #16a34a; width: auto; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="material-symbols-rounded">download</i> Export Excel
                    </a>
                </div>
            </div>

            <div id="importBox" class="form-card" style="display: none; border: 2px dashed #6366f1; background: #f5f3ff;">
                <h4 style="margin-top: 0; color: #4338ca;">Import Data via CSV</h4>
                <p style="font-size: 12px; color: #6366f1;">Format Kolom: <strong>NIK, Nama Lengkap, Organization, Position, Level, Status</strong></p>
                <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                    <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                    <button type="submit" name="import_csv" class="btn-primary" style="width: auto;">Proses Import</button>
                    <button type="button" onclick="document.getElementById('importBox').style.display='none'" class="btn-primary" style="background: #94a3b8; width: auto;">Batal</button>
                </form>
            </div>

            <div class="form-card">
                <h4 style="margin-top: 0; margin-bottom: 20px;"><?= $edit_data ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' ?></h4>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <div class="grid-form">
                        <div class="form-group"><label>NIK (Employee ID)</label><input type="text" name="employee_id" class="form-control" value="<?= $edit_data['employee_id'] ?? '' ?>" required></div>
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="full_name" class="form-control" value="<?= $edit_data['full_name'] ?? '' ?>" required></div>
                        <div class="form-group"><label>Organization</label>
                            <select name="org_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($orgs as $o): ?>
                                    <option value="<?= $o['id'] ?>" <?= (isset($edit_data['org_id']) && $edit_data['org_id'] == $o['id']) ? 'selected' : '' ?>><?= $o['org_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Job Position</label>
                            <select name="position_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($positions as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($edit_data['position_id']) && $edit_data['position_id'] == $p['id']) ? 'selected' : '' ?>><?= $p['position_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Job Level</label>
                            <select name="level_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($levels as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= (isset($edit_data['level_id']) && $edit_data['level_id'] == $l['id']) ? 'selected' : '' ?>><?= $l['level_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Active" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="submit" name="save_employee" class="btn-primary" style="width: auto; padding: 10px 30px;">Simpan</button>
                        <?php if($edit_data): ?><a href="index.php" class="btn-primary" style="background: #94a3b8; width: auto; padding: 10px 30px; text-decoration: none;">Batal</a><?php endif; ?>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>No</th><th>NIK</th><th>Nama Lengkap</th><th>Organization</th><th>Position</th><th>Level</th><th>Status</th><th width="150">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach($employees as $e): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= $e['employee_id'] ?></strong></td>
                        <td><?= htmlspecialchars($e['full_name']) ?></td>
                        <td><?= $e['org_name'] ?></td>
                        <td><?= $e['position_name'] ?></td>
                        <td><?= $e['level_name'] ?></td>
                        <td><span class="badge <?= ($e['status'] == 'Active') ? 'badge-active' : 'badge-inactive' ?>"><?= $e['status'] ?></span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="?edit=<?= $e['id'] ?>" class="btn-primary" style="padding: 5px 10px; font-size: 11px; background: #eef2ff; color: #4338ca; text-decoration: none;">Edit</a>
                                <a href="?delete=<?= $e['id'] ?>" class="btn-primary" style="padding: 5px 10px; font-size: 11px; background: #fff1f2; color: #be123c; text-decoration: none;" onclick="return confirm('Hapus?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>