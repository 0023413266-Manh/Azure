<?php
session_start();
include 'admin/connect.php';

// 1. XỬ LÝ NÚT XÓA BÊN TRANG PROFILE (Có load lại trang)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
    
    $id_yt = (int)$_GET['id'];
    $uid = $_SESSION['user_id'];
    
    $conn->query("DELETE FROM yeu_thich WHERE id = $id_yt AND id_nguoi_dung = $uid");
    $_SESSION['toast_msg'] = "Đã xóa khỏi danh sách yêu thích!";
    $_SESSION['toast_type'] = "success";
    header("Location: profile.php");
    exit();
}

// 2. XỬ LÝ NÚT THẢ TIM BÊN TRANG CHI TIẾT (Dùng AJAX - Chạy ngầm không load trang)
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    if (!isset($_SESSION['user_id'])) { 
        echo 'not_logged_in'; 
        exit(); 
    }
    
    $sp_id = (int)$_GET['id'];
    $uid = $_SESSION['user_id'];
    
    // Kiểm tra xem khách đã thả tim món này chưa
    $check = $conn->query("SELECT * FROM yeu_thich WHERE id_nguoi_dung = $uid AND id_san_pham = $sp_id");
    if ($check && $check->num_rows > 0) {
        // Nếu có rồi thì XÓA (Bỏ thích)
        $conn->query("DELETE FROM yeu_thich WHERE id_nguoi_dung = $uid AND id_san_pham = $sp_id");
        echo 'removed';
    } else {
        // Nếu chưa có thì THÊM (Thả tim)
        $conn->query("INSERT INTO yeu_thich (id_nguoi_dung, id_san_pham) VALUES ($uid, $sp_id)");
        echo 'added';
    }
    exit();
}
?>