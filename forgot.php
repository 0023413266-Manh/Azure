<?php
session_start();
require_once 'env_loader.php';
require_once 'send_mail_gcp.php'; // Nạp file gửi mail Google Cloud API
include 'admin/connect.php';

$step = 1;
$error = '';

// =====================================
// BƯỚC 1: KIỂM TRA CAPTCHA & EMAIL / SĐT -> GỬI MAIL CLOUD
// =====================================
if (isset($_POST['btn_step1'])) {
    
    // 1. XÁC THỰC GOOGLE RECAPTCHA V2
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $recaptcha_secret   = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '6LdCn4ItAAAAAAHhm3OWqKM8lHS_J3Ndzz3y0gwgC';

    if (empty($recaptcha_response)) {
        $error = "Vui lòng tích chọn ô 'Tôi không phải là người máy'!";
        $step = 1;
    } else {
        // Gửi Yêu cầu xác thực tới Google Server
        $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}";
        $response_json = @file_get_contents($verify_url);
        $response_data = json_decode($response_json, true);

        if (!$response_data['success']) {
            $error = "Xác nhận Captcha thất bại! Vui lòng thử lại.";
            $step = 1;
        } else {
            // 2. CAPTCHA HỢP LỆ -> XỬ LÝ TÌM TÀI KHOẢN VÀ GỬI MAIL OTP
            $email_phone = $conn->real_escape_string(trim($_POST['email_phone']));
            
            $sql = "SELECT id, email FROM nguoi_dung WHERE email = '$email_phone' OR so_dien_thoai = '$email_phone' LIMIT 1";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $target_email = $row['email'];

                $_SESSION['reset_user_id'] = $row['id'];
                $_SESSION['reset_email']   = $target_email; 
                $_SESSION['otp_verified']  = false; 
                
                // Tạo mã OTP ngẫu nhiên 4 số
                $otp = rand(1000, 9999); 
                $_SESSION['reset_otp'] = $otp; 
                
                // GỌI DỊCH VỤ GOOGLE CLOUD ĐỂ GỬI EMAIL OTP
                if (sendOtpViaGoogleCloud($target_email, $otp)) {
                    $step = 2; // Qua bước 2 nhập OTP
                } else {
                    $error = "Lỗi hệ thống Google Cloud Email! Không thể gửi mã OTP. Vui lòng kiểm tra lại cấu hình.";
                    $step = 1;
                }

            } else {
                $error = "Không tìm thấy tài khoản nào khớp với Email/SĐT này!";
                $step = 1;
            }
        }
    }
}

// =====================================
// BƯỚC 2: XÁC THỰC MÃ OTP
// =====================================
if (isset($_POST['btn_step2'])) {
    $otp_nhap = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'];
    
    if (isset($_SESSION['reset_otp']) && $otp_nhap == $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = true; 
        $step = 3;
    } else {
        $error = "Mã OTP không chính xác! Vui lòng kiểm tra lại hòm thư Email.";
        $step = 2;
    }
}

// =====================================
// BƯỚC 3: CẬP NHẬT MẬT KHẨU MỚI
// =====================================
if (isset($_POST['btn_step3'])) {
    
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_user_id'])) {
        die("<h3 style='color:red; text-align:center; margin-top:50px;'>Phát hiện gian lận! Yêu cầu bị từ chối.</h3>");
    }

    $pass1 = $_POST['new_pass'];
    $pass2 = $_POST['confirm_pass'];
    
    if ($pass1 === $pass2) {
        $hashed_pass = password_hash($pass1, PASSWORD_DEFAULT);
        $uid = (int)$_SESSION['reset_user_id'];
        
        $sql_update = "UPDATE nguoi_dung SET mat_khau = '$hashed_pass' WHERE id = $uid";
        
        if ($conn->query($sql_update)) {
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['otp_verified']);
            
            $_SESSION['toast_msg'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";
            $_SESSION['toast_type'] = "success";
            header("Location: login.php");
            exit();
        } else {
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
    
    <!-- Script Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
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

            <!-- BƯỚC 1 -->
            <div id="step1" class="step-container <?php if($step == 1) echo 'active'; ?>">
                <div class="step-title-inline">
                    <img src="image/logo.png" alt="Icon">
                    <h2>Quên mật khẩu</h2>
                </div>
                <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Vui lòng nhập Email hoặc Số điện thoại đã đăng ký để nhận mã khôi phục qua Gmail.</p>
                
                <form method="POST" action="forgot.php">
                    <div class="auth-form-group">
                        <label>SĐT hoặc Email:</label>
                        <input type="text" name="email_phone" placeholder="Nhập sđt hoặc email của bạn" required>
                    </div>

                    <!-- Khung reCAPTCHA v2 -->
                    <div class="g-recaptcha" data-sitekey="6LdCn4ItAAAAACPhPcU-eo_YpsllyN0j9lpuRmEx" style="margin-bottom: 15px; display: flex; justify-content: center;"></div>

                    <button type="submit" name="btn_step1" class="btn-auth-primary">Nhận mã OTP qua Email</button>
                </form>
            </div>

            <!-- BƯỚC 2 -->
            <div id="step2" class="step-container <?php if($step == 2) echo 'active'; ?>">
                <div class="step-title-inline">
                    <img src="image/logo.png" alt="Icon">
                    <h2>Nhập mã xác nhận</h2>
                </div>
                
                <div class="otp-simulator" style="background: #e6fffa; border-color: #319795; color: #234e52;">
                    <i class="fa-solid fa-paper-plane" style="font-size: 20px; color: #319795; margin-bottom: 5px;"></i><br>
                    <strong style="font-size: 12px; text-transform: uppercase;">[Google Cloud Email Service]</strong><br>
                    Mã OTP 4 số đã được gửi thành công đến hòm thư:<br>
                    <b style="font-size: 15px; color: #2b6cb0; word-break: break-all;"><?php echo $_SESSION['reset_email'] ?? ''; ?></b>
                </div>

                <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Vui lòng mở hòm thư Email của bạn và nhập mã gồm 4 số vào ô bên dưới.</p>
                
                <form method="POST" action="forgot.php">
                    <div class="otp-group">
                        <input type="text" name="otp1" class="otp-input" maxlength="1" required autofocus>
                        <input type="text" name="otp2" class="otp-input" maxlength="1" required>
                        <input type="text" name="otp3" class="otp-input" maxlength="1" required>
                        <input type="text" name="otp4" class="otp-input" maxlength="1" required>
                    </div>
                    <button type="submit" name="btn_step2" class="btn-auth-primary">Xác nhận mã</button>
                </form>
            </div>

            <!-- BƯỚC 3 -->
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