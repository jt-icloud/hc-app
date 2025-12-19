<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi Sesi
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';
$msg = "";

// =========================================
// 1. LOGIKA AKSI (POST) - SIMPAN & UPDATE
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                // Update Data Karyawan
                $sql = "UPDATE employees SET employee_id=?, full_name=?, org_id=?, position_id=?, level_id=?, status=? WHERE id=?";
                $params = [$emp_id, $name, $org, $pos, $lvl, $status, $id];
                $action_text = "mengubah data karyawan: $name";
            } else {
                // Tambah Data Baru
                $sql = "INSERT INTO employees (employee_id, full_name, org_id, position_id, level_id, status) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$emp_id, $name, $org, $pos, $lvl, $status];
                $action_text = "menambah karyawan baru: $name";
            }
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                write_log($pdo, "Admin $action_text");
                header("Location: index.php?msg=success");
                exit();
            }
        } catch (PDOException $e) {
            $msg = "Error: " . $e->getMessage();
        }
    }
}

// =========================================
// 2. LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$id]);
    write_log($pdo, "Admin menghapus data karyawan ID: $id");
    header("Location: index.php?msg=deleted");
    exit();
}

// =========================================
// 3. PENGAMBILAN DATA
// =========================================
// Mengambil data master untuk dropdown
$orgs = $pdo->query("SELECT * FROM master_organizations ORDER BY org_name ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM master_job_positions ORDER BY position_name ASC")->fetchAll();
$levels = $pdo->query("SELECT * FROM master_job_levels ORDER BY level_name ASC")->fetchAll();

// Mengambil data karyawan dengan JOIN untuk mendapatkan label nama (bukan ID)
$sql_employees = "SELECT e.*, o.org_name, p.position_name, l.level_name 
                  FROM employees e 
                  LEFT JOIN master_organizations o ON e.org_id = o.id 
                  LEFT JOIN master_job_positions p ON e.position_id = p.id 
                  LEFT JOIN master_job_levels l ON e.level_id = l.id 
                  ORDER BY e.full_name ASC";
$employees = $pdo->query($sql_employees)->fetchAll();

// Ambil data untuk mode EDIT
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
        .action-container { display: flex; gap: 8px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <h2 style="color: var(--primary-blue); margin-bottom: 25px;">Employee Data Management</h2>

            <div class="form-card">
                <h4 style="margin-top: 0; margin-bottom: 20px;"><?= $edit_data ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' ?></h4>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <div class="grid-form">
                        <div class="form-group">
                            <label>NIK (Employee ID)</label>
                            <input type="text" name="employee_id" class="form-control" value="<?= $edit_data['employee_id'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="<?= $edit_data['full_name'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Organization</label>
                            <select name="org_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($orgs as $o): ?>
                                    <option value="<?= $o['id'] ?>" <?= (isset($edit_data['org_id']) && $edit_data['org_id'] == $o['id']) ? 'selected' : '' ?>><?= $o['org_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Job Position</label>
                            <select name="position_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($positions as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($edit_data['position_id']) && $edit_data['position_id'] == $p['id']) ? 'selected' : '' ?>><?= $p['position_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Job Level</label>
                            <select name="level_id" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($levels as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= (isset($edit_data['level_id']) && $edit_data['level_id'] == $l['id']) ? 'selected' : '' ?>><?= $l['level_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Active" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="submit" name="save_employee" class="btn-primary" style="width: auto; padding: 10px 30px;">
                            <?= $edit_data ? 'Update Karyawan' : 'Simpan Karyawan' ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="index.php" class="btn-primary" style="background: #94a3b8; width: auto; padding: 10px 30px; text-decoration: none;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Organization</th>
                        <th>Position</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($employees) > 0): ?>
                        <?php $no = 1; foreach($employees as $e): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $e['employee_id'] ?></strong></td>
                            <td><?= htmlspecialchars($e['full_name']) ?></td>
                            <td><?= $e['org_name'] ?></td>
                            <td><?= $e['position_name'] ?></td>
                            <td><?= $e['level_name'] ?></td>
                            <td>
                                <span class="badge <?= ($e['status'] == 'Active') ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $e['status'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-container">
                                    <a href="?edit=<?= $e['id'] ?>" class="btn-primary" style="padding: 5px 10px; font-size: 11px; background: #eef2ff; color: #4338ca;">Edit</a>
                                    <a href="?delete=<?= $e['id'] ?>" class="btn-primary" style="padding: 5px 10px; font-size: 11px; background: #fff1f2; color: #be123c;" onclick="return confirm('Hapus karyawan ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">Belum ada data karyawan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>