<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Check admin permission
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">👑 Admin Panel</h1>
            <p class="text-muted">Chào mừng <?= htmlspecialchars($_SESSION['name']) ?> đến với trang quản trị</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as total FROM users");
                        echo number_format($result->fetch_assoc()['total']);
                        ?>
                    </h3>
                    <p class="mb-0">👥 Tổng Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as total FROM translations");
                        echo number_format($result->fetch_assoc()['total']);
                        ?>
                    </h3>
                    <p class="mb-0">🌍 Lượt Dịch</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">
                        <?php
                        // Check if chat tables exist
                        $table_check = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
                        if ($table_check->num_rows > 0) {
                            $result = $conn->query("SELECT COUNT(*) as total FROM chat_conversations");
                            echo number_format($result->fetch_assoc()['total']);
                        } else {
                            echo "0";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">💬 Cuộc trò chuyện</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?php
                        // Check if chat tables exist
                        $table_check = $conn->query("SHOW TABLES LIKE 'chat_messages'");
                        if ($table_check->num_rows > 0) {
                            $result = $conn->query("SELECT COUNT(*) as total FROM chat_messages");
                            echo number_format($result->fetch_assoc()['total']);
                        } else {
                            echo "0";
                        }
                        ?>
                    </h3>
                    <p class="mb-0">💭 Tin nhắn AI</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🛠️ Quản lý hệ thống</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="users.php" class="btn btn-primary">👥 Quản lý Users</a>
                        <a href="../update_database.php" class="btn btn-success" target="_blank">🔧 Cập nhật Database</a>
                        <a href="dashboard.php" class="btn btn-info">📊 Dashboard Nâng cao</a>
                        <a href="../chatbot_new.php" class="btn btn-secondary">💬 Test Chat Mới</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🎯 Trạng thái hệ thống</h5>
                </div>
                <div class="card-body">
                    <div class="system-status">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Database Connection:</span>
                            <span class="badge bg-success">✅ Hoạt động</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Users Table:</span>
                            <span class="badge bg-success">✅ OK</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Translations Table:</span>
                            <span class="badge bg-success">✅ OK</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Chat Tables:</span>
                            <?php
                            $chat_conv_exists = $conn->query("SHOW TABLES LIKE 'chat_conversations'")->num_rows > 0;
                            $chat_msg_exists = $conn->query("SHOW TABLES LIKE 'chat_messages'")->num_rows > 0;
                            
                            if ($chat_conv_exists && $chat_msg_exists) {
                                echo '<span class="badge bg-success">✅ OK</span>';
                            } else {
                                echo '<span class="badge bg-warning">⚠️ Cần cập nhật</span>';
                            }
                            ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>CSS/JS Files:</span>
                            <?php
                            $css_exists = file_exists('../css/style.css');
                            $js_exists = file_exists('../js/sidebar.js');
                            
                            if ($css_exists && $js_exists) {
                                echo '<span class="badge bg-success">✅ OK</span>';
                            } else {
                                echo '<span class="badge bg-danger">❌ Thiếu files</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🕒 Users gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                <?= $row['role'] === 'admin' ? '👑 Admin' : '👤 User' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($row['is_active'])): ?>
                                                <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $row['is_active'] ? '✅ Active' : '❌ Inactive' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info">❓ Unknown</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Instructions -->
    <div class="row mt-4">
        <div class="col">
            <div class="card bg-light">
                <div class="card-body">
                    <h5>📋 Hướng dẫn nhanh:</h5>
                    <ol>
                        <li><strong>Cập nhật Database</strong>: Click "🔧 Cập nhật Database" để tạo bảng chat mới</li>
                        <li><strong>Test Chat Sidebar</strong>: Vào "💬 Test Chat Mới" để test tính năng chat</li>
                        <li><strong>Quản lý Users</strong>: Click "👥 Quản lý Users" để quản lý người dùng</li>
                        <li><strong>Dashboard Nâng cao</strong>: Click "📊 Dashboard Nâng cao" để xem stats chi tiết</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard .card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.admin-dashboard .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.system-status {
    font-size: 0.9rem;
}

.badge {
    font-size: 0.75rem;
}
</style>

<?php include '../includes/footer.php'; ?>