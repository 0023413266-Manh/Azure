<?php
session_start();
include 'admin/connect.php'; // Trỏ đúng kết nối CSDL của bạn

if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];

    // 1. Giải mã & Xác thực Token với Google Cloud API
    $verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = @file_get_contents($verify_url);

    if ($response !== FALSE) {
        $user_info = json_decode($response, true);

        $email     = $user_info['email'];
        $full_name = $user_info['name'];

        // 2. Tìm người dùng trong bảng nguoi_dung
        $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE email = ? AND vai_tro = 'khach_hang'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Đã có tài khoản -> Lấy dữ liệu
            $row = $result->fetch_assoc();
        } else {
            // Chưa có tài khoản -> Tự động tạo mới
            $default_role = 'khach_hang';
            $stmt_insert = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, email, vai_tro) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $full_name, $email, $default_role);
            $stmt_insert->execute();

            $user_id = $stmt_insert->insert_id;
            
            // Lấy lại thông tin user mới tạo
            $stmt_new = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
            $stmt_new->bind_param("i", $user_id);
            $stmt_new->execute();
            $row = $stmt_new->get_result()->fetch_assoc();
        }

        // 3. Đặt các biến Session khớp 100% với login.php
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['ho_ten']  = $row['ho_ten'];

        if (!empty($row['du_lieu_gio_hang'])) {
            $_SESSION['cart'] = json_decode($row['du_lieu_gio_hang'], true);
        } else {
            $_SESSION['cart'] = array();
        }

        $_SESSION['toast_msg']  = 'Đăng nhập Google thành công! Chào mừng ' . $row['ho_ten'];
        $_SESSION['toast_type'] = 'success';

        // 🟢 4. GHI LOG TỰ ĐỘNG VÀO AZURE FILE SHARE
        $log_dir = __DIR__ . '/shared/logs/';
        if (!file_exists($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        $log_file = $log_dir . 'app_log_' . date('Y-m-d') . '.log';
        $log_msg  = "[" . date('Y-m-d H:i:s') . "] [GOOGLE_CLOUD_AUTH] User: $email (ID: {$row['id']}) dang nhap thanh cong." . PHP_EOL;
        @file_put_contents($log_file, $log_msg, FILE_APPEND);

        // 5. Chuyển hướng về Trang chủ
        header("Location: index.php");
        exit();

    } else {
        $_SESSION['toast_msg']  = 'Xác thực Google Cloud thất bại!';
        $_SESSION['toast_type'] = 'error';
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>