<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR employee_id = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // MENYIMPAN DATA KE SESSION
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['is_verified'] = $user['is_verified'];
            $_SESSION['profile_photo'] = $user['profile_photo'];
            
            write_log($pdo, "User " . $user['name'] . " berhasil login.");
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            write_log($pdo, "Gagal login: Percobaan dengan identifier: " . $identifier);
            $error = "Email/NIK atau password salah!";
        }
    } catch (PDOException $e) { $error = "Kesalahan sistem."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HC-APP</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Login HC-APP</h2>
            
            <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                    Registrasi berhasil! Silakan login.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email atau NIK</label>
                    <input type="text" name="identifier" class="form-control" placeholder="Masukkan Email atau NIK" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                </div>
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
            
            <div style="text-align: center; margin-top: 25px; font-size: 14px;">
                <p>Belum punya akun? <a href="register.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">Daftar di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>