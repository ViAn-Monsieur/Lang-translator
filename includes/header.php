<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Web Dịch Ngôn Ngữ</title>
    <link rel="stylesheet" href="<?= isset($is_admin) ? '../css/style.css' : 'css/style.css' ?>">
    <link rel="stylesheet" href="<?= isset($is_admin) ? '../css/footer.css' : 'css/footer.css' ?>">
    <link rel="stylesheet" href="<?= isset($is_admin) ? '../css/index.css' : 'css/index.css' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Chart.js CDN for admin pages -->
    <?php if (isset($is_admin) && $is_admin): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <?php endif; ?>
</head>
<body class="<?= isset($is_admin) && $is_admin ? 'admin-layout' : '' ?>">

<?php if (isset($is_admin) && $is_admin): ?>
    <!-- Admin Layout with Sidebar -->
    <?php include dirname(__FILE__) . '/admin_sidebar.php'; ?>
    
    <div class="admin-content">
        <header class="admin-header">
            <div class="header-left">
                <div class="page-title">
                    <h1 class="h3 mb-0"><?= isset($page_title) ? $page_title : 'Admin Dashboard' ?></h1>
                    <?php if (isset($page_subtitle)): ?>
                        <p class="text-muted mb-0"><?= $page_subtitle ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="header-right">
                <div class="admin-actions">
                    <div class="user-info">
                        <span class="text-muted">Xin chào, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="admin-main">
            <div class="container-fluid">
<?php else: ?>
    <!-- Regular Layout -->
    <div class="container">
        <nav class="d-flex justify-content-between align-items-center p-3 bg-secondary rounded mb-4">
            <div>
                <h1 class="h3 mb-0">🌍 Language Translator</h1>
            </div>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-muted me-3">Xin chào, <strong><?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Người dùng' ?></strong></span>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-sm btn-outline-primary me-2">👑 Admin</a>
                    <?php endif; ?>
                    <a href="history.php" class="btn btn-sm btn-outline-primary me-2">📜 Lịch sử</a>
                    <a href="bot.php" class="btn btn-sm btn-primary me-2">💬 Chatbot</a>
                    <a href="logout.php" class="btn btn-sm btn-secondary">🚪 Đăng xuất</a>
                <?php else: ?>
                    <a href="bot.php" class="btn btn-sm btn-primary me-2">💬 Chatbot</a>
                    <a href="login.php" class="btn btn-sm btn-primary me-2">🔑 Đăng nhập</a>
                    <a href="register.php" class="btn btn-sm btn-outline-primary">📝 Đăng ký</a>
                <?php endif; ?>
            </div>
        </nav>
<?php endif; ?>
