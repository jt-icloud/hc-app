<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi Sesi: Hanya Super Admin (ID: 1) yang boleh mengelola menu
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../dashboard/index.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';
$msg = "";

// =========================================
// 1. LOGIKA AKSI (POST) - SIMPAN & EDIT
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['menu_name'];
    $url = $_POST['menu_url'];
    $icon = $_POST['menu_icon'];
    $category = $_POST['menu_category'];
    $order = $_POST['order_no'];

    try {
        if ($id) {
            // Update Menu
            $sql = "UPDATE menus SET menu_name=?, menu_url=?, menu_icon=?, menu_category=?, order_no=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $url, $icon, $category, $order, $id]);
            write_log($pdo, "Mengubah menu aplikasi: $name");
        } else {
            // Tambah Menu Baru
            $sql = "INSERT INTO menus (menu_name, menu_url, menu_icon, menu_category, order_no) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $url, $icon, $category, $order]);
            
            // Otomatis berikan akses menu baru ini ke Super Admin (Role 1)
            $new_menu_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO role_access (role_id, menu_id) VALUES (1, ?)")->execute([$new_menu_id]);
            
            write_log($pdo, "Menambah menu aplikasi baru: $name");
        }
        header("Location: index.php?msg=success");
        exit();
    } catch (PDOException $e) { $msg = "Error: " . $e->getMessage(); }
}

// =========================================
// 2. LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);
    write_log($pdo, "Menghapus menu aplikasi ID: $id");
    header("Location: index.php?msg=deleted");
    exit();
}

// =========================================
// 3. PENGAMBILAN DATA
// =========================================
// Diurutkan berdasarkan kategori lalu nomor urutan
$all_menus = $pdo->query("SELECT * FROM menus ORDER BY FIELD(menu_category, 'Dashboard', 'Data Area', 'Analysis Area', 'System Area'), order_no ASC")->fetchAll();

$edit_data = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $st->execute([$_GET['edit']]);
    $edit_data = $st->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Management Menu - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .form-card { background: #f8fafc; padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .data-table th { background: #f8fafc; padding: 12px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .action-container { display: flex; gap: 8px; }
        .btn-sm { padding: 8px 15px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-edit { background: #eef2ff; color: #4338ca; }
        .btn-delete { background: #fff1f2; color: #be123c; }
        .icon-preview { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #f1f5f9; border-radius: 8px; color: var(--primary-blue); }
    </style>
</head>
<body>

    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="material-symbols-rounded" style="font-size: 32px; color: var(--primary-blue);">menu</i>
                <h2 style="margin: 0; color: var(--primary-blue);">Management Menu Aplikasi</h2>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px;">
                    Data menu berhasil diperbarui!
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h4 style="margin-top: 0; margin-bottom: 20px; color: #334155;"><?= $edit_data ? 'Edit Menu' : 'Tambah Menu Baru' ?></h4>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <div class="grid-form">
                        <div class="form-group">
                            <label>Nama Menu</label>
                            <input type="text" name="menu_name" class="form-control" placeholder="Contoh: Log Sistem" value="<?= $edit_data['menu_name'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>URL / Link</label>
                            <input type="text" name="menu_url" class="form-control" placeholder="../folder/index.php" value="<?= $edit_data['menu_url'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Icon (Material Symbols)</label>
                            <input type="text" name="menu_icon" class="form-control" placeholder="Contoh: settings" value="<?= $edit_data['menu_icon'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="menu_category" class="form-control" required>
                                <option value="Dashboard" <?= (isset($edit_data['menu_category']) && $edit_data['menu_category'] == 'Dashboard') ? 'selected' : '' ?>>Dashboard</option>
                                <option value="Data Area" <?= (isset($edit_data['menu_category']) && $edit_data['menu_category'] == 'Data Area') ? 'selected' : '' ?>>Data Area</option>
                                <option value="Analysis Area" <?= (isset($edit_data['menu_category']) && $edit_data['menu_category'] == 'Analysis Area') ? 'selected' : '' ?>>Analysis Area</option>
                                <option value="System Area" <?= (isset($edit_data['menu_category']) && $edit_data['menu_category'] == 'System Area') ? 'selected' : '' ?>>System Area</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nomor Urut</label>
                            <input type="number" name="order_no" class="form-control" value="<?= $edit_data['order_no'] ?? '1' ?>" required>
                        </div>
                    </div>
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="submit" class="btn-primary" style="width: auto; padding: 10px 30px;">
                            <?= $edit_data ? 'Update Menu' : 'Simpan Menu' ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="index.php" class="btn-sm" style="background: #94a3b8; color: white; height: 40px;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Kategori</th>
                        <th>Nama Menu</th>
                        <th>URL</th>
                        <th style="text-align: center;">Icon</th>
                        <th width="60">Urutan</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($all_menus as $m): 
                    ?>
                    <tr>
                        <td style="color: #94a3b8;"><?= $no++ ?></td>
                        <td><span style="font-size: 11px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 3px 8px; border-radius: 5px;"><?= $m['menu_category'] ?></span></td>
                        <td><strong><?= htmlspecialchars($m['menu_name']) ?></strong></td>
                        <td style="font-family: monospace; color: #64748b; font-size: 12px;"><?= $m['menu_url'] ?></td>
                        <td style="text-align: center;">
                            <div class="icon-preview">
                                <i class="material-symbols-rounded" style="font-size: 20px;"><?= $m['menu_icon'] ?></i>
                            </div>
                        </td>
                        <td style="text-align: center; font-weight: 600;"><?= $m['order_no'] ?></td>
                        <td>
                            <div class="action-container">
                                <a href="?edit=<?= $m['id'] ?>" class="btn-sm btn-edit">
                                    <i class="material-symbols-rounded" style="font-size: 16px;">edit</i> Edit
                                </a>
                                <a href="?delete=<?= $m['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus menu ini? Mengabaikan akses role terkait.')">
                                    <i class="material-symbols-rounded" style="font-size: 16px;">delete</i> Hapus
                                </a>
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