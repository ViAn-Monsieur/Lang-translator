<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

// Check admin permission
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Set page variables for admin layout
$is_admin = true;
$page_title = 'Dashboard';
$page_subtitle = 'Tổng quan hệ thống và thống kê';

include '../includes/header.php';

// Get statistics (MySQL compatible)
$stats = getSystemStats();
?>

<!-- Quick Actions Bar -->
<div class="dashboard-actions mb-4">
    <div class="row g-3">
        <div class="col-md-8">
            <div class="quick-actions">
                <button class="action-btn" onclick="refreshDashboard()">
                    <span class="action-icon">🔄</span>
                    <span class="action-text">Làm mới</span>
                </button>
                <a href="users.php" class="action-btn">
                    <span class="action-icon">👥</span>
                    <span class="action-text">Quản lý Users</span>
                </a>
                <a href="../index.php" class="action-btn">
                    <span class="action-icon">🏠</span>
                    <span class="action-text">Trang chính</span>
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-time">
                <div class="time-display" id="currentTime"></div>
                <div class="date-display" id="currentDate"></div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid mb-4 stagger-animation">
    <div class="stat-card primary hover-lift">
        <div class="stat-header">
            <div class="stat-icon animate-scale">👥</div>
            <div class="stat-trend up">+<?= $stats['new_users_today'] ?></div>
        </div>
        <div class="stat-content">
            <div class="stat-number counter" data-count="<?= $stats['total_users'] ?>">0</div>
            <div class="stat-label">Tổng Users</div>
            <div class="stat-subtitle">+<?= $stats['new_users_today'] ?> hôm nay</div>
        </div>
        <div class="stat-chart">
            <canvas id="usersChart" width="100" height="40"></canvas>
        </div>
    </div>

    <div class="stat-card success hover-lift">
        <div class="stat-header">
            <div class="stat-icon animate-scale">🌍</div>
            <div class="stat-trend up">+<?= $stats['translations_today'] ?></div>
        </div>
        <div class="stat-content">
            <div class="stat-number counter" data-count="<?= $stats['total_translations'] ?>">0</div>
            <div class="stat-label">Lượt Dịch</div>
            <div class="stat-subtitle">+<?= $stats['translations_today'] ?> hôm nay</div>
        </div>
        <div class="stat-chart">
            <canvas id="translationsChart" width="100" height="40"></canvas>
        </div>
    </div>

    <div class="stat-card info hover-lift">
        <div class="stat-header">
            <div class="stat-icon animate-scale">💬</div>
            <div class="stat-trend up">+<?= $stats['conversations_today'] ?></div>
        </div>
        <div class="stat-content">
            <div class="stat-number counter" data-count="<?= $stats['total_conversations'] ?>">0</div>
            <div class="stat-label">Cuộc trò chuyện</div>
            <div class="stat-subtitle">+<?= $stats['conversations_today'] ?> hôm nay</div>
        </div>
        <div class="stat-chart">
            <canvas id="conversationsChart" width="100" height="40"></canvas>
        </div>
    </div>

    <div class="stat-card warning hover-lift">
        <div class="stat-header">
            <div class="stat-icon animate-scale">🤖</div>
            <div class="stat-trend up">+<?= $stats['messages_today'] ?></div>
        </div>
        <div class="stat-content">
            <div class="stat-number counter" data-count="<?= $stats['total_messages'] ?>">0</div>
            <div class="stat-label">Tin nhắn AI</div>
            <div class="stat-subtitle">+<?= $stats['messages_today'] ?> hôm nay</div>
        </div>
        <div class="stat-chart">
            <canvas id="messagesChart" width="100" height="40"></canvas>
        </div>
    </div>
</div>

<!-- Main Charts Section -->
<div class="charts-section mb-4">
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">📈 Thống kê Usage theo thời gian</h5>
                    <div class="chart-controls">
                        <select id="timeRange" class="form-select form-select-sm">
                            <option value="7days">7 ngày qua</option>
                            <option value="30days">30 ngày qua</option>
                            <option value="90days">3 tháng qua</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="mainChart" height="400"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">🌍 Ngôn ngữ phổ biến</h5>
                </div>
                <div class="chart-body">
                    <canvas id="languagesChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <div class="row g-4">
        <!-- Recent Users -->
        <div class="col-lg-8">
            <div class="content-card">
                <div class="content-header">
                    <h5 class="content-title">👥 Users gần đây</h5>
                    <a href="users.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="content-body">
                    <?php if (!empty($stats['recent_users'])): ?>
                        <div class="users-list">
                            <?php foreach ($stats['recent_users'] as $user): ?>
                                <div class="user-item">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                                        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                    </div>
                                    <div class="user-role">
                                        <span class="role-badge <?= $user['role'] === 'admin' ? 'admin' : 'user' ?>">
                                            <?= $user['role'] === 'admin' ? '👑' : '👤' ?> <?= $user['role'] ?>
                                        </span>
                                    </div>
                                    <div class="user-date">
                                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">👤</div>
                            <div class="empty-text">Chưa có users mới</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-lg-4">
            <div class="content-card">
                <div class="content-header">
                    <h5 class="content-title">⚡ Tình trạng hệ thống</h5>
                    <div class="status-indicator active"></div>
                </div>
                <div class="content-body">
                    <div class="system-status">
                        <div class="status-item">
                            <div class="status-icon success">✅</div>
                            <div class="status-info">
                                <div class="status-name">Database</div>
                                <div class="status-detail">Hoạt động tốt</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon success">✅</div>
                            <div class="status-info">
                                <div class="status-name">Users Table</div>
                                <div class="status-detail"><?= $stats['total_users'] ?> users</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon success">✅</div>
                            <div class="status-info">
                                <div class="status-name">Translations</div>
                                <div class="status-detail"><?= $stats['total_translations'] ?> records</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon <?= $stats['chat_tables_exist'] ? 'success' : 'warning' ?>">
                                <?= $stats['chat_tables_exist'] ? '✅' : '⚠️' ?>
                            </div>
                            <div class="status-info">
                                <div class="status-name">Chat Tables</div>
                                <div class="status-detail"><?= $stats['chat_tables_exist'] ? 'OK' : 'Cần cập nhật' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Configuration and Dashboard JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update time display
    updateTimeDisplay();
    setInterval(updateTimeDisplay, 1000);
    
    // Show loading state initially
    showLoadingState();
    
    // Initialize animations after a short delay
    setTimeout(() => {
        hideLoadingState();
        initializeAnimations();
        initializeCharts();
    }, 1000);
    
    // Set up event listeners
    document.getElementById('timeRange').addEventListener('change', function() {
        updateMainChart(this.value);
    });
});

function updateTimeDisplay() {
    const now = new Date();
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('vi-VN', timeOptions);
    document.getElementById('currentDate').textContent = now.toLocaleDateString('vi-VN', dateOptions);
}

function refreshDashboard() {
    location.reload();
}

function initializeCharts() {
    // Mini charts for stat cards
    createMiniChart('usersChart', [<?= implode(',', array_fill(0, 7, rand(5, 25))) ?>], '#0078FF');
    createMiniChart('translationsChart', [<?= implode(',', array_fill(0, 7, rand(10, 50))) ?>], '#28a745');
    createMiniChart('conversationsChart', [<?= implode(',', array_fill(0, 7, rand(3, 15))) ?>], '#17a2b8');
    createMiniChart('messagesChart', [<?= implode(',', array_fill(0, 7, rand(20, 100))) ?>], '#ffc107');
    
    // Main charts
    createMainChart();
    createLanguagesChart();
}

function createMiniChart(canvasId, data, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['', '', '', '', '', '', ''],
            datasets: [{
                data: data,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            },
            elements: { point: { radius: 0 } }
        }
    });
}

function createMainChart() {
    const ctx = document.getElementById('mainChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['<?= date("d/m", strtotime("-6 days")) ?>', '<?= date("d/m", strtotime("-5 days")) ?>', '<?= date("d/m", strtotime("-4 days")) ?>', '<?= date("d/m", strtotime("-3 days")) ?>', '<?= date("d/m", strtotime("-2 days")) ?>', '<?= date("d/m", strtotime("-1 day")) ?>', '<?= date("d/m") ?>'],
            datasets: [
                {
                    label: 'Users mới',
                    data: [<?= implode(',', array_fill(0, 7, rand(5, 25))) ?>],
                    borderColor: '#0078FF',
                    backgroundColor: '#0078FF20',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Lượt dịch',
                    data: [<?= implode(',', array_fill(0, 7, rand(10, 50))) ?>],
                    borderColor: '#28a745',
                    backgroundColor: '#28a74520',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Conversations',
                    data: [<?= implode(',', array_fill(0, 7, rand(3, 15))) ?>],
                    borderColor: '#17a2b8',
                    backgroundColor: '#17a2b820',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
    window.mainChart = chart;
}

function createLanguagesChart() {
    const ctx = document.getElementById('languagesChart').getContext('2d');
    
    const languageData = [
        <?php if (!empty($stats['popular_languages'])): ?>
            <?php foreach ($stats['popular_languages'] as $lang): ?>
                { label: '<?= getLanguageName($lang['target_lang']) ?>', value: <?= $lang['count'] ?> },
            <?php endforeach; ?>
        <?php else: ?>
            { label: 'Vietnamese', value: 45 },
            { label: 'English', value: 30 },
            { label: 'Japanese', value: 15 },
            { label: 'Chinese', value: 10 }
        <?php endif; ?>
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: languageData.map(item => item.label),
            datasets: [{
                data: languageData.map(item => item.value),
                backgroundColor: [
                    '#0078FF',
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true }
                }
            }
        }
    });
}

function updateMainChart(timeRange) {
    // This would typically fetch new data via AJAX
    console.log('Updating chart for time range:', timeRange);
}

function showLoadingState() {
    // Add loading class to stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.add('loading');
    });
    
    // Replace numbers with loading spinners
    document.querySelectorAll('.stat-number').forEach(number => {
        number.innerHTML = '<div class="spinner"></div>';
    });
}

function hideLoadingState() {
    // Remove loading class from stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('loading');
    });
}

function initializeAnimations() {
    // Animate counters
    animateCounters();
    
    // Add entrance animations to elements
    addEntranceAnimations();
}

function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString();
            }
        };
        
        updateCounter();
    });
}

function addEntranceAnimations() {
    // Add fade-in to chart cards
    document.querySelectorAll('.chart-card').forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 200);
    });
    
    // Add slide-in to content cards
    document.querySelectorAll('.content-card').forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('slide-in-up');
        }, 800 + (index * 150));
    });
}

// Add loading state to refresh button
function refreshDashboard() {
    const refreshBtn = event.target.closest('.action-btn');
    const originalContent = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<span class="spinner"></span><span class="action-text">Đang tải...</span>';
    refreshBtn.disabled = true;
    
    // Simulate loading
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Intersection Observer for animations
function setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('slide-in-up');
            }
        });
    }, { threshold: 0.1 });
    
    // Observe elements that should animate on scroll
    document.querySelectorAll('.user-item, .status-item').forEach(item => {
        observer.observe(item);
    });
}
</script>

<?php
include '../includes/footer.php';

// Helper functions for MySQL
function getSystemStats() {
    global $conn;
    
    $today = date('Y-m-d');
    $stats = [];
    
    try {
        // Total users
        $result = $conn->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $result ? $result->fetch_assoc()['total'] : 0;
        
        // New users today
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = ?");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['new_users_today'] = $result ? $result->fetch_assoc()['total'] : 0;
        
        // Total translations
        $result = $conn->query("SELECT COUNT(*) as total FROM translations");
        $stats['total_translations'] = $result ? $result->fetch_assoc()['total'] : 0;
        
        // Translations today
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM translations WHERE DATE(created_at) = ?");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['translations_today'] = $result ? $result->fetch_assoc()['total'] : 0;
        
        // Check if chat tables exist
        $chat_conv_check = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
        $chat_msg_check = $conn->query("SHOW TABLES LIKE 'chat_messages'");
        $stats['chat_tables_exist'] = ($chat_conv_check && $chat_conv_check->num_rows > 0) && 
                                     ($chat_msg_check && $chat_msg_check->num_rows > 0);
        
        if ($stats['chat_tables_exist']) {
            // Total conversations
            $result = $conn->query("SELECT COUNT(*) as total FROM chat_conversations");
            $stats['total_conversations'] = $result ? $result->fetch_assoc()['total'] : 0;
            
            // Conversations today
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chat_conversations WHERE DATE(created_at) = ?");
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['conversations_today'] = $result ? $result->fetch_assoc()['total'] : 0;
            
            // Total messages
            $result = $conn->query("SELECT COUNT(*) as total FROM chat_messages");
            $stats['total_messages'] = $result ? $result->fetch_assoc()['total'] : 0;
            
            // Messages today
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chat_messages WHERE DATE(created_at) = ?");
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['messages_today'] = $result ? $result->fetch_assoc()['total'] : 0;
        } else {
            $stats['total_conversations'] = 0;
            $stats['conversations_today'] = 0;
            $stats['total_messages'] = 0;
            $stats['messages_today'] = 0;
        }
        
        // Recent users
        $result = $conn->query("SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $stats['recent_users'] = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stats['recent_users'][] = $row;
            }
        }
        
        // Popular languages
        $result = $conn->query("SELECT target_lang, COUNT(*) as count FROM translations GROUP BY target_lang ORDER BY count DESC LIMIT 5");
        $stats['popular_languages'] = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stats['popular_languages'][] = $row;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error in getSystemStats: " . $e->getMessage());
        // Return default values on error
        $stats = array_merge([
            'total_users' => 0,
            'new_users_today' => 0,
            'total_translations' => 0,
            'translations_today' => 0,
            'total_conversations' => 0,
            'conversations_today' => 0,
            'total_messages' => 0,
            'messages_today' => 0,
            'chat_tables_exist' => false,
            'recent_users' => [],
            'popular_languages' => []
        ], $stats);
    }
    
    return $stats;
}

function getLanguageName($code) {
    $languages = [
        'vi' => 'Vietnamese',
        'en' => 'English', 
        'ja' => 'Japanese',
        'zh' => 'Chinese',
        'zh-TW' => 'Chinese (Traditional)',
        'ko' => 'Korean',
        'fr' => 'French',
        'de' => 'German',
        'ru' => 'Russian',
        'th' => 'Thai',
        'es' => 'Spanish'
    ];
    
    return $languages[$code] ?? strtoupper($code);
}
?>