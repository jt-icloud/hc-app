<header class="header">
    <div style="display: flex; align-items: center;">
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="material-symbols-rounded" style="font-size: 28px;">menu</i>
        </div>

        <div class="page-title">
            <h4 style="margin: 0; font-weight: 600; color: var(--primary-blue);">HC-APP</h4>
        </div>
    </div>

    <div class="user-profile" style="display: flex; align-items: center; gap: 12px;">
        <div class="user-info" style="text-align: right; line-height: 1.2;">
            <span class="user-name" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-main);">
                <?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?>
            </span>
            <span class="user-role" style="display: block; font-size: 10px; color: var(--text-muted); text-transform: uppercase;">
                <?= ($_SESSION['role_id'] == 1) ? 'Super Admin' : 'Karyawan' ?>
            </span>
        </div>
        
        <div style="position: relative;">
            <img src="../../assets/img/profil/<?= !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.png' ?>?t=<?= time() ?>" 
                 class="profile-img" 
                 alt="User Profile">
        </div>
        
        <a href="/modul/auth/logout.php" style="color: #ef4444; margin-left: 5px; text-decoration: none;" title="Logout">
            <i class="material-symbols-rounded" style="font-size: 22px;">power_settings_new</i>
        </a>
    </div>
</header>