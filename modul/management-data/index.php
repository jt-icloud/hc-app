<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';
$active_tab = $_GET['tab'] ?? 'org';
$msg = "";

// =========================================
// LOGIKA PROSES (POST)
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Tab 1: Organization
        if (isset($_POST['save_org'])) {
            $id = $_POST['id'] ?? null;
            $name = $_POST['org_name'];
            if ($id) {
                $pdo->prepare("UPDATE master_organizations SET org_name=? WHERE id=?")->execute([$name, $id]);
            } else {
                $pdo->prepare("INSERT INTO master_organizations (org_name) VALUES (?)")->execute([$name]);
            }
            write_log($pdo, "Mengelola Data Organization: $name");
            header("Location: index.php?tab=org&msg=success"); exit();
        }

        // Tab 2: Job Level
        if (isset($_POST['save_level'])) {
            $id = $_POST['id'] ?? null;
            $name = $_POST['level_name'];
            if ($id) {
                $pdo->prepare("UPDATE master_job_levels SET level_name=? WHERE id=?")->execute([$name, $id]);
            } else {
                $pdo->prepare("INSERT INTO master_job_levels (level_name) VALUES (?)")->execute([$name]);
            }
            write_log($pdo, "Mengelola Data Job Level: $name");
            header("Location: index.php?tab=level&msg=success"); exit();
        }

        // Tab 3: Job Position
        if (isset($_POST['save_position'])) {
            $id = $_POST['id'] ?? null;
            $name = $_POST['position_name'];
            if ($id) {
                $pdo->prepare("UPDATE master_job_positions SET position_name=? WHERE id=?")->execute([$name, $id]);
            } else {
                $pdo->prepare("INSERT INTO master_job_positions (position_name) VALUES (?)")->execute([$name]);
            }
            write_log($pdo, "Mengelola Data Job Position: $name");
            header("Location: index.php?tab=position&msg=success"); exit();
        }

        // Tab 4: Trainer
        if (isset($_POST['save_trainer'])) {
            $id = $_POST['id'] ?? null;
            $nik = $_POST['employee_id'];
            $name = $_POST['trainer_name'];
            $org = $_POST['org_id'];
            $pos = $_POST['position_id'];
            if ($id) {
                $pdo->prepare("UPDATE master_trainers SET employee_id=?, trainer_name=?, org_id=?, position_id=? WHERE id=?")->execute([$nik, $name, $org, $pos, $id]);
            } else {
                $pdo->prepare("INSERT INTO master_trainers (employee_id, trainer_name, org_id, position_id) VALUES (?, ?, ?, ?)")->execute([$nik, $name, $org, $pos]);
            }
            write_log($pdo, "Mengelola Data Trainer: $name");
            header("Location: index.php?tab=trainer&msg=success"); exit();
        }
    } catch (PDOException $e) { $msg = $e->getMessage(); }
}

// =========================================
// LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['del_org'])) { $pdo->prepare("DELETE FROM master_organizations WHERE id=?")->execute([$_GET['del_org']]); header("Location: index.php?tab=org&msg=deleted"); exit(); }
if (isset($_GET['del_level'])) { $pdo->prepare("DELETE FROM master_job_levels WHERE id=?")->execute([$_GET['del_level']]); header("Location: index.php?tab=level&msg=deleted"); exit(); }
if (isset($_GET['del_pos'])) { $pdo->prepare("DELETE FROM master_job_positions WHERE id=?")->execute([$_GET['del_pos']]); header("Location: index.php?tab=position&msg=deleted"); exit(); }
if (isset($_GET['del_train'])) { $pdo->prepare("DELETE FROM master_trainers WHERE id=?")->execute([$_GET['del_train']]); header("Location: index.php?tab=trainer&msg=deleted"); exit(); }

// =========================================
// PENGAMBILAN DATA
// =========================================
$orgs = $pdo->query("SELECT * FROM master_organizations ORDER BY org_name ASC")->fetchAll();
$levels = $pdo->query("SELECT * FROM master_job_levels ORDER BY level_name ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM master_job_positions ORDER BY position_name ASC")->fetchAll();
$trainers = $pdo->query("SELECT t.*, o.org_name, p.position_name FROM master_trainers t LEFT JOIN master_organizations o ON t.org_id = o.id LEFT JOIN master_job_positions p ON t.position_id = p.id ORDER BY t.trainer_name ASC")->fetchAll();

$edit = null;
if (isset($_GET['edit_id'])) {
    $tbl = ['org' => 'master_organizations', 'level' => 'master_job_levels', 'position' => 'master_job_positions', 'trainer' => 'master_trainers'][$active_tab];
    $st = $pdo->prepare("SELECT * FROM $tbl WHERE id = ?");
    $st->execute([$_GET['edit_id']]);
    $edit = $st->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Management Data - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; overflow-x: auto; }
        .tab-link { padding: 12px 20px; text-decoration: none; color: #64748b; font-weight: 600; font-size: 13px; border-radius: 10px 10px 0 0; white-space: nowrap; }
        .tab-link.active { color: var(--primary-blue); border-bottom: 2px solid var(--primary-blue); background: #f8fafc; }
        .form-card { background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .action-container { display: flex; gap: 5px; }
        .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; border: none; cursor: pointer; font-weight: 600; }
        .btn-edit { background: #eef2ff; color: #4338ca; }
        .btn-delete { background: #fff1f2; color: #be123c; }
        .data-table { width: 100%; border-collapse: collapse; background: white; }
        .data-table th { text-align: left; padding: 12px; background: #f8fafc; font-size: 11px; color: #64748b; text-transform: uppercase; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>
    <main class="main-content">
        <div class="content-card">
            <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Management Data Master</h2>
            <div class="tabs">
                <a href="?tab=org" class="tab-link <?= $active_tab == 'org' ? 'active' : '' ?>">Data Organization</a>
                <a href="?tab=level" class="tab-link <?= $active_tab == 'level' ? 'active' : '' ?>">Job Level</a>
                <a href="?tab=position" class="tab-link <?= $active_tab == 'position' ? 'active' : '' ?>">Job Position</a>
                <a href="?tab=trainer" class="tab-link <?= $active_tab == 'trainer' ? 'active' : '' ?>">Data Trainer</a>
            </div>

            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                        <?php if($active_tab == 'org'): ?>
                            <div class="form-group" style="margin:0"><label>Nama Departemen</label><input type="text" name="org_name" class="form-control" value="<?= $edit['org_name'] ?? '' ?>" required></div>
                            <button type="submit" name="save_org" class="btn-primary">Simpan Organization</button>
                        <?php elseif($active_tab == 'level'): ?>
                            <div class="form-group" style="margin:0"><label>Nama Level</label><input type="text" name="level_name" class="form-control" value="<?= $edit['level_name'] ?? '' ?>" required></div>
                            <button type="submit" name="save_level" class="btn-primary">Simpan Job Level</button>
                        <?php elseif($active_tab == 'position'): ?>
                            <div class="form-group" style="margin:0"><label>Nama Jabatan</label><input type="text" name="position_name" class="form-control" value="<?= $edit['position_name'] ?? '' ?>" required></div>
                            <button type="submit" name="save_position" class="btn-primary">Simpan Position</button>
                        <?php elseif($active_tab == 'trainer'): ?>
                            <div class="form-group" style="margin:0"><label>NIK</label><input type="text" name="employee_id" class="form-control" value="<?= $edit['employee_id'] ?? '' ?>" required></div>
                            <div class="form-group" style="margin:0"><label>Nama Trainer</label><input type="text" name="trainer_name" class="form-control" value="<?= $edit['trainer_name'] ?? '' ?>" required></div>
                            <div class="form-group" style="margin:0"><label>Departemen</label>
                                <select name="org_id" class="form-control" required><option value="">-- Pilih --</option>
                                <?php foreach($orgs as $o): ?><option value="<?= $o['id'] ?>" <?= (isset($edit['org_id']) && $edit['org_id'] == $o['id']) ? 'selected' : '' ?>><?= $o['org_name'] ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="form-group" style="margin:0"><label>Jabatan</label>
                                <select name="position_id" class="form-control" required><option value="">-- Pilih --</option>
                                <?php foreach($positions as $p): ?><option value="<?= $p['id'] ?>" <?= (isset($edit['position_id']) && $edit['position_id'] == $p['id']) ? 'selected' : '' ?>><?= $p['position_name'] ?></option><?php endforeach; ?>
                                </select></div>
                            <button type="submit" name="save_trainer" class="btn-primary">Simpan Trainer</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th width="50">No</th>
                    <?php if($active_tab == 'trainer'): ?><th>NIK</th><th>Nama Trainer</th><th>Departemen</th><th>Jabatan</th>
                    <?php else: ?><th>Nama Item</th><?php endif; ?>
                    <th width="150">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; if($active_tab == 'org'): foreach($orgs as $d): ?>
                        <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($d['org_name']) ?></td><td><div class="action-container"><a href="?tab=org&edit_id=<?= $d['id'] ?>" class="btn-sm btn-edit">Edit</a><a href="?tab=org&del_org=<?= $d['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus?')">Hapus</a></div></td></tr>
                    <?php endforeach; elseif($active_tab == 'level'): foreach($levels as $d): ?>
                        <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($d['level_name']) ?></td><td><div class="action-container"><a href="?tab=level&edit_id=<?= $d['id'] ?>" class="btn-sm btn-edit">Edit</a><a href="?tab=level&del_level=<?= $d['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus?')">Hapus</a></div></td></tr>
                    <?php endforeach; elseif($active_tab == 'position'): foreach($positions as $d): ?>
                        <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($d['position_name']) ?></td><td><div class="action-container"><a href="?tab=position&edit_id=<?= $d['id'] ?>" class="btn-sm btn-edit">Edit</a><a href="?tab=position&del_pos=<?= $d['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus?')">Hapus</a></div></td></tr>
                    <?php endforeach; elseif($active_tab == 'trainer'): foreach($trainers as $d): ?>
                        <tr><td><?= $no++ ?></td><td><?= $d['employee_id'] ?></td><td><strong><?= htmlspecialchars($d['trainer_name']) ?></strong></td><td><?= $d['org_name'] ?></td><td><?= $d['position_name'] ?></td><td><div class="action-container"><a href="?tab=trainer&edit_id=<?= $d['id'] ?>" class="btn-sm btn-edit">Edit</a><a href="?tab=trainer&del_train=<?= $d['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus?')">Hapus</a></div></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>