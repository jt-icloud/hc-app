<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi: Hanya Super Admin (ID: 1) yang boleh akses [cite: 23, 26]
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../dashboard/index.php");
    exit();
}

$layout_dir = __DIR__ . '/../layout/';
$active_tab = $_GET['tab'] ?? 'users';
$msg = "";

// =========================================
// LOGIKA AKSI (POST)
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Logika CRUD User [cite: 25, 81-82]
    if (isset($_POST['save_user'])) {
        $id = $_POST['user_id'] ?? null;
        $name = $_POST['name'];
        $employee_id = $_POST['employee_id'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role_id = $_POST['role_id'];
        $is_verified = isset($_POST['is_verified']) ? 1 : 0;

        try {
            if ($id) {
                $sql = "UPDATE users SET name=?, employee_id=?, email=?, phone=?, role_id=?, is_verified=? WHERE id=?";
                $params = [$name, $employee_id, $email, $phone, $role_id, $is_verified, $id];
            } else {
                $pass = password_hash('123456', PASSWORD_BCRYPT);
                $sql = "INSERT INTO users (name, employee_id, email, phone, role_id, is_verified, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$name, $employee_id, $email, $phone, $role_id, $is_verified, $pass];
            }
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                write_log($pdo, "Super Admin mengelola data user: $name");
                header("Location: index.php?tab=users&msg=success");
                exit();
            }
        } catch (PDOException $e) { $msg = "Error User: " . $e->getMessage(); }
    }

    // 2. Logika CRUD Role [cite: 26, 83-84]
    if (isset($_POST['save_role'])) {
        $id = $_POST['role_id'] ?? null;
        $role_name = $_POST['role_name'];
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE roles SET role_name = ? WHERE id = ?");
                $stmt->execute([$role_name, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO roles (role_name) VALUES (?)");
                $stmt->execute([$role_name]);
            }
            write_log($pdo, "Super Admin mengelola role: $role_name");
            header("Location: index.php?tab=roles&msg=success");
            exit();
        } catch (PDOException $e) { $msg = "Error Role: " . $e->getMessage(); }
    }

    // 3. Logika Role Management (Hak Akses Menu) [cite: 27, 85-87]
    if (isset($_POST['save_access'])) {
        $role_id = $_POST['role_id'];
        $menus = $_POST['menu_ids'] ?? [];
        $pdo->prepare("DELETE FROM role_access WHERE role_id = ?")->execute([$role_id]);
        foreach ($menus as $menu_id) {
            $pdo->prepare("INSERT INTO role_access (role_id, menu_id) VALUES (?, ?)")->execute([$role_id, $menu_id]);
        }
        write_log($pdo, "Super Admin memperbarui hak akses untuk Role ID: $role_id");
        header("Location: index.php?tab=access&role_id=$role_id&msg=success");
        exit();
    }
}

// =========================================
// LOGIKA HAPUS (GET)
// =========================================
if (isset($_GET['delete_user'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_GET['delete_user']]);
    write_log($pdo, "Super Admin menghapus user ID: " . $_GET['delete_user']);
    header("Location: index.php?tab=users&msg=deleted");
    exit();
}
if (isset($_GET['delete_role'])) {
    $id = $_GET['delete_role'];
    $check = $pdo->prepare("SELECT id FROM users WHERE role_id = ? LIMIT 1");
    $check->execute([$id]);
    if ($check->fetch()) {
        header("Location: index.php?tab=roles&msg=error_used");
    } else {
        $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$id]);
        write_log($pdo, "Super Admin menghapus role ID: $id");
        header("Location: index.php?tab=roles&msg=deleted");
    }
    exit();
}

// =========================================
// PENGAMBILAN DATA
// =========================================
$users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
$all_menus = $pdo->query("SELECT * FROM menus ORDER BY order_no ASC")->fetchAll();

$edit_data = null;
if (isset($_GET['edit_user'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit_user']]);
    $edit_data = $stmt->fetch();
}
$edit_role = null;
if (isset($_GET['edit_role'])) {
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$_GET['edit_role']]);
    $edit_role = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Management User - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .tab-link { padding: 10px 20px; text-decoration: none; color: #666; border-radius: 8px; font-weight: 500; }
        .tab-link.active { background: var(--primary-blue); color: white; }
        .form-container { background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .btn-sm { padding: 6px 10px; border-radius: 6px; font-size: 12px; text-decoration: none; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card">
            <h2 style="color: var(--primary-blue);">Management Sistem</h2>

            <div class="tabs">
                <a href="?tab=users" class="tab-link <?= $active_tab == 'users' ? 'active' : '' ?>">Management User</a>
                <a href="?tab=roles" class="tab-link <?= $active_tab == 'roles' ? 'active' : '' ?>">Role</a>
                <a href="?tab=access" class="tab-link <?= $active_tab == 'access' ? 'active' : '' ?>">Role Management</a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'error_used'): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px;">Gagal: Role masih digunakan oleh karyawan.</div>
            <?php endif; ?>

            <?php if ($active_tab == 'users'): ?>
                <div class="form-container">
                    <h4><?= $edit_data ? 'Edit User' : 'Tambah User' ?></h4>
                    <form method="POST" class="grid-form">
                        <input type="hidden" name="user_id" value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="text" name="name" class="form-control" placeholder="Nama" value="<?= $edit_data['name'] ?? '' ?>" required>
                        <input type="text" name="employee_id" class="form-control" placeholder="NIK" value="<?= $edit_data['employee_id'] ?? '' ?>" required>
                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $edit_data['email'] ?? '' ?>" required>
                        <input type="text" name="phone" class="form-control" placeholder="Telepon" value="<?= $edit_data['phone'] ?? '' ?>" required>
                        <select name="role_id" class="form-control">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= (isset($edit_data['role_id']) && $edit_data['role_id'] == $r['id']) ? 'selected' : '' ?>><?= $r['role_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="is_verified" <?= (isset($edit_data['is_verified']) && $edit_data['is_verified'] == 1) ? 'checked' : '' ?>> Terverifikasi
                        </label>
                        <button type="submit" name="save_user" class="btn-primary" style="padding: 10px;"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
                    </form>
                </div>
                <table>
                    <thead><tr><th>Nama</th><th>NIK</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['employee_id']) ?></td>
                            <td><?= htmlspecialchars($u['role_name']) ?></td>
                            <td><span class="badge <?= $u['is_verified'] ? 'status-verified' : 'status-pending' ?>"><?= $u['is_verified'] ? 'Verified' : 'Pending' ?></span></td>
                            <td>
                                <a href="?tab=users&edit_user=<?= $u['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <a href="?delete_user=<?= $u['id'] ?>" class="btn-sm" style="background: #fee2e2; color: #b91c1c;" onclick="return confirm('Hapus user?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($active_tab == 'roles'): ?>
                <div class="form-container" style="max-width: 400px;">
                    <h4><?= $edit_role ? 'Edit Role' : 'Tambah Role' ?></h4>
                    <form method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="role_id" value="<?= $edit_role['id'] ?? '' ?>">
                        <input type="text" name="role_name" class="form-control" placeholder="Nama Role" value="<?= $edit_role['role_name'] ?? '' ?>" required>
                        <button type="submit" name="save_role" class="btn-primary" style="width: auto; padding: 0 20px;">Simpan</button>
                    </form>
                </div>
                <table style="max-width: 600px;">
                    <thead><tr><th>ID</th><th>Nama Role</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($roles as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['role_name']) ?></strong></td>
                            <td>
                                <a href="?tab=roles&edit_role=<?= $r['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <a href="?delete_role=<?= $r['id'] ?>" class="btn-sm" style="background: #fee2e2; color: #b91c1c;" onclick="return confirm('Hapus role?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($active_tab == 'access'): ?>
                <div style="padding: 10px;">
                    <form method="GET" style="margin-bottom: 25px;">
                        <input type="hidden" name="tab" value="access">
                        <label>Pilih Role untuk Mengatur Akses Menu:</label><br>
                        <select name="role_id" class="form-control" style="width: 250px; display: inline-block; margin-top: 10px;" onchange="this.form.submit()">
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= (isset($_GET['role_id']) && $_GET['role_id'] == $r['id']) ? 'selected' : '' ?>><?= $r['role_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (isset($_GET['role_id']) && !empty($_GET['role_id'])): 
                        $selected_role = $_GET['role_id'];
                        $access = $pdo->prepare("SELECT menu_id FROM role_access WHERE role_id = ?");
                        $access->execute([$selected_role]);
                        $allowed = $access->fetchAll(PDO::FETCH_COLUMN);
                    ?>
                    <form method="POST">
                        <input type="hidden" name="role_id" value="<?= $selected_role ?>">
                        <table class="data-table">
                            <thead><tr><th>Akses</th><th>Nama Menu</th><th>Kategori</th></tr></thead>
                            <tbody>
                                <?php foreach ($all_menus as $m): ?>
                                <tr>
                                    <td><input type="checkbox" name="menu_ids[]" value="<?= $m['id'] ?>" <?= in_array($m['id'], $allowed) ? 'checked' : '' ?>></td>
                                    <td><i class="material-symbols-rounded" style="font-size: 18px; vertical-align: middle;">Small <?= $m['menu_icon'] ?></i> <?= $m['menu_name'] ?></td>
                                    <td><span style="font-size: 12px; color: #777;"><?= $m['menu_category'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="save_access" class="btn-primary" style="margin-top: 20px; width: 250px;">Simpan Hak Akses</button>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>