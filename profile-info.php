<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$path_prefix = ''; 
include 'admin/connect.php';
// ================= 1. BỘ XỬ LÝ NGÔN NGỮ (KHÔNG CẦN HEADER) =================
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('site_lang', $_GET['lang'], time() + 86400 * 30, '/');
    $_COOKIE['site_lang'] = $_GET['lang'];
}
$current_lang = $_COOKIE['site_lang'] ?? ($_SESSION['lang'] ?? 'vi');

// Hàm giữ nguyên tham số URL khi đổi ngôn ngữ
function getLangUrl($lang) {
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}

// Bật bộ hứng HTML dịch tự động nếu không phải Tiếng Việt
if ($current_lang !== 'vi') {
    ob_start();
}
if (file_exists('azure_translator.php')) {
    require_once 'azure_translator.php';
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// =========================================================================
// PHẦN XỬ LÝ LƯU DỮ LIỆU LÊN DATABASE VÀ SET THÔNG BÁO (SS)
// =========================================================================

// A. XỬ LÝ LƯU THÔNG TIN TÀI KHOẢN
if (isset($_POST['btn_update_info'])) {
    // Thêm khiên bảo vệ (real_escape_string) cho toàn bộ dữ liệu đầu vào
    $ho_ten = $conn->real_escape_string(trim($_POST['ho_ten']));
    $so_dien_thoai = $conn->real_escape_string(trim($_POST['so_dien_thoai']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $ngay_sinh = $conn->real_escape_string(trim($_POST['ngay_sinh']));

    $sql_update_info = "UPDATE nguoi_dung SET ho_ten = '$ho_ten', so_dien_thoai = '$so_dien_thoai', email = '$email', ngay_sinh = '$ngay_sinh' WHERE id = $user_id";
    
    if ($conn->query($sql_update_info) === TRUE) {
        $_SESSION['ho_ten'] = $ho_ten; 
        $_SESSION['toast_msg'] = "Cập nhật thông্তিn thành công!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Lỗi: Không thể cập nhật thông tin!";
        $_SESSION['toast_type'] = "error";
    }
    header("Location: profile-info.php"); exit();
}

// B. XỬ LÝ LƯU ĐỊA CHỈ
if (isset($_POST['btn_update_address'])) {
    $dia_chi = $_POST['dia_chi'];
    $sql_update_address = "UPDATE nguoi_dung SET dia_chi = '$dia_chi' WHERE id = $user_id";
    
    if ($conn->query($sql_update_address) === TRUE) {
        $_SESSION['toast_msg'] = "Cập nhật địa chỉ nhận hàng thành công!";
        $_SESSION['toast_type'] = "success";
    }
    header("Location: profile-info.php"); exit();
}

// C. XỬ LÝ ĐỔI MẬT KHẨU
if (isset($_POST['btn_change_password'])) {
    $old_password = $_POST['old_password']; 
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $check_pass = $conn->query("SELECT mat_khau FROM nguoi_dung WHERE id = $user_id")->fetch_assoc();

    // Xác thực mật khẩu cũ bằng password_verify() cho bcrypt hash
    if (!password_verify($old_password, $check_pass['mat_khau'])) {
        $_SESSION['toast_msg'] = "Mật khẩu hiện tại không chính xác!";
        $_SESSION['toast_type'] = "error";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['toast_msg'] = "Mật khẩu mới không trùng khớp!";
        $_SESSION['toast_type'] = "error";
    } else {
        // Mã hóa mật khẩu mới bằng bcrypt (password_hash)
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE nguoi_dung SET mat_khau = '$new_hashed' WHERE id = $user_id");
        $_SESSION['toast_msg'] = "Đổi mật khẩu thành công! Đang đăng xuất...";
        $_SESSION['toast_type'] = "success";
        $_SESSION['force_logout'] = true;
    }
    header("Location: profile-info.php"); exit();
}

// BẮT LẠI THÔNG BÁO TỪ SESSION ĐỂ ĐƯA XUỐNG JS
$toast_msg = ""; $toast_type = "";
if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_type = $_SESSION['toast_type'];
    unset($_SESSION['toast_msg']); unset($_SESSION['toast_type']);
}

// 2. Lấy thông tin khách hàng từ CSDL
$sql_user = "SELECT * FROM nguoi_dung WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user_data = $result_user->fetch_assoc();

if (!$user_data) { 
    header("Location: logout.php");
    exit();
}

// 3. LẤY THÔNG TIN ĐƠN HÀNG TỪ DATABASE ĐỂ ĐỒNG BỘ HIỂN THỊ
$count_order = $conn->query("SELECT COUNT(*) as total, SUM(tong_tien) as sum_money FROM don_hang WHERE id_nguoi_dung = $user_id")->fetch_assoc();
$tong_don = $count_order['total'] ? $count_order['total'] : 0;
$tich_luy = $count_order['sum_money'] ? number_format($count_order['sum_money'], 0, ',', '.') : '0';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin tài khoản - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .edit-link {
            float: right; font-size: 14px; color: #b58b5a; cursor: pointer;
            font-weight: bold; transition: 0.3s; padding: 5px 10px;
            border-radius: 4px; border: 1px solid transparent;
        }
        .edit-link:hover { background-color: #f9f6f0; border-color: #b58b5a; }

        /* ĐÃ FIX LỖI DÍNH HEADER BẰNG Z-INDEX VÀ CĂN GIỮA HOÀN HẢO */
        .modal {
            display: none; position: fixed; 
            z-index: 999999 !important; /* Lên hạng siêu cấp để đè bẹp Header */
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px); 
            justify-content: center; align-items: center; /* Trở lại căn giữa màn hình */
        }
        .modal-content {
            background-color: #fff; padding: 30px 40px; border-radius: 12px;
            width: 450px; max-width: 90%; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 28px; color: #888; cursor: pointer; transition: 0.2s; }
        .close-btn:hover { color: #d9534f; }
        
        .modal-content h3 {
            margin-top: 0; margin-bottom: 25px; color: #333; font-size: 22px;
            border-bottom: 2px solid #b58b5a; padding-bottom: 10px; display: inline-block;
        }
        .form-group { margin-bottom: 18px; position: relative; } 
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 12px; box-sizing: border-box; border: 1px solid #ccc;
            border-radius: 6px; font-size: 15px; transition: 0.3s; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus { border-color: #b58b5a; outline: none; box-shadow: 0 0 5px rgba(181, 139, 90, 0.3); }
        .btn-submit {
            background-color: #b58b5a; color: #fff; padding: 12px 15px; border: none; cursor: pointer;
            width: 100%; border-radius: 6px; font-size: 16px; font-weight: bold; margin-top: 15px; transition: 0.3s;
        }
        .btn-submit:hover { background-color: #9c764a; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .toggle-pwd-icon { position: absolute; right: 15px; top: 38px; cursor: pointer; color: #888; font-size: 16px; }

        /* PRISM TOAST Z-INDEX PHẢI CAO HƠN CẢ MODAL NỮA ĐỂ BÁO LỖI LÊN TRÊN CÙNG */
        #prism-toast.toast-show { bottom: 40px !important; opacity: 1 !important; pointer-events: auto !important; }
        @keyframes lightSweep {
            0% { transform: translateX(-100%) skewX(-45deg); opacity: 1; }
            10% { transform: translateX(-100%) skewX(-45deg); opacity: 1; }
            90% { transform: translateX(100%) skewX(-45deg); opacity: 0.8; }
            100% { transform: translateX(100%) skewX(-45deg); opacity: 0; }
        }

        /* CSS Nút chuyển ngôn ngữ hình Capsule bo tròn */
.lang-switcher-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #f8f9fa; /* Nền xám rất nhẹ */
    border: 1px solid #e2e8f0; /* Viền xám mảnh */
    border-radius: 30px;        /* Bo tròn dạng viên thuốc (Pill) */
    padding: 4px 14px;
    font-size: 13px;
    font-weight: 500;
}

/* Icon quả địa cầu màu vàng */
.lang-switcher-pill .fa-globe {
    color: #b58b5a; 
    font-size: 14px;
}

/* Kiểu chữ của các ngôn ngữ */
.lang-switcher-pill a {
    text-decoration: none;
    color: #4A5568; /* Màu chữ chưa chọn (xám đậm) */
    transition: color 0.2s ease;
}

/* Ngôn ngữ đang được chọn (Màu vàng ánh kim) */
.lang-switcher-pill a.active {
    color: #b58b5a;
    font-weight: bold;
}

.lang-switcher-pill a:hover {
    color: #b58b5a;
}

/* Dấu gạch đứng ngăn cách */
.lang-switcher-pill .lang-divider {
    color: #cbd5e0;
    font-weight: 300;
}
    </style>
</head>
<body>

    <div class="profile-header" id="smart-profile-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 30px;">
        <!-- 1. LOGO TRÊN HEADER -->
        <a href="index.php" class="header-logo" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
            <img src="image/logo.png" alt="Logo" style="height: 30px;"> TIMELESS
        </a>

        <!-- 2. KHU VỰC BÊN PHẢI: GIỎ HÀNG & NÚT CHUYỂN NGÔN NGỮ NẰM LIỀN KỀ NHAU -->
        <div style="display: flex; align-items: center; gap: 20px;">
            
            <!-- Nút Giỏ Hàng -->
            <a href="cart.php" class="icon-cart" style="text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
            </a>

            <!-- Đường gạch đứng ngăn cách -->
            <span style="color: #ddd;">|</span>

            <!-- 🎯 NÚT NGÔN NGỮ NẰM KẾ BÊN GIỎ HÀNG -->
<!-- NÚT NGÔN NGỮ ĐÚNG CHUẨN GIAO DIỆN MẪU -->
<div class="lang-switcher-pill">
    <i class="fa-solid fa-globe"></i>
    <a href="<?= getLangUrl('vi') ?>" class="<?= $current_lang == 'vi' ? 'active' : '' ?>">VI</a>
    <span class="lang-divider">|</span>
    <a href="<?= getLangUrl('en') ?>" class="<?= $current_lang == 'en' ? 'active' : '' ?>">EN</a>
    <span class="lang-divider">|</span>
    <a href="<?= getLangUrl('ja') ?>" class="<?= $current_lang == 'ja' ? 'active' : '' ?>">JA</a>
</div>

        </div>
        
    </div>

    <div class="container">
        <div class="user-banner">
            <div class="user-info">
                <div class="user-avatar"><i class="fa fa-user"></i></div>
                <div class="user-details">
                    <h3><?php echo $user_data['ho_ten']; ?></h3>
                    <p><?php echo $user_data['so_dien_thoai']; ?></p>
                </div>
            </div>
            
            <div class="user-stat"><div class="stat-icon"><i class="fa fa-check"></i></div><div class="stat-info"><h2><?php echo $tong_don; ?></h2><p>Đơn hàng</p></div></div>
            <div class="user-stat"><div class="stat-icon"><i class="fa fa-dollar-sign"></i></div><div class="stat-info"><h2><?php echo $tich_luy; ?>đ</h2><p>Tích lũy</p></div></div>
        </div>
        
        <div class="main-content">
            <div class="sidebar">
                <ul>
                    <li><a href="index.php"><i class="fa fa-arrow-left"></i> Trang chủ</a></li>
                    <li><a href="profile.php"><i class="fa fa-home"></i> Tổng quan</a></li>
                    <li><a href="profile-history.php"><i class="fa fa-history"></i> Lịch sử mua hàng</a></li>
                    <li><a href="profile-info.php" class="active"><i class="fa fa-user"></i> Thông tin tài khoản</a></li>
                   <li><a href="bao_hanh.php"><i class="fa fa-file-alt"></i> Bảo hành sản phẩm</a></li>
                    <li><a href="profile.php?tab=feedback"><i class="fa fa-envelope"></i> Phản hồi - Hỗ Trợ</a></li>
                    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>

            <div class="content-area">
                <div class="info-section">
                    <div class="section-title">
                        Thông tin tài khoản 
                        <span class="edit-link" onclick="openModal('modalInfo')"><i class="fa fa-edit"></i> Chỉnh sửa</span>
                    </div>
                    <div class="info-row">
                        <div class="info-col"><label>Họ và tên:</label><span><?php echo $user_data['ho_ten']; ?></span></div>
                        <div class="info-col"><label>Số điện thoại:</label><span><?php echo $user_data['so_dien_thoai']; ?></span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-col"><label>Giới tính:</label><span>Nam</span></div>
                        <div class="info-col"><label>Email:</label><span><?php echo !empty($user_data['email']) ? $user_data['email'] : 'Chưa cập nhật'; ?></span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-col">
                            <label>Ngày sinh:</label>
                            <span>
                                <?php 
                                    if (!empty($user_data['ngay_sinh']) && $user_data['ngay_sinh'] != '0000-00-00') {
                                        echo date("d/m/Y", strtotime($user_data['ngay_sinh']));
                                    } else {
                                        echo "Chưa cập nhật";
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="info-col"><label>Địa chỉ mặc định:</label><span><?php echo !empty($user_data['dia_chi']) ? $user_data['dia_chi'] : 'Chưa thiết lập'; ?></span></div>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title">
                        Sổ địa chỉ 
                        <span class="edit-link" onclick="openModal('modalAddress')"><i class="fa fa-map-marker-alt"></i> Cập nhật địa chỉ</span>
                    </div>
                    <div style="padding: 20px 0;">
                        <?php if(!empty($user_data['dia_chi'])): ?>
                            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fafafa;">
                                <p style="margin: 0; font-weight: bold; color: #333;"><i class="fa fa-location-dot" style="color: #d9534f; margin-right: 5px;"></i> Địa chỉ nhận hàng mặc định:</p>
                                <p style="margin: 10px 0 0 0; color: #555; line-height: 1.5;"><?php echo $user_data['dia_chi']; ?></p>
                            </div>
                        <?php else: ?>
                            <p style="color: #888; font-style: italic;">Bạn chưa thiết lập địa chỉ giao hàng nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-section">
                    <div class="section-title">
                        Bảo mật 
                        <span class="edit-link change-pass-link" onclick="openModal('modalPassword')"><i class="fa fa-key"></i> Đổi mật khẩu</span>
                    </div>
                    <div class="info-row">
                        <div class="info-col"><span style="color: #666;">Trạng thái tài khoản:</span> <span style="color: #28a745; font-weight: bold;">Đang hoạt động</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalInfo" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('modalInfo')">&times;</span>
            <h3>Cập nhật thông tin</h3>
            <form action="" method="POST"> 
                <div class="form-group">
                    <label>Họ và tên (*)</label>
                    <input type="text" name="ho_ten" value="<?php echo $user_data['ho_ten']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại (*)</label>
                    <input type="text" name="so_dien_thoai" value="<?php echo $user_data['so_dien_thoai']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Email liên hệ</label>
                    <input type="email" name="email" value="<?php echo $user_data['email']; ?>">
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="ngay_sinh" value="<?php echo $user_data['ngay_sinh']; ?>">
                </div>
                <button type="submit" name="btn_update_info" class="btn-submit"><i class="fa fa-save"></i> Lưu Thay Đổi</button>
            </form>
        </div>
    </div>

    <div id="modalAddress" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('modalAddress')">&times;</span>
            <h3>Cập nhật địa chỉ</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Địa chỉ nhận hàng chi tiết (*)</label>
                    <textarea name="dia_chi" rows="4" placeholder="Ví dụ: 81 Đ. Nguyễn Huệ, Phường 1, Cao Lãnh, Đồng Tháp 81100, Việt Nam" required><?php echo $user_data['dia_chi']; ?></textarea>
                </div>
                <button type="submit" name="btn_update_address" class="btn-submit"><i class="fa fa-location-dot"></i> Xác nhận địa chỉ</button>
            </form>
        </div>
    </div>

    <div id="modalPassword" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('modalPassword')">&times;</span>
            <h3>Thay đổi mật khẩu</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Mật khẩu hiện tại (*)</label>
                    <input type="password" name="old_password" id="old_pwd" placeholder="Nhập mật khẩu đang sử dụng..." required style="padding-right: 40px;">
                    <i class="fa fa-eye toggle-pwd-icon" id="eye_old" onclick="togglePwd('old_pwd', 'eye_old')"></i>
                </div>
                <div class="form-group">
                    <label>Mật khẩu mới (*)</label>
                    <input type="password" name="new_password" id="new_pwd" placeholder="Nhập mật khẩu mới..." required style="padding-right: 40px;">
                    <i class="fa fa-eye toggle-pwd-icon" id="eye_new" onclick="togglePwd('new_pwd', 'eye_new')"></i>
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu mới (*)</label>
                    <input type="password" name="confirm_password" id="confirm_pwd" placeholder="Nhập lại mật khẩu mới..." required style="padding-right: 40px;">
                    <i class="fa fa-eye toggle-pwd-icon" id="eye_confirm" onclick="togglePwd('confirm_pwd', 'eye_confirm')"></i>
                </div>
                <button type="submit" name="btn_change_password" class="btn-submit" style="background-color: #d9534f;"><i class="fa fa-shield-halved"></i> Cập Nhật Mật Khẩu</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
        window.onclick = function(event) { if (event.target.classList.contains('modal')) { event.target.style.display = "none"; } }

        function togglePwd(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye"); icon.classList.add("fa-eye-slash");
                icon.style.color = "#b58b5a";
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash"); icon.classList.add("fa-eye");
                icon.style.color = "#888";
            }
        }

        // HÀM HIỂN THỊ PRISM TOAST
        function showGlassPrismToast(message, iconClass, color) {
            const toast = document.getElementById('prism-toast');
            const lightBar = document.getElementById('light-bar');
            const toastIcon = document.getElementById('toast-icon');
            const toastText = document.getElementById('toast-text');

            toastText.innerText = message;
            toastIcon.className = 'fa-solid ' + iconClass;
            toastIcon.style.color = color;
            lightBar.style.background = `linear-gradient(90deg, transparent, ${color}, ${color}, transparent)`;

            toast.classList.add('toast-show');
            lightBar.style.animation = 'none';
            void lightBar.offsetWidth; 
            lightBar.style.animation = 'lightSweep 5s ease-out forwards';
            
            setTimeout(() => { toast.classList.remove('toast-show'); }, 5500);
        }

        // KÍCH HOẠT THÔNG BÁO TỪ PHP BẰNG HÀM XỊN
        <?php if ($toast_msg != ""): ?>
            window.onload = function() {
                var msg = "<?php echo $toast_msg; ?>";
                var type = "<?php echo $toast_type; ?>";
                var icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
                var color = type === 'success' ? '#b58b5a' : '#d9534f';
                showGlassPrismToast(msg, icon, color);
            };
        <?php endif; ?>

        <?php if (isset($_SESSION['force_logout']) && $_SESSION['force_logout'] == true): ?>
            setTimeout(function() { window.location.href = 'logout.php'; }, 2000);
            <?php unset($_SESSION['force_logout']); ?>
        <?php endif; ?>
    </script>
<?php
include 'ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>