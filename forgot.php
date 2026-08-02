<?php
session_start();
include 'admin/connect.php';

$step = 1;
$error = '';

// =====================================
// BƯỚC 1: KIỂM TRA EMAIL / SĐT
// =====================================
if (isset($_POST['btn_step1'])) {
    $email_phone = $conn->real_escape_string(trim($_POST['email_phone']));
    
    // Tìm trong database xem có ai dùng SĐT hoặc Email này không
    $sql = "SELECT id FROM nguoi_dung WHERE email = '$email_phone' OR so_dien_thoai = '$email_phone' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['reset_user_id'] = $row['id'];
        
        // BẪY LỖI: Đặt lại cờ xác thực OTP (Reset an toàn)
        $_SESSION['otp_verified'] = false; 
        
        // Tạo mã OTP ngẫu nhiên 4 số
        $_SESSION['reset_otp'] = rand(1000, 9999); 
        
        // Chuyển sang bước 2
        $step = 2;
    } else {
        $error = "Không tìm thấy tài khoản nào khớp với Email/SĐT này!";
    }
}

// =====================================
// BƯỚC 2: XÁC THỰC MÃ OTP
// =====================================
if (isset($_POST['btn_step2'])) {
    // Ghép 4 ô input lại thành 1 chuỗi
    $otp_nhap = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'];
    
    if (isset($_SESSION['reset_otp']) && $otp_nhap == $_SESSION['reset_otp']) {
        // Đúng OTP, cấp "Giấy thông hành" và cho qua bước 3
        $_SESSION['otp_verified'] = true; 
        $step = 3;
    } else {
        $error = "Mã OTP không chính xác! Vui lòng thử lại.";
        $step = 2;
    }
}

// =====================================
// BƯỚC 3: CẬP NHẬT MẬT KHẨU MỚI (BẢO VỆ KÉP)
// =====================================
if (isset($_POST['btn_step3'])) {
    
    // BẪY LỖI: CHỐNG HACK NHẢY CÓC (Cực kỳ quan trọng)
    // Dù hacker có cố tình gửi form POST btn_step3, nếu không có cờ otp_verified thì bị chặn đứng
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_user_id'])) {
        die("<h3 style='color:red; text-align:center; margin-top:50px;'>Phát hiện gian lận! Yêu cầu bị từ chối.</h3>");
    }

    $pass1 = $_POST['new_pass'];
    $pass2 = $_POST['confirm_pass'];
    
    if ($pass1 === $pass2) {
        // Mã hóa mật khẩu an toàn bằng bcrypt (password_hash)
        $hashed_pass = password_hash($pass1, PASSWORD_DEFAULT);
        $uid = (int)$_SESSION['reset_user_id'];
        
        // Cập nhật mật khẩu mới vào database
        $sql_update = "UPDATE nguoi_dung SET mat_khau = '$hashed_pass' WHERE id = $uid";
        
        if ($conn->query($sql_update)) {
            // Xóa sạch dấu vết Session sau khi xong việc
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['otp_verified']);
            
            $_SESSION['toast_msg'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";
            $_SESSION['toast_type'] = "success";
            header("Location: login.php");
            exit();
        } else {
            // Báo lỗi cụ thể ra màn hình nếu Database bị trục trặc
            $error = "Lỗi hệ thống khi cập nhật: " . $conn->error;
            $step = 3;
        }
    } else {
        $error = "Mật khẩu nhập lại không khớp!";
        $step = 3;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body { padding-top: 0 !important; background-color: #f9f9f9; }
        .forgot-page-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .forgot-box { background: #fff; width: 450px; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        .step-container { display: none; }
        .step-container.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .step-title-inline { margin-bottom: 20px; }
        .step-title-inline img { width: 100px; margin-bottom: 15px; }
        .step-title-inline h2 { font-family: 'Playfair Display', serif; color: #b58b5a; font-size: 26px; }
        
        .auth-form-group { margin-bottom: 20px; text-align: left; }
        .auth-form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        .auth-form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 15px; outline: none; transition: 0.3s; }
        .auth-form-group input:focus { border-color: #b58b5a; }
        
        .otp-group { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; }
        .otp-input { width: 50px !important; height: 50px; text-align: center; font-size: 24px !important; font-weight: bold; border: 1px solid #ccc; border-radius: 8px; }
        .otp-input:focus { border-color: #b58b5a; box-shadow: 0 0 5px rgba(181, 139, 90, 0.5); }
        
        .btn-auth-primary { width: 100%; background: #b58b5a; color: #fff; border: none; padding: 14px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: 0.3s; }
        .btn-auth-primary:hover { background: #967045; }
        
        .error-msg { background: #fde8e8; color: #d9534f; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: bold; }
        .back-link { display: inline-block; margin-top: 20px; color: #888; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .back-link:hover { color: #b58b5a; }
        
        .otp-simulator { background: #eef2ff; border: 1px dashed #2b6cb0; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #004085; text-align: center;}
    </style>
</head>
<body>

    <div class="forgot-page-container">
        <div class="forgot-box">

            <?php if($error != ''): ?>
                <div class="error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div id="step1" class="step-container <?php if($step == 1) echo 'active'; ?>">
                <div class="step-title-inline">
                    <img src="image/logo.png" alt="Icon">
                    <h2>Quên mật khẩu</h2>
                </div>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Vui lòng nhập Email hoặc Số điện thoại đã đăng ký để nhận mã khôi phục.</p>
                
                <form method="POST" action="forgot.php">
                    <div class="auth-form-group">
                        <label>SĐT hoặc Email:</label>
                        <input type="text" name="email_phone" placeholder="Nhập sđt hoặc email của bạn" required>
                    </div>
                    <button type="submit" name="btn_step1" class="btn-auth-primary">Nhận mã OTP</button>
                </form>
            </div>

            <div id="step2" class="step-container <?php if($step == 2) echo 'active'; ?>">
                <div class="step-title-inline">
                    <img src="image/logo.png" alt="Icon">
                    <h2>Nhập mã xác nhận</h2>
                </div>
                
                <div class="otp-simulator">
                    <strong style="font-size: 12px; text-transform: uppercase;">[Mô phỏng tin nhắn SMS]</strong><br>
                    Mã OTP của bạn là: <span style="font-size: 24px; color: #d9534f; font-weight: bold; letter-spacing: 3px; display: block; margin-top: 5px;"><?php echo isset($_SESSION['reset_otp']) ? $_SESSION['reset_otp'] : 'Lỗi'; ?></span>
                </div>

                <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Vui lòng nhập mã gồm 4 số ở trên vào các ô bên dưới.</p>
                
                <form method="POST" action="forgot.php">
                    <div class="otp-group">
                        <input type="text" name="otp1" class="otp-input" maxlength="1" required>
                        <input type="text" name="otp2" class="otp-input" maxlength="1" required>
                        <input type="text" name="otp3" class="otp-input" maxlength="1" required>
                        <input type="text" name="otp4" class="otp-input" maxlength="1" required>
                    </div>
                    <button type="submit" name="btn_step2" class="btn-auth-primary">Xác nhận mã</button>
                </form>
            </div>

            <div id="step3" class="step-container <?php if($step == 3) echo 'active'; ?>">
                <div class="step-title-inline">
                    <img src="image/logo.png" alt="Icon">
                    <h2>Đặt lại mật khẩu</h2>
                </div>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Vui lòng tạo mật khẩu mới an toàn và dễ nhớ.</p>
                
                <form method="POST" action="forgot.php">
                    <div class="auth-form-group">
                        <label>Mật khẩu mới:</label>
                        <input type="password" name="new_pass" placeholder="Nhập mật khẩu mới" required minlength="6">
                    </div>
                    <div class="auth-form-group">
                        <label>Xác nhận mật khẩu:</label>
                        <input type="password" name="confirm_pass" placeholder="Nhập lại mật khẩu mới" required minlength="6">
                    </div>
                    <button type="submit" name="btn_step3" class="btn-auth-primary">Đổi mật khẩu</button>
                </form>
            </div>

            <a href="login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Quay lại Đăng nhập</a>
                
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/[^0-9]/g, '');
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
    </script>
    
    <?php include 'thongbao.php'; ?>

</body>
</html>