<?php
// Pastikan ada tag <?php di baris pertama
function write_log($pdo, $activity) {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $sql = "INSERT INTO logs (user_id, activity, ip_address) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $activity, $ip]);
    } catch (PDOException $e) {
        // Jika log gagal, jangan hentikan aplikasi, cukup abaikan atau catat ke error_log php
        error_log("Gagal menulis log: " . $e->getMessage());
    }
}
?>