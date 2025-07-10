<?php
// $server_name = "sql309.infinityfree.com";
// $username = "if0_39420888";
// $password = "9NtO8Oyq6pGsD";
// $db_name = "if0_39420888_lang_translate";
$server_name = "localhost";
$username = "root";
$password = "root";
$db_name = "lang_translate";

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($server_name, $username, $password, $db_name);
// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Thiết lập mã hóa ký tự
$conn->set_charset("utf8mb4"); 
?>
