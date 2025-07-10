<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Xử lý hành động (phân quyền, khóa, xóa)
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_SESSION['user_id'] == $id) {
        header("Location: users.php");
        exit;
    }

    switch ($_GET['action']) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;
        case 'promote':
            $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;
        case 'demote':
            $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;
        case 'toggle':
            $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;
    }
    header("Location: users.php");
    exit;
}

// Phân trang
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$search = $_GET['search'] ?? '';
$searchSql = $search ? "WHERE email LIKE CONCAT('%', ?, '%')" : '';
$countStmt = $search
    ? $conn->prepare("SELECT COUNT(*) FROM users WHERE email LIKE CONCAT('%', ?, '%')")
    : $conn->prepare("SELECT COUNT(*) FROM users");

if ($search) $countStmt->bind_param("s", $search);
$countStmt->execute();
$countStmt->bind_result($totalUsers);
$countStmt->fetch();
$countStmt->close();

$totalPages = ceil($totalUsers / $limit);

// Truy vấn user
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email LIKE CONCAT('%', ?, '%') ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f4f6f9; }
    .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .table td, .table th { vertical-align: middle; }
    .badge-user { background-color: #6c757d; }
    .badge-admin { background-color: #28a745; }
  </style>
</head>
<body>
  <div class="container">
    <h3 class="mb-4">🔐 Quản lý người dùng</h3>

    <form class="d-flex mb-3" method="get">
      <input type="text" name="search" class="form-control me-2" placeholder="Tìm theo email..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-primary">Tìm</button>
    </form>

    <div class="table-responsive">
      <table class="table table-hover table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = $offset + 1; while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><span class="badge <?= $row['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"> <?= $row['role'] === 'admin' ? '👑 Admin' : '👤 User' ?> </span></td>
            <td><span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-danger' ?>"> <?= $row['is_active'] ? 'Hoạt động' : 'Bị khóa' ?> </span></td>
            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
            <td>
              <?php if ($_SESSION['user_id'] !== $row['id']): ?>
              <div class="btn-group btn-group-sm">
                <?php if ($row['role'] === 'user'): ?>
                  <a href="?action=promote&id=<?= $row['id'] ?>" class="btn btn-success" title="Nâng quyền Admin">↑</a>
                <?php else: ?>
                  <a href="?action=demote&id=<?= $row['id'] ?>" class="btn btn-warning" title="Hạ quyền User">↓</a>
                <?php endif; ?>
                <a href="?action=toggle&id=<?= $row['id'] ?>" class="btn btn-secondary" title="Khóa/Mở"> <?= $row['is_active'] ? '🔒' : '🔓' ?> </a>
                <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Xác nhận xóa người dùng này?')">🗑</a>
              </div>
              <?php else: ?> <em class="text-muted">Bạn</em> <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

    <a href="dashboard.php" class="btn btn-outline-primary mt-3">← Về Dashboard</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
