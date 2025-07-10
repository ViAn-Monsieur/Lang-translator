<?php
session_start();
require 'includes/db.php';

// Nếu chưa đăng nhập → về login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$lang_filter = $_GET['lang'] ?? '';

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM translations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header("Location: history.php");
    exit;
}

// Truy vấn lịch sử
$query = "SELECT * FROM translations WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($search !== '') {
    $query .= " AND (original LIKE ? OR translated LIKE ?)";
    $searchParam = "%" . $search . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if ($lang_filter !== '') {
    $query .= " AND target_lang = ?";
    $params[] = $lang_filter;
    $types .= "s";
}

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_records / $per_page);

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1">📜 Lịch sử dịch của bạn</h2>
            <p class="text-muted mb-0">Quản lý và tìm kiếm các bản dịch đã lưu</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php 
            ?>
            <div class="stat-badge">
                <span class="stat-number"><?= $total_records ?></span>
                <span class="stat-label">bản dịch</span>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">🔍 Tìm kiếm</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nhập từ khóa tìm kiếm..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">🌍 Ngôn ngữ đích</label>
                        <select name="lang" class="form-control">
                            <option value="">— Tất cả ngôn ngữ —</option>
                            <option value="en" <?= $lang_filter == 'en' ? 'selected' : '' ?>>🇺🇸 English</option>
                            <option value="vi" <?= $lang_filter == 'vi' ? 'selected' : '' ?>>🇻🇳 Tiếng Việt</option>
                            <option value="ja" <?= $lang_filter == 'ja' ? 'selected' : '' ?>>🇯🇵 日本語</option>
                            <option value="zh" <?= $lang_filter == 'zh' ? 'selected' : '' ?>>🇨🇳 中文</option>
                            <option value="ko" <?= $lang_filter == 'ko' ? 'selected' : '' ?>>🇰🇷 한국어</option>
                            <option value="fr" <?= $lang_filter == 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                            <option value="de" <?= $lang_filter == 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch</option>
                            <option value="ru" <?= $lang_filter == 'ru' ? 'selected' : '' ?>>🇷🇺 Русский</option>
                            <option value="th" <?= $lang_filter == 'th' ? 'selected' : '' ?>>🇹🇭 ไทย</option>
                            <option value="es" <?= $lang_filter == 'es' ? 'selected' : '' ?>>🇪🇸 Español</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                🔍 Lọc
                            </button>
                            <?php if ($search !== '' || $lang_filter !== ''): ?>
                                <a href="history.php" class="btn btn-outline-secondary">
                                    ✖️ Xóa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Section -->
<?php if ($result->num_rows === 0): ?>
    <!-- Empty State -->
    <div class="empty-state text-center py-5">
        <div class="empty-icon mb-3">
            <span style="font-size: 4rem;">📚</span>
        </div>
        <h4 class="text-muted mb-2">
            <?php if ($search !== '' || $lang_filter !== ''): ?>
                Không tìm thấy kết quả
            <?php else: ?>
                Chưa có bản dịch nào
            <?php endif; ?>
        </h4>
        <p class="text-muted mb-4">
            <?php if ($search !== '' || $lang_filter !== ''): ?>
                Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc
            <?php else: ?>
                Hãy bắt đầu dịch để xem lịch sử tại đây
            <?php endif; ?>
        </p>
        <?php if ($search !== '' || $lang_filter !== ''): ?>
            <a href="history.php" class="btn btn-outline-primary">
                🔄 Xem tất cả bản dịch
            </a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary">
                🌍 Bắt đầu dịch ngay
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Results Grid -->
    <div class="translations-grid stagger-animation">
        <?php $i = $offset + 1; while ($row = $result->fetch_assoc()): ?>
            <div class="translation-card card hover-lift mb-3">
                <div class="card-body">
                    <div class="translation-header d-flex justify-content-between align-items-start mb-3">
                        <div class="translation-number">
                            <span class="badge bg-secondary">#<?= $i++ ?></span>
                        </div>
                        <div class="translation-time">
                            <small class="text-muted">
                                <?php
                                $time_diff = time() - strtotime($row['created_at']);
                                if ($time_diff < 60) echo "Vừa xong";
                                elseif ($time_diff < 3600) echo floor($time_diff/60) . " phút trước";
                                elseif ($time_diff < 86400) echo floor($time_diff/3600) . " giờ trước";
                                else echo floor($time_diff/86400) . " ngày trước";
                                ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="translation-content">
                        <!-- Original Text -->
                        <div class="text-block mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="language-badge source">
                                    <?php
                                    $lang_map = [
                                        'en' => '🇺🇸 EN', 'vi' => '🇻🇳 VI', 'ja' => '🇯🇵 JA',
                                        'zh' => '🇨🇳 ZH', 'ko' => '🇰🇷 KO', 'fr' => '🇫🇷 FR',
                                        'de' => '🇩🇪 DE', 'ru' => '🇷🇺 RU', 'th' => '🇹🇭 TH',
                                        'es' => '🇪🇸 ES', 'auto' => '🔍 Auto'
                                    ];
                                    echo $lang_map[$row['source_lang']] ?? strtoupper($row['source_lang']);
                                    ?>
                                </span>
                                <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                        onclick="copyText('<?= htmlspecialchars($row['original'], ENT_QUOTES) ?>')">
                                    📋 Copy
                                </button>
                            </div>
                            <div class="text-content">
                                <?= nl2br(htmlspecialchars($row['original'])) ?>
                            </div>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="text-center mb-3">
                            <span class="translation-arrow">⬇️</span>
                        </div>
                        
                        <!-- Translated Text -->
                        <div class="text-block">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="language-badge target">
                                    <?= $lang_map[$row['target_lang']] ?? strtoupper($row['target_lang']) ?>
                                </span>
                                <button class="btn btn-sm btn-outline-primary copy-btn" 
                                        onclick="copyText('<?= htmlspecialchars($row['translated'], ENT_QUOTES) ?>')">
                                    📋 Copy
                                </button>
                            </div>
                            <div class="text-content translated">
                                <?= nl2br(htmlspecialchars($row['translated'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="translation-actions mt-3 pt-3 border-top d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-fill" 
                                onclick="retranslate('<?= htmlspecialchars($row['original'], ENT_QUOTES) ?>', '<?= $row['source_lang'] ?>', '<?= $row['target_lang'] ?>')">
                            🔄 Dịch lại
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="deleteTranslation(<?= $row['id'] ?>)">
                            🗑️ Xóa
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrapper mt-4">
        <nav aria-label="Pagination">
            <div class="d-flex justify-content-between align-items-center">
                <div class="pagination-info">
                    <small class="text-muted">
                        Hiển thị <?= (($page - 1) * $per_page) + 1 ?> - <?= min($page * $per_page, $total_records) ?> 
                        trong <?= $total_records ?> bản dịch
                    </small>
                </div>
                <ul class="pagination">
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $base_url = 'history.php?' . http_build_query($query_params);
                    $separator = empty($query_params) ? '?' : '&';
                    
                    // Previous button
                    if ($page > 1):
                    ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base_url . $separator ?>page=<?= $page - 1 ?>">
                                ← Trước
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    // Page numbers
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1):
                    ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base_url . $separator ?>page=1">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $base_url . $separator ?>page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base_url . $separator ?>page=<?= $total_pages ?>"><?= $total_pages ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next button -->
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $base_url . $separator ?>page=<?= $page + 1 ?>">
                                Sau →
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- JavaScript for interactions -->
<script>
// Copy text functionality
function copyText(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        showToast('📋 Đã copy vào clipboard!', 'success');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        showToast('❌ Không thể copy!', 'error');
    });
}

// Delete translation with confirmation
function deleteTranslation(id) {
    if (confirm('🗑️ Bạn có chắc muốn xóa bản dịch này?\n\nHành động này không thể hoàn tác.')) {
        window.location.href = 'history.php?delete=' + id;
    }
}

// Retranslate functionality
function retranslate(original, sourceLang, targetLang) {
    // Store data in sessionStorage and redirect to main page
    sessionStorage.setItem('retranslate_data', JSON.stringify({
        text: original,
        source: sourceLang,
        target: targetLang
    }));
    window.location.href = 'index.php';
}

// Toast notification system
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        font-weight: 500;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>
