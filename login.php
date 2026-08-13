<?php
session_start();

// 🟢 1. NẠP CẤU HÌNH TỪ FILE .ENV
require_once 'env_loader.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'admin/connect.php'; 

$chuyen_huong = '';

if (isset($_POST['dang_nhap'])) {
    $tai_khoan = trim($_POST['tai_khoan']);
    $mat_khau = $_POST['mat_khau'];

    if (empty($tai_khoan) || empty($mat_khau)) {
        $_SESSION['toast_msg'] = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu!';
        $_SESSION['toast_type'] = 'error';
    } else {
        // Dùng Prepared Statement để chống SQL Injection
        $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE (so_dien_thoai = ? OR email = ?) AND vai_tro = 'khach_hang'");
        $stmt->bind_param('ss', $tai_khoan, $tai_khoan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Xác thực mật khẩu bằng password_verify() cho bcrypt hash
            if (password_verify($mat_khau, $row['mat_khau'])) {
                $_SESSION['user_id'] = $row['id'];

                // --- ĐOẠN LẤY LẠI GIỎ HÀNG CŨ ---
                if (!empty($row['du_lieu_gio_hang'])) {
                    $_SESSION['cart'] = json_decode($row['du_lieu_gio_hang'], true);
                } else {
                    $_SESSION['cart'] = array();
                }
                // ---------------------------------
                $_SESSION['ho_ten'] = $row['ho_ten']; 
                
                $_SESSION['toast_msg'] = 'Đăng nhập thành công! Chào mừng trở lại.';
                $_SESSION['toast_type'] = 'success';
                $chuyen_huong = 'index.php'; // Đăng nhập xong cắm cờ để JS quay hiệu ứng
            } else {
                $_SESSION['toast_msg'] = 'Sai tài khoản hoặc mật khẩu!';
                $_SESSION['toast_type'] = 'error';
            }
        } else {
            $_SESSION['toast_msg'] = 'Sai tài khoản hoặc mật khẩu!';
            $_SESSION['toast_type'] = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Timeless</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- 🟢 THƯ VIỆN GOOGLE IDENTITY SERVICES -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        /* CSS Phủ nút Google thật lên trên nút Custom chuẩn của bạn */
        .google-wrapper {
            position: relative;
            display: inline-block;
        }
        .google-wrapper .g_id_signin {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.0001; /* Làm trong suốt nhưng vẫn bấm được */
            overflow: hidden;
            cursor: pointer;
            z-index: 10;
        }
        .google-wrapper .g_id_signin iframe {
            width: 100% !important;
            height: 100% !important;
            transform: scale(1.5); /* Đảm bảo phủ kín toàn bộ diện tích nút */
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-left">
            <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Quay lại Trang chủ</a>
            <div class="brand-logo">
                <img src="image/logo.png" alt="Timeless Logo">
                <h1>TIMELESS</h1>
            </div>
            <div class="welcome-text">
                <h2>Chào mừng bạn đến với <br> TIMELESS !</h2>
                <p>Đăng nhập để không bỏ lỡ các <br> sản phẩm chất lượng</p>
            </div>
            <img src="image/logo.png" class="decor-img" alt="Decor"> 
        </div>

        <div class="login-right">
            <h2 class="form-title">Đăng nhập</h2>
            
            <form action="login.php" method="POST" class="login-form">
                <div class="input-group">
                    <label>SĐT hoặc Email:</label>
                    <input type="text" name="tai_khoan" placeholder="Nhập sdt hoặc email của bạn" required>
                </div>

                <div class="input-group" style="position: relative;">
                    <label>Nhập mật khẩu:</label>
                    <input type="password" name="mat_khau" placeholder="Nhập mật khẩu của bạn" id="passwordInput" required>
                    <i class="fa-solid fa-eye" id="togglePassword"></i>
                </div>

                <button type="submit" name="dang_nhap" class="btn-login">Đăng nhập</button>

                <a href="forgot.php" class="forgot-pass">Quên mật khẩu?</a>

                <div class="divider"><span>Hoặc đăng nhập bằng</span></div>

                <div class="social-login">
                    <!-- 🟢 NÚT GOOGLE CUSTOM CHUẨN HTML CỦA BẠN (CÓ THẺ GOOGLE THẬT PHỦ ẨN BÊN TRÊN) -->
                    <div class="google-wrapper">
                        <!-- Nút hiển thị mắt thường thấy: Chuẩn 100% nút của bạn -->
                        <button type="button" class="social-btn btn-google">
                            <img src="https://img.icons8.com/color/48/google-logo.png" alt="G" style="width: 24px;">
                        </button>

                        <!-- Lớp kích hoạt đăng nhập Google (Bị ẩn) -->
                        <div id="g_id_onload"
                             data-client_id="<?php echo htmlspecialchars($_ENV['GOOGLE_CLIENT_ID'] ?? ''); ?>"
                             data-callback="handleCredentialResponse"
                             data-auto_select="false">
                        </div>

                        <div class="g_id_signin" 
                             data-type="icon" 
                             data-shape="square" 
                             data-size="large">
                        </div>
                    </div>

                    <!-- Nút Facebook -->
                    <button type="button" class="social-btn btn-facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </button>
                </div>

               <p class="signup-link">Bạn chưa có tài khoản ? <a href="register.php" style="color: red; font-weight: bold;">Đăng ký ngay</a></p>
            </form>
        </div>
    </div>

    <!-- 🟢 SCRIPT DÙNG CHUNG (Ẩn/Hiện Pass & Google Login) -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#passwordInput');

        if(togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'google_callback.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <!-- 🟢 HIỆN THÔNG BÁO LỖI NẾU Ở LẠI TRANG LOGIN -->
    <?php include 'thongbao.php'; ?>

    <!-- 🟢 CHỈ CHẠY HIỆU ỨNG CHỜ KHI ĐĂNG NHẬP THÀNH CÔNG -->
    <?php if ($chuyen_huong != ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnLogin = document.querySelector('.btn-login');
            if (btnLogin) {
                btnLogin.style.opacity = '0.7';
                btnLogin.style.pointerEvents = 'none';
                btnLogin.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ĐANG XÁC THỰC...';
            }

            setTimeout(function() {
                window.location.href = '<?php echo $chuyen_huong; ?>';
            }, 2500); 
        });
    </script>
    <?php endif; ?>

</body>
</html>