<?php
// includes/functions.php

// ✅ Kiểm tra xem người dùng đã đăng nhập chưa
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// ✅ Kiểm tra xem có phải admin không
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ✅ Lấy tên người dùng hiện tại
function currentUsername() {
    return $_SESSION['username'] ?? 'Khách';
}

// ✅ Format ngày tháng (từ DB → hiển thị)
function formatDate($datetime) {
    return date("H:i d/m/Y", strtotime($datetime));
}

// ✅ Escape dữ liệu để tránh XSS
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
