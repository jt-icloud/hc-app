<footer class="footer">
    <div style="font-size: 12px; color: var(--text-muted);">
        &copy; <?= date('Y') ?> <strong>Team HC</strong>. All Rights Reserved.
    </div>
</footer>

<script>
    /**
     * Fungsi untuk toggle class active pada sidebar
     * Digunakan untuk membuka/menutup menu di tampilan mobile
     */
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    }

    /**
     * Menutup sidebar secara otomatis jika user mengklik area konten utama
     * Sangat berguna untuk user experience di handphone
     */
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickInsideToggle = menuToggle.contains(event.target);

        if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });

    /**
     * Penyesuaian otomatis saat ukuran layar berubah (resize)
     * Menghapus class active jika layar kembali ke ukuran desktop
     */
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });
</script>