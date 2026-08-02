<?php
session_start();
include 'admin/connect.php'; // Sửa lại đường dẫn file connect cho đúng với máy bác nhé

// 1. Cất giỏ hàng vào Database TRƯỚC KHI xóa Session
if (isset($_SESSION['user_id']) && isset($_SESSION['cart'])) {
    $uid = $_SESSION['user_id'];
    $cart_json = json_encode($_SESSION['cart']); // Đóng gói giỏ hàng thành chuỗi
    $conn->query("UPDATE nguoi_dung SET du_lieu_gio_hang = '$cart_json' WHERE id = $uid");
}

// 2. Xóa Session và đăng xuất
session_unset();
session_destroy();
header("Location: index.php");
exit();
?>