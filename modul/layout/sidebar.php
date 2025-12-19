<?php
require_once __DIR__ . '/../../config/database.php';
$current_uri = $_SERVER['REQUEST_URI'];
$role_id = $_SESSION['role_id'] ?? 0;

// Ambil menu berdasarkan hak akses role user
$query_menu = "SELECT m.* FROM menus m 
               JOIN role_access ra ON m.id = ra.menu_id 
               WHERE ra.role_id = ? 
               ORDER BY FIELD(m.menu_category, 'Dashboard', 'Data Area', 'Analysis Area', 'System Area'), m.order_no ASC";
$stmt_menu = $pdo->prepare($query_menu);
$stmt_menu->execute([$role_id]);
$menus = $stmt_menu->fetchAll();

$menu_list = [];
foreach ($menus as $m) {
    $menu_list[$m['menu_category']][] = $m;
}
?>

<aside class="sidebar">
    <div style="padding: 10px; margin-bottom: 20px; text-align: center;">
        <h3 style="color: var(--primary-blue); margin: 0; font-weight: 600;">HC-APP</h3>
    </div>

    <nav style="flex-grow: 1; overflow-y: auto;">
        <?php foreach ($menu_list as $category => $items): ?>
            <?php if ($category !== 'Dashboard'): ?>
                <p style="font-size: 11px; color: #aaa; text-transform: uppercase; margin: 20px 0 10px 12px; font-weight: 600;">
                    <?= htmlspecialchars($category) ?>
                </p>
            <?php endif; ?>
            
            <?php foreach ($items as $item): ?>
                <a href="<?= $item['menu_url'] ?>" 
                   class="menu-item <?= (strpos($current_uri, basename(dirname($item['menu_url']))) !== false) ? 'active' : '' ?>">
                    <i class="material-symbols-rounded"><?= $item['menu_icon'] ?></i> 
                    <span><?= htmlspecialchars($item['menu_name']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div style="padding-top: 10px; border-top: 1px solid #f1f5f9; margin-top: 10px;">
        <a href="../auth/logout.php" class="menu-item" style="color: #dc3545;">
            <i class="material-symbols-rounded">logout</i> 
            <span>Logout</span>
        </a>
    </div>
</aside>