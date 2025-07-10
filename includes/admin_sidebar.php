<?php
// Admin Sidebar Component
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
    <span class="sidebar-toggle-icon">
        <i class="sidebar-icon">☰</i>
    </span>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Admin Sidebar -->
<nav class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">👑</div>
            <div class="brand-text">
                <h5 class="brand-title">Admin Panel</h5>
                <span class="brand-subtitle">Language Translator</span>
            </div>
        </div>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
            <i class="close-icon">✕</i>
        </button>
    </div>

    <!-- Admin Profile Section -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
            </div>
        </div>
        <div class="profile-info">
            <h6 class="profile-name"><?= htmlspecialchars($_SESSION['name']) ?></h6>
            <span class="profile-role">Administrator</span>
        </div>
        <div class="profile-status">
            <div class="status-indicator active"></div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-nav">
        <ul class="nav-menu">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                    <div class="nav-icon">📊</div>
                    <span class="nav-text">Dashboard</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>

            <!-- Users Management -->
            <li class="nav-item">
                <a href="users.php" class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
                    <div class="nav-icon">👥</div>
                    <span class="nav-text">Quản lý Users</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>

            <!-- Translations -->
            <!-- <li class="nav-item">
                <a href="translations.php" class="nav-link <?= $current_page === 'translations.php' ? 'active' : '' ?>">
                    <div class="nav-icon">🌍</div>
                    <span class="nav-text">Dịch thuật</span>
                    <div class="nav-indicator"></div>
                </a>
            </li> -->

            <!-- Analytics -->
            <!-- <li class="nav-item">
                <a href="analytics.php" class="nav-link <?= $current_page === 'analytics.php' ? 'active' : '' ?>">
                    <div class="nav-icon">📈</div>
                    <span class="nav-text">Thống kê</span>
                    <div class="nav-indicator"></div>
                </a>
            </li> -->

            <!-- System Settings -->
            <!-- <li class="nav-item">
                <a href="settings.php" class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>">
                    <div class="nav-icon">⚙️</div>
                    <span class="nav-text">Cài đặt</span>
                    <div class="nav-indicator"></div>
                </a>
            </li> -->

            <!-- Divider -->
            <li class="nav-divider">
                <div class="divider-line"></div>
                <span class="divider-text">Khác</span>
            </li>

            <!-- Back to Main Site -->
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <div class="nav-icon">🏠</div>
                    <span class="nav-text">Trang chính</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <a href="../logout.php" class="nav-link logout-link">
                    <div class="nav-icon">🚪</div>
                    <span class="nav-text">Đăng xuất</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-stats">
            <div class="stat-item">
                <div class="stat-value" id="onlineUsers">-</div>
                <div class="stat-label">Online</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="totalTranslations">-</div>
                <div class="stat-label">Dịch</div>
            </div>
        </div>
        <div class="footer-version">
            <small>v1.0.0</small>
        </div>
    </div>
</nav>

<!-- Sidebar JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        sidebarToggle.classList.toggle('sidebar-active');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        sidebarToggle.classList.remove('sidebar-active');
        document.body.classList.remove('sidebar-open');
    }
    
    // Event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarClose.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);
    
    // Close sidebar on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
            // On desktop, keep sidebar open
            document.body.classList.add('sidebar-open');
        }
    });
    
    // Load quick stats for footer
    loadQuickStats();
    
    function loadQuickStats() {
        // This could be an AJAX call to get real-time stats
        // For now, we'll use placeholder values
        setTimeout(() => {
            document.getElementById('onlineUsers').textContent = '12';
            document.getElementById('totalTranslations').textContent = '1.2k';
        }, 1000);
    }
});
</script>