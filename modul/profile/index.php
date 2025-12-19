<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

// Proteksi: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$layout_dir = __DIR__ . '/../layout/';
$message = "";
$status_type = "";

// 1. Ambil data user terbaru dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Logika Update Profil (saat tombol simpan diklik)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $new_password = $_POST['password'];
    $photo_name = $user['profile_photo']; // Default gunakan foto lama

    try {
        // A. Proses Upload Foto (jika ada file yang dipilih)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $file_name = $_FILES['photo']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_ext)) {
                // Buat nama file unik: profile_ID_TIMESTAMP.ext
                $new_photo_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
                $upload_path = "../../assets/img/profil/" . $new_photo_name;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    // Hapus foto lama dari folder jika bukan 'default.png'
                    if ($user['profile_photo'] != 'default.png' && file_exists("../../assets/img/profil/" . $user['profile_photo'])) {
                        unlink("../../assets/img/profil/" . $user['profile_photo']);
                    }
                    $photo_name = $new_photo_name;
                }
            }
        }

        // B. Update Database
        if (!empty($new_password)) {
            // Jika ganti password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, password = ?, profile_photo = ? WHERE id = ?";
            $params = [$name, $email, $phone, $hashed_password, $photo_name, $user_id];
        } else {
            // Jika tidak ganti password
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, profile_photo = ? WHERE id = ?";
            $params = [$name, $email, $phone, $photo_name, $user_id];
        }

        $stmt_update = $pdo->prepare($sql);
        if ($stmt_update->execute($params)) {
            // --- KRUSIAL: Sinkronisasi Session agar Header langsung berubah ---
            $_SESSION['name'] = $name;
            $_SESSION['profile_photo'] = $photo_name;

            write_log($pdo, "User " . $name . " memperbarui informasi profil.");
            header("Location: index.php?msg=success");
            exit();
        }

    } catch (PDOException $e) {
        $message = "Terjadi kesalahan: " . $e->getMessage();
        $status_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

    <?php include $layout_dir . 'sidebar.php'; ?>
    <?php include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="content-card" style="max-width: 700px; margin: 0 auto;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 30px;">
                <i class="material-symbols-rounded" style="font-size: 32px; color: var(--primary-blue);">person_edit</i>
                <h2 style="margin: 0; color: var(--primary-blue);">Pengaturan Profil</h2>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; border: 1px solid #a7f3d0;">
                    Profil Anda telah berhasil diperbarui!
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 40px; background: #f8fafc; padding: 30px; border-radius: 20px;">
                    <img src="../../assets/img/profil/<?= $user['profile_photo'] ?>?t=<?= time() ?>" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 5px solid white; box-shadow: var(--shadow);">
                    
                    <div style="margin-top: 15px; text-align: center;">
                        <label for="photo-upload" style="cursor: pointer; color: var(--primary-blue); font-weight: 600; font-size: 14px;">
                            <i class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px;">photo_camera</i> Ganti Foto Profil
                        </label>
                        <input id="photo-upload" type="file" name="photo" style="display: none;" onchange="this.form.submit()">
                        <p style="font-size: 11px; color: #94a3b8; margin-top: 5px;">Format: JPG, JPEG, atau PNG</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Nomor Induk Karyawan (NIK)</label>
                        <input type="text" class="form-control" value="<?= $user['employee_id'] ?>" readonly style="background: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Perusahaan</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Ganti Password <span style="font-weight: 400; color: #94a3b8;">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password baru jika ingin ganti">
                    </div>
                </div>

                <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px; text-align: right;">
                    <button type="submit" class="btn-primary" style="width: 200px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>

    <?php include $layout_dir . 'footer.php'; ?>

</body>
</html>