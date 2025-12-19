<header class="header">
    <div class="page-title">
        <h4 style="margin: 0; font-weight: 600; color: var(--primary-blue);">Dashboard Sistem</h4>
    </div>

    <div class="user-profile" style="display: flex; align-items: center; gap: 15px;">
        <div class="user-info" style="text-align: right; line-height: 1.2;">
            <span class="user-name" style="display: block; font-size: 14px; font-weight: 600; color: var(--text-main);">
                <?= htmlspecialchars($_SESSION['name']) ?>
            </span>
            <span class="user-role" style="display: block; font-size: 11px; color: var(--text-muted);">
                <?= ($_SESSION['role_id'] == 1) ? 'Super Admin' : 'Karyawan' ?>
            </span>
        </div>
        
        <div style="position: relative;">
            <img src="../../assets/img/profil/<?= !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.png' ?>?t=<?= time() ?>" 
                 class="profile-img" 
                 alt="User Profile"
                 style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
        </div>
        
        <a href="/modul/auth/logout.php" class="logout-icon" title="Logout" style="color: #ef4444; margin-left: 10px; text-decoration: none;">
            <i class="material-symbols-rounded" style="font-size: 24px;">power_settings_new</i>
        </a>
    </div>
</header>