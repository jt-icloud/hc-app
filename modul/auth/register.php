<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = $_POST['name'];
    $employee_id = $_POST['employee_id'];
    $email       = $_POST['email'];
    $phone       = $_POST['phone'];
    $password    = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR employee_id = ?");
        $check->execute([$email, $employee_id]);

        if ($check->rowCount() > 0) {
            $message = "Email atau NIK sudah terdaftar!";
            write_log($pdo, "Percobaan daftar gagal: Data ganda ($email / $employee_id)");
        } else {
            $sql = "INSERT INTO users (name, employee_id, email, phone, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$name, $employee_id, $email, $phone, $password])) {
                write_log($pdo, "Registrasi sukses: $name ($employee_id)");
                header("Location: login.php?registration=success");
                exit();
            }
        }
    } catch (PDOException $e) {
        $message = "Error sistem.";
        write_log($pdo, "Error Register: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HC-APP</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Daftar HC-APP</h2>
            <?php if ($message): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>NIK (Employee ID)</label>
                    <input type="text" name="employee_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary">Daftar</button>
            </form>
            <div style="text-align: center; margin-top: 25px; font-size: 14px;">
                <p>Sudah punya akun? <a href="login.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>