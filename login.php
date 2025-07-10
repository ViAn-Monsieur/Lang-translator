<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // ✅ Chuyển hướng về trang chính
        header("Location: index.php");
        exit;
    } else {
        echo "❌ Sai thông tin đăng nhập.";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <div class="login-container">
    <h2 class="login-title">🔐 Đăng nhập</h2>
    <form method="POST" class="login-form" id="loginForm">
      <div class="form-group">
        <label for="email">📧 Email</label>
        <input type="email" name="email" id="email" class="form-input" required>
      </div>

      <div class="form-group">
        <label for="password">🔒 Mật khẩu</label>
        <input type="password" name="password" id="password" class="form-input" required>
      </div>

      <button type="submit" class="btn-login">🚪 Đăng nhập</button>
    </form>

    <p class="register-link">
      Chưa có tài khoản? <a href="register.php">Đăng ký</a>
    </p>
  </div>

</body>
</html>
