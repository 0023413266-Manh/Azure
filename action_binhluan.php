<?php
session_start();
include 'admin/connect.php';

// Bẫy lỗi: Chưa đăng nhập thì cấm vào
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

// ==========================================
// 1. XỬ LÝ GỬI BÌNH LUẬN MỚI (Load lại trang)
// ==========================================
if (isset($_POST['submit_comment'])) {
    $sp_id = (int)$_POST['id_san_pham'];
    $noi_dung = $conn->real_escape_string($_POST['noi_dung']);

    if (!empty($noi_dung)) {
        $conn->query("INSERT INTO binh_luan (id_san_pham, id_nguoi_dung, noi_dung) VALUES ($sp_id, $uid, '$noi_dung')");
        $_SESSION['toast_msg'] = "Đã gửi bình luận thành công!";
        $_SESSION['toast_type'] = "success";
    }
    
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: " . $referer);
    exit();
}

// ==========================================
// 2. XỬ LÝ XÓA BÌNH LUẬN CỦA CHÍNH MÌNH (AJAX)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $bl_id = (int)$_POST['comment_id'];
    
    // Bẫy lỗi: Chỉ cho phép xóa nếu id_nguoi_dung khớp với người đang đăng nhập
    $check = $conn->query("SELECT id FROM binh_luan WHERE id = $bl_id AND id_nguoi_dung = $uid");
    
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM binh_luan WHERE id = $bl_id");
        echo 'success';
    } else {
        echo 'error'; // Không phải chủ nhân bình luận
    }
    exit();
}

// ==========================================
// 3. XỬ LÝ SỬA BÌNH LUẬN CỦA CHÍNH MÌNH (AJAX)
// ==========================================
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $bl_id = (int)$_POST['comment_id'];
    $noi_dung_moi = $conn->real_escape_string($_POST['noi_dung_moi']);
    
    // Bẫy lỗi: Chỉ cho phép sửa nếu id_nguoi_dung khớp với người đang đăng nhập
    $check = $conn->query("SELECT id FROM binh_luan WHERE id = $bl_id AND id_nguoi_dung = $uid");
    
    if ($check->num_rows > 0 && !empty($noi_dung_moi)) {
        $conn->query("UPDATE binh_luan SET noi_dung = '$noi_dung_moi' WHERE id = $bl_id");
        echo htmlspecialchars($noi_dung_moi); // Trả về nội dung mới đã làm sạch để hiển thị
    } else {
        echo 'error';
    }
    exit();
}
?>