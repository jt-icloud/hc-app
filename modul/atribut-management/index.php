<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi Sesi: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';
$active_tab = $_GET['tab'] ?? 'activities';
$msg = "";

// =========================================
// 1. LOGIKA AKSI (POST) - SIMPAN & EDIT
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logika Simpan/Edit Activities
    if (isset($_POST['save_activity'])) {
        $id = $_POST['id'] ?? null;
        $name = $_POST['activity_name'];
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE attr_activities SET activity_name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                write_log($pdo, "Mengubah Activity: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO attr_activities (activity_name) VALUES (?)");
                $stmt->execute([$name]);
                write_log($pdo, "Menambah Activity baru: $name");
            }
            header("Location: index.php?tab=activities&msg=success");
            exit();
        } catch (PDOException $e) { $msg = $e->getMessage(); }
    }

    // Logika Simpan/Edit Skills
    if (isset($_POST['save_skill'])) {
        $id = $_POST['id'] ?? null;
        $name = $_POST['skill_name'];
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE attr_skills SET skill_name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                write_log($pdo, "Mengubah Skill: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO attr_skills (skill_name) VALUES (?)");
                $stmt->execute([$name]);
                write_log($pdo, "Menambah Skill baru: $name");
            }
            header("Location: index.php?tab=skills&msg=success");
            exit();
        } catch (PDOException $e) { $msg = $e->getMessage(); }
    }
}

// =========================================
// 2. LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['delete_act'])) {
    $pdo->prepare("DELETE FROM attr_activities WHERE id = ?")->execute([$_GET['delete_act']]);
    write_log($pdo, "Menghapus Activity ID: " . $_GET['delete_act']);
    header("Location: index.php?tab=activities&msg=deleted");
    exit();
}
if (isset($_GET['delete_skill'])) {
    $pdo->prepare("DELETE FROM attr_skills WHERE id = ?")->execute([$_GET['delete_skill']]);
    write_log($pdo, "Menghapus Skill ID: " . $_GET['delete_skill']);
    header("Location: index.php?tab=skills&msg=deleted");
    exit();
}

// =========================================
// 3. PENGAMBILAN DATA (ORDER BY A-Z)
// =========================================
$activities = $pdo->query("SELECT * FROM attr_activities ORDER BY activity_name ASC")->fetchAll();
$skills = $pdo->query("SELECT * FROM attr_skills ORDER BY skill_name ASC")->fetchAll();

// Data untuk form Edit
$edit_data = null;
if (isset($_GET['edit_act'])) {
    $st = $pdo->prepare("SELECT * FROM attr_activities WHERE id = ?");
    $st->execute([$_GET['edit_act']]);
    $edit_data = $st->fetch();
} elseif (isset($_GET['edit_skill'])) {
    $st = $pdo->prepare("SELECT * FROM attr_skills WHERE id = ?");
    $st->execute([$_GET['edit_skill']]);
    $edit_data = $st->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atribut Management - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; }
        .tab-link { padding: 12px 24px; text-decoration: none; color: #64748b; font-weight: 600; border-radius: 12px 12px 0 0; transition: 0.3s; position: relative; bottom: -2px; }
        .tab-link.active { color: var(--primary-blue); border-bottom: 2px solid var(--primary-blue); background: #f8fafc; }
        
        .attr-form-card { background: #f8fafc; padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #e2e8f0; max-width: 600px; }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
        .data-table th { text-align: left; padding: 15px; background: #f8fafc; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        /* Container tombol aksi agar sejajar horizontal */
        .action-container { display: flex; gap: 8px; justify-content: flex-start; align-items: center; }
        
        .btn-sm { padding: 8px 15px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-edit { background: #eef2ff; color: #4338ca; }
        .btn-edit:hover { background: #e0e7ff; }
        .btn-delete { background: #fff1f2; color: #be123c; }
        .btn-delete:hover { background: #ffe4e6; }
    </style>
</head>
<body>

    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <i class="material-symbols-rounded" style="font-size: 32px; color: var(--primary-blue);">settings_input_component</i>
                <h2 style="margin: 0; color: var(--primary-blue);">Atribut Management</h2>
            </div>

            <div class="tabs">
                <a href="?tab=activities" class="tab-link <?= $active_tab == 'activities' ? 'active' : '' ?>">Data Activities</a>
                <a href="?tab=skills" class="tab-link <?= $active_tab == 'skills' ? 'active' : '' ?>">Data Skill</a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; border: 1px solid #a7f3d0;">
                    <i class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px;">check_circle</i> Operasi Berhasil!
                </div>
            <?php endif; ?>

            <?php if ($active_tab == 'activities'): ?>
                <div class="attr-form-card">
                    <h4 style="margin: 0 0 15px 0; color: #334155;"><?= $edit_data ? 'Edit Activity' : 'Tambah Activity Baru' ?></h4>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="text" name="activity_name" class="form-control" placeholder="Contoh: Internal, External, etc." value="<?= $edit_data['activity_name'] ?? '' ?>" required>
                        <button type="submit" name="save_activity" class="btn-primary" style="width: auto; padding: 0 25px;">
                            <?= $edit_data ? 'Update' : 'Simpan' ?>
                        </button>
                        <?php if ($edit_data): ?> <a href="?tab=activities" class="btn-sm" style="background: #f1f5f9; color: #64748b; align-items: center;">Batal</a> <?php endif; ?>
                    </form>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Activity</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($activities) > 0): ?>
                            <?php $no = 1; foreach ($activities as $a): ?>
                            <tr>
                                <td style="color: #94a3b8;"><?= $no++ ?></td>
                                <td><span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($a['activity_name']) ?></span></td>
                                <td>
                                    <div class="action-container">
                                        <a href="?tab=activities&edit_act=<?= $a['id'] ?>" class="btn-sm btn-edit">
                                            <i class="material-symbols-rounded" style="font-size: 16px;">edit</i> Edit
                                        </a>
                                        <a href="?delete_act=<?= $a['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus data ini?')">
                                            <i class="material-symbols-rounded" style="font-size: 16px;">delete</i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada data activity.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($active_tab == 'skills'): ?>
                <div class="attr-form-card">
                    <h4 style="margin: 0 0 15px 0; color: #334155;"><?= $edit_data ? 'Edit Skill' : 'Tambah Skill Baru' ?></h4>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="text" name="skill_name" class="form-control" placeholder="Contoh: Soft Skill, Hard Skill, etc." value="<?= $edit_data['skill_name'] ?? '' ?>" required>
                        <button type="submit" name="save_skill" class="btn-primary" style="width: auto; padding: 0 25px;">
                            <?= $edit_data ? 'Update' : 'Simpan' ?>
                        </button>
                        <?php if ($edit_data): ?> <a href="?tab=skills" class="btn-sm" style="background: #f1f5f9; color: #64748b; align-items: center;">Batal</a> <?php endif; ?>
                    </form>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Skill</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($skills) > 0): ?>
                            <?php $no = 1; foreach ($skills as $s): ?>
                            <tr>
                                <td style="color: #94a3b8;"><?= $no++ ?></td>
                                <td><span style="font-weight: 600; color: #334155;"><?= htmlspecialchars($s['skill_name']) ?></span></td>
                                <td>
                                    <div class="action-container">
                                        <a href="?tab=skills&edit_skill=<?= $s['id'] ?>" class="btn-sm btn-edit">
                                            <i class="material-symbols-rounded" style="font-size: 16px;">edit</i> Edit
                                        </a>
                                        <a href="?delete_skill=<?= $s['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus data ini?')">
                                            <i class="material-symbols-rounded" style="font-size: 16px;">delete</i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada data skill.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>