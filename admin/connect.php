<?php
// Thông tin kết nối CSDL
$host = "localhost";
$user = "root";       // Tên đăng nhập mặc định của XAMPP
$password = "";       // Mật khẩu mặc định của XAMPP là để trống
$database = "timeless"; // Tên database 

// Tạo kết nối
$conn = new mysqli($host, $user, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

// Bật chế độ tiếng Việt (UTF-8) để chữ không bị lỗi font
$conn->set_charset("utf8mb4");
