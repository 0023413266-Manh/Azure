<?php
session_start();
include 'admin/connect.php'; 

if (isset($_POST['dang_ky'])) {
    // Lấy dữ liệu và cắt bỏ khoảng trắng thừa
    $ho_ten = trim($_POST['ho_ten']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $ngay_sinh = $_POST['ngay_sinh']; // BỔ SUNG DÒNG NÀY
    $email = trim($_POST['email']);
    $mat_khau = $_POST['mat_khau'];
    $nhap_lai = $_POST['nhap_lai_mat_khau'];

    // LỚP BẪY LỖI 1: Kiểm tra rỗng
    if (empty($ho_ten) || empty($so_dien_thoai) || empty($mat_khau) || empty($nhap_lai)) {
        $_SESSION['toast_msg'] = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $_SESSION['toast_type'] = 'error';
    }
    // LỚP BẪY LỖI 2: Số điện thoại (10-11 số)
    elseif (!preg_match('/^[0-9]{10,11}$/', $so_dien_thoai)) {
        $_SESSION['toast_msg'] = 'Số điện thoại không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
    }
    // LỚP BẪY LỖI 3: Email (nếu có nhập)
    elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast_msg'] = 'Định dạng email không hợp lệ!';
        $_SESSION['toast_type'] = 'error';
    }
    // LỚP BẪY LỖI 4: Độ dài mật khẩu
    elseif (strlen($mat_khau) < 6) {
        $_SESSION['toast_msg'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $_SESSION['toast_type'] = 'error';
    }
    // LỚP BẪY LỖI 5: Khớp mật khẩu
    elseif ($mat_khau !== $nhap_lai) {
        $_SESSION['toast_msg'] = 'Mật khẩu nhập lại không khớp!';
        $_SESSION['toast_type'] = 'error';
    } 
    else {
        // Kiểm tra trùng tài khoản
        $check = $conn->query("SELECT * FROM nguoi_dung WHERE so_dien_thoai = '$so_dien_thoai' OR (email != '' AND email = '$email')");
        if ($check->num_rows > 0) {
            $_SESSION['toast_msg'] = 'Số điện thoại hoặc Email đã tồn tại!';
            $_SESSION['toast_type'] = 'error';
        } else {
            // Mã hóa mật khẩu an toàn bằng bcrypt (password_hash)
            $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);
            
            // Dùng Prepared Statement để chống SQL Injection
            $stmt = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, so_dien_thoai, ngay_sinh, email, mat_khau, vai_tro) VALUES (?, ?, ?, ?, ?, 'khach_hang')");
            $stmt->bind_param('sssss', $ho_ten, $so_dien_thoai, $ngay_sinh, $email, $mat_khau_hash);
            
            if ($stmt->execute()) {
                $_SESSION['toast_msg'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                $_SESSION['toast_type'] = 'success';
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['toast_msg'] = 'Lỗi hệ thống: ' . $conn->error;
                $_SESSION['toast_type'] = 'error';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - Timeless</title>
    <link rel="stylesheet" href="register.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <div class="register-container">
        <div class="header-section">
            <img src="image/logo.png" alt="Logo">
            <h2>Đăng ký</h2>
        </div>

        <p class="social-text">Đăng ký bằng tài khoản mạng xã hội</p>
        <div class="social-group">
            <button class="social-btn"><i class="fa-brands fa-google google-icon"></i></button>
            <button class="social-btn"><i class="fa-brands fa-facebook fa-facebook"></i></button>
        </div>

        <p class="divider">Hoặc điền thông tin sau</p>

        <form action="register.php" method="POST" class="form-section">
            <div class="section-title">Thông tin cá nhân</div>
            
            <div class="input-group">
                <label>Họ và tên</label>
                <input type="text" name="ho_ten" placeholder="Nhập họ và tên" required>
            </div>

            <div class="input-group">
                <label>Số điện thoại</label>
                <input type="text" name="so_dien_thoai" placeholder="Nhập số điện thoại" required>
            </div>

            <div class="input-group">
                <label>Ngày sinh</label>
                <input type="date" name="ngay_sinh" style="color: #666;">
            </div>

            <div class="input-group">
                <label>Email ( Không bắt buộc )</label>
                <input type="email" name="email" placeholder="Nhập email">
            </div>

            <div class="section-title" style="margin-top: 30px;">Tạo mật khẩu</div>

            <div class="input-group">
                <label>Nhập mật khẩu</label>
                <input type="password" name="mat_khau" placeholder="Nhập mật khẩu của bạn" required minlength="6">
            </div>
            <p class="password-note">*Mật khẩu tối thiểu 6 ký tự, có ít nhất 1 chữ số và 1 số</p>

            <div class="input-group">
                <label>Nhập lại mật khẩu</label>
                <input type="password" name="nhap_lai_mat_khau" placeholder="Nhập lại mật khẩu của bạn" required>
            </div>

            <p class="terms">
                Bằng việc Đăng ký, bạn đã đọc và đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a>
            </p>

            <div class="btn-footer">
                <a href="login.php" class="btn btn-back">Quay lại đăng nhập</a>
                <button type="submit" name="dang_ky" class="btn btn-submit">Hoàn tất đăng ký</button>
            </div>
        </form>
    </div>

    <?php include 'thongbao.php'; ?>

</body>
</html>