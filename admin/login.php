<?php
session_start();
include 'connect.php'; 

$chuyen_huong = ''; 

if (isset($_POST['login_admin'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password_input = $_POST['password'];

    // Lấy record admin theo username, sau đó verify mật khẩu
    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Xác thực mật khẩu bằng password_verify() cho bcrypt hash
        if (password_verify($password_input, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            
            // Bắt lỗi nếu database đặt tên cột bị sai chính tả (ho_ten hay hot_ten)
            $_SESSION['admin_name'] = isset($row['ho_ten']) ? $row['ho_ten'] : (isset($row['hot_ten']) ? $row['hot_ten'] : 'Admin');
            
            $_SESSION['toast_msg'] = 'Đăng nhập thành công! Đang vào hệ thống...';
            $_SESSION['toast_type'] = 'success';
            
            $chuyen_huong = 'index.php';
        } else {
            $_SESSION['toast_msg'] = 'Sai tài khoản hoặc mật khẩu!';
            $_SESSION['toast_type'] = 'error';
            
            header("Location: login.php");
            exit(); 
        }
    } else {
        $_SESSION['toast_msg'] = 'Sai tài khoản hoặc mật khẩu!';
        $_SESSION['toast_type'] = 'error';
        
        header("Location: login.php");
        exit(); 
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - Timeless</title>
    <link rel="stylesheet" href="../login.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #eef2f3 0%, #8e9eab 100%) !important;
            background-attachment: fixed;
        }
        
        .login-container {
            background: transparent !important;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25) !important; 
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .login-right {
            background: #ffffff !important; 
        }

        .login-left {
            background: rgba(255, 255, 255, 0.25) !important; 
            backdrop-filter: blur(20px) saturate(200%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(200%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.7) !important;
            box-shadow: inset 10px 0 30px rgba(255, 255, 255, 0.8) !important; 
        }

        .brand-logo h1, .welcome-text h2 {
            background: linear-gradient(to right, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            text-shadow: 2px 4px 6px rgba(0,0,0,0.1); 
            font-weight: 900 !important;
            letter-spacing: 1px;
        }
        
        .welcome-text p {
            color: #444 !important; 
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-login {
            background: linear-gradient(135deg, #d4af37, #b38728) !important; 
            color: #ffffff !important; 
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: none !important; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3); 
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #d4af37, #aa771c) !important; 
            color: #111 !important; 
            border-color: #111 !important;
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.6);
            transform: translateY(-3px); 
        }

        .input-group input:focus {
            border-color: #d4af37 !important;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.3) !important;
            outline: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <div class="login-left">
            <a href="../index.php" class="back-home" style="color: #ccc;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại Cửa hàng
            </a>

            <div class="brand-logo">
                <img src="../image/logo.png" alt="Timeless Logo">
                <h1>TIMELESS</h1>
            </div>
            
            <div class="welcome-text">
                <h2>HỆ THỐNG <br> QUẢN TRỊ</h2>
                <p style="color: #bbb;">Khu vực dành riêng cho <br> Ban quản trị website Timeless</p>
            </div>

            <img src="../image/logo.png" class="decor-img" alt="Decor" style="opacity: 0.15;"> 
        </div>

        <div class="login-right">
            <h2 class="form-title" style="margin-bottom: 40px;">Đăng nhập Admin</h2>
             
            <form action="login.php" method="POST" class="login-form">
                <div class="input-group">
                    <label>Tài khoản (Username):</label>
                    <input type="text" name="username" placeholder="Nhập tài khoản quản trị" required>
                </div>

                <div class="input-group" style="position: relative;">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" placeholder="Nhập mật khẩu quản trị" id="passwordInput" required>
                    <i class="fa-solid fa-eye" id="togglePassword"></i>
                </div>

                <button type="submit" name="login_admin" class="btn-login" style="margin-top: 20px;">ĐĂNG NHẬP HỆ THỐNG</button>

                <div style="margin-top: 40px; text-align: center; color: #888; font-size: 14px;">
                    <i class="fa-solid fa-shield-halved" style="color: #d4af37;"></i> Khu vực bảo mật. Cấm truy cập trái phép.
                </div>
            </form>
        </div>
    </div>

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
    </script>

    <?php include '../thongbao.php'; ?>

    <?php if ($chuyen_huong != ''): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnLogin = document.querySelector('.btn-login');
            btnLogin.style.opacity = '0.7';
            btnLogin.style.pointerEvents = 'none';
            btnLogin.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ĐANG XÁC THỰC...';

            setTimeout(function() {
                window.location.href = '<?php echo $chuyen_huong; ?>';
            }, 2500); 
        });
    </script>
    <?php endif; ?>

</body>
</html>