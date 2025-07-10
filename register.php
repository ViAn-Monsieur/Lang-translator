<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra trùng email
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>❌ Email đã tồn tại!</p>";
    } else {
        // Thêm người dùng mới
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();

        // Lấy ID người dùng vừa tạo
        $new_user_id = $stmt->insert_id;

        // Tự động đăng nhập
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['role'] = 'user'; 
        // Chuyển đến trang chính
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng ký</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>

  <div class="register-container">
  <h2 class="register-title">📝 Đăng ký tài khoản</h2>

  <form method="POST" class="register-form" id="registerForm">
    <div class="form-group">
      <label for="name">👤 Tên</label>
      <input type="text" name="name" id="name" class="form-input" required>
    </div>

    <div class="form-group">
      <label for="email">📧 Email</label>
      <input type="email" name="email" id="email" class="form-input" required>
    </div>

    <div class="form-group">
      <label for="password">🔒 Mật khẩu</label>
      <input type="password" name="password" id="password" class="form-input" required>
    </div>

    <button type="submit" class="btn-register">📩 Đăng ký</button>
  </form>

  <p class="login-link">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>

</body>
</html>

