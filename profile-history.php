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


// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM nguoi_dung WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user_data = $result_user->fetch_assoc();

if (!$user_data) { 
    header("Location: logout.php");
    exit();
}

// ĐẢM BẢO BẢNG danh_gia LUÔN ĐỦ CỘT CẦN THIẾT - CHẠY MỖI LẦN LOAD TRANG
$conn->query("CREATE TABLE IF NOT EXISTS danh_gia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT,
    id_san_pham INT,
    id_don_hang INT,
    so_sao INT,
    noi_dung TEXT,
    anh_danh_gia VARCHAR(255) DEFAULT '',
    ngay_danh_gia DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Tự động bổ sung cột anh_danh_gia nếu bảng cũ chưa có
$check_col = $conn->query("SHOW COLUMNS FROM danh_gia LIKE 'anh_danh_gia'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE danh_gia ADD COLUMN anh_danh_gia VARCHAR(255) DEFAULT ''");
}

// Tự động bổ sung cột id_don_hang nếu bảng cũ chưa có (fix lỗi nút Đánh giá luôn hiện dù đã đánh giá)
$check_col_don = $conn->query("SHOW COLUMNS FROM danh_gia LIKE 'id_don_hang'");
if ($check_col_don && $check_col_don->num_rows == 0) {
    $conn->query("ALTER TABLE danh_gia ADD COLUMN id_don_hang INT DEFAULT NULL");
}

// XỬ LÝ LƯU ĐÁNH GIÁ KHI NGUỜI DÙNG GỬI FORM (KÈM UPLOAD ẢNH)
if (isset($_POST['gui_danh_gia'])) {
    $id_sp = intval($_POST['id_san_pham']);
    $id_don = intval($_POST['id_don_hang']);
    $so_sao = intval($_POST['so_sao']);
    $noi_dung = $conn->real_escape_string($_POST['noi_dung']);

    // Xử lý Upload Ảnh (Nếu có)
    $path_anh_db = "";
    if (isset($_FILES['anh_danh_gia']) && $_FILES['anh_danh_gia']['error'] == 0) {
        $target_dir = "image/review_images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['anh_danh_gia']['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'webp');
        
        if (in_array($ext, $allowed)) {
            $file_name = "review_" . $user_id . "_" . $id_sp . "_" . time() . "." . $ext;
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES['anh_danh_gia']['tmp_name'], $target_file)) {
                $path_anh_db = $target_file;
            }
        }
    }

    // CHẶN TRÙNG: kiểm tra lại lần cuối trước khi insert (phòng bấm gửi 2 lần / mở nhiều tab)
    $check_dup = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $user_id AND id_san_pham = $id_sp AND id_don_hang = $id_don");
    if ($check_dup && $check_dup->num_rows > 0) {
        echo "<script>alert('Sản phẩm này đã được đánh giá rồi!'); window.location.href='profile-history.php';</script>";
        exit();
    }

    // Chèn đánh giá vào database
    $sql_insert_review = "INSERT INTO danh_gia (id_nguoi_dung, id_san_pham, id_don_hang, so_sao, noi_dung, anh_danh_gia) 
                          VALUES ($user_id, $id_sp, $id_don, $so_sao, '$noi_dung', '$path_anh_db')";
    if ($conn->query($sql_insert_review)) {
        echo "<script>alert('Cảm ơn bạn đã gửi đánh giá sản phẩm!'); window.location.href='profile-history.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử mua hàng - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* CSS Riêng cho Thẻ Đơn Hàng & Đánh Giá */
        .order-card { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 15px; align-items: center; }
        .order-id { font-weight: bold; color: #333; font-size: 15px; }
        .order-date { color: #888; font-size: 13px; margin-left: 10px; }
        .order-status { font-weight: bold; padding: 5px 12px; border-radius: 20px; font-size: 13px; }
        
        .order-item { display: flex; align-items: center; margin-bottom: 15px; gap: 15px; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed #f5f5f5; }
        .order-item:last-child { border-bottom: none; }
        .order-item img { width: 65px; height: 65px; object-fit: contain; border: 1px solid #f0f0f0; border-radius: 5px; padding: 5px; }
        .order-item-info { flex: 1; }
        .order-item-info h4 { margin: 0 0 5px 0; font-size: 14px; color: #333; font-weight: 600; }
        .order-item-price { font-weight: bold; color: #333; font-size: 14px; text-align: right; margin-left: 15px; }
        
        .order-footer { display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid #f0f0f0; padding-top: 15px; margin-top: 10px; }
        .order-total-price { font-size: 18px; font-weight: bold; color: #d9534f; margin-left: 8px; }
        .empty-order { text-align: center; padding: 50px 0; color: #888; }
        .empty-order i { font-size: 50px; color: #ddd; margin-bottom: 15px; }

        /* Nút Đánh giá sản phẩm */
        .btn-review {
            display: inline-block;
            padding: 6px 14px;
            background: #f39c12;
            color: #fff !important;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 5px;
        }
        .btn-review:hover { background: #e08e0b; }
        .btn-reviewed {
            display: inline-block;
            padding: 6px 14px;
            background: #e0e0e0;
            color: #666 !important;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: default;
            margin-top: 5px;
            pointer-events: none;
        }

        /* Modal Hộp thoại Đánh giá Popup */
        .review-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 500px;
            max-width: 92%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 12px; right: 18px;
            font-size: 24px; cursor: pointer; color: #888;
        }

        /* CSS CHO 5 NÚT MỨC ĐỘ ĐÁNH GIÁ */
        .star-rating-options {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 15px 0 20px 0;
            flex-wrap: wrap;
        }
        .star-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fafafa;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            flex: 1;
            min-width: 75px;
        }
        .star-btn .star-num {
            font-size: 13px;
            font-weight: 600;
            color: #f39c12;
        }
        .star-btn .star-text {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        .star-btn:hover {
            border-color: #f39c12;
            background-color: #fff9f0;
        }
        input[name="so_sao"]:checked + .star-btn {
            border-color: #f39c12;
            background-color: #fff8e7;
            box-shadow: 0 0 0 1px #f39c12;
        }
        input[name="so_sao"]:checked + .star-btn .star-text {
            color: #d35400;
            font-weight: 600;
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
    <!-- CONTAINER TỔNG THỐNG NHẤT BỐ CỤC -->
    <div class="container">
        
        <!-- BANNER THÔNG TIN USER -->
        <div class="user-banner">
            <div class="user-info">
                <div class="user-avatar"><i class="fa fa-user"></i></div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user_data['ho_ten']); ?></h3>
                    <p><?php echo htmlspecialchars($user_data['so_dien_thoai']); ?></p>
                </div>
            </div>
            
            <?php
            $count_order = $conn->query("SELECT COUNT(*) as total, SUM(tong_tien) as sum_money FROM don_hang WHERE id_nguoi_dung = $user_id")->fetch_assoc();
            $tong_don = $count_order['total'] ? $count_order['total'] : 0;
            $tich_luy = $count_order['sum_money'] ? number_format($count_order['sum_money'], 0, ',', '.') : '0';
            ?>
            <div class="user-stat">
                <div class="stat-icon"><i class="fa fa-check"></i></div>
                <div class="stat-info"><h2><?php echo $tong_don; ?></h2><p>Tổng số đơn hàng</p></div>
            </div>
            <div class="user-stat">
                <div class="stat-icon"><i class="fa fa-dollar-sign"></i></div>
                <div class="stat-info"><h2><?php echo $tich_luy; ?>đ</h2><p>Tiền tích lũy</p></div>
            </div>
        </div>
        
        <!-- CHIA 2 CỘT: SIDEBAR & CONTENT -->
        <div class="main-content">
            
            <!-- CỘT TRÁI: SIDEBAR MENU -->
            <div class="sidebar">
                <ul>
                    <li><a href="index.php"><i class="fa fa-arrow-left"></i> Trang chủ</a></li>
                    <li><a href="profile.php"><i class="fa fa-home"></i> Tổng quan</a></li>
                    <li><a href="profile-history.php" class="active"><i class="fa fa-history"></i> Lịch sử mua hàng</a></li>
                    <li><a href="profile-info.php"><i class="fa fa-user"></i> Thông tin tài khoản</a></li>
                    <li><a href="bao_hanh.php"><i class="fa fa-file-alt"></i> Bảo hành sản phẩm</a></li>
                    <li><a href="profile.php?tab=feedback"><i class="fa fa-envelope"></i> Phản hồi - Hỗ Trợ</a></li>
                    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>

            <!-- CỘT PHẢI: NỘI DUNG LỊCH SỬ MUA HÀNG -->
            <div class="content-area">
                <h3 style="margin: 0 0 20px 0; font-size: 20px; font-weight: bold;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử mua hàng</h3>
                
                <?php
                // Lấy tất cả đơn hàng của User, xếp mới nhất lên đầu
                $sql_orders = "SELECT * FROM don_hang WHERE id_nguoi_dung = $user_id ORDER BY ngay_dat DESC";
                $result_orders = $conn->query($sql_orders);
                
                if ($result_orders && $result_orders->num_rows > 0):
                    while ($order = $result_orders->fetch_assoc()):
                        
                        $tt = !empty($order['trang_thai']) ? $order['trang_thai'] : 'Chờ xác nhận';
                        $bg_color = '#fff3cd'; $text_color = '#856404'; $icon = 'fa-clock-rotate-left';
                        if ($tt == 'Đang giao') { $bg_color = '#cce5ff'; $text_color = '#004085'; $icon = 'fa-truck-fast'; }
                        if ($tt == 'Đã giao') { $bg_color = '#d4edda'; $text_color = '#155724'; $icon = 'fa-check-circle'; }
                        if ($tt == 'Đã hủy') { $bg_color = '#f8d7da'; $text_color = '#721c24'; $icon = 'fa-times-circle'; }
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-id"><i class="fa-solid fa-receipt"></i> Đơn hàng #<?php echo $order['id']; ?></span>
                                <span class="order-date"><i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></span>
                            </div>
                            <div class="order-status" style="background: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>;">
                                <i class="fa-solid <?php echo $icon; ?>"></i> <?php echo $tt; ?>
                            </div>
                        </div>
                        
                        <?php
                        // Lấy các sản phẩm trong đơn hàng
                        $order_id = $order['id'];
                        $sql_details = "SELECT * FROM chi_tiet_don_hang WHERE id_don_hang = $order_id";
                        $result_details = $conn->query($sql_details);
                        
                        if ($result_details && $result_details->num_rows > 0):
                            while ($item = $result_details->fetch_assoc()):
                                $sp_id = $item['id_san_pham'];
                                $don_id = $order['id'];
                                
                                $sp_info = $conn->query("SELECT ten_san_pham, anh_san_pham, id_thuong_hieu FROM san_pham WHERE id = $sp_id")->fetch_assoc();
                                $ten_sp = isset($sp_info['ten_san_pham']) ? $sp_info['ten_san_pham'] : 'Sản phẩm đã ngừng bán';
                                $anh_sp = isset($sp_info['anh_san_pham']) ? $sp_info['anh_san_pham'] : 'image/logo.png';
                                
                                // TẠO LINK CHUYỂN HƯỚNG THEO HÃNG
                                $link_sp = "#";
                                if (isset($sp_info['id_thuong_hieu'])) {
                                    $type_name = 'rolex'; 
                                    if ($sp_info['id_thuong_hieu'] == 2) { $type_name = 'hublot'; }
                                    elseif ($sp_info['id_thuong_hieu'] == 3) { $type_name = 'omega'; }
                                    elseif ($sp_info['id_thuong_hieu'] == 4) { $type_name = 'casio'; }
                                    elseif ($sp_info['id_thuong_hieu'] == 5) { $type_name = 'seiko'; }
                                    $link_sp = "chi_tiet_sp/chi_tiet_" . $type_name . ".php?id=" . $sp_id;
                                }

                                // BẮT BUỘC CHECK CHÍNH XÁC ID ĐƠN HÀNG NÀY ĐÃ ĐƯỢC ĐÁNH GIÁ CHƯA
                                $check_review = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = '$user_id' AND id_san_pham = '$sp_id' AND id_don_hang = '$don_id'");
                                $is_reviewed = ($check_review && $check_review->num_rows > 0);
                        ?>
                        <div class="order-item">
                            <a href="<?php echo $link_sp; ?>" style="display: flex; align-items: center; text-decoration: none; color: inherit; gap: 15px; flex: 1;">
                                <img src="<?php echo $anh_sp; ?>" onerror="this.src='image/logo.png'">
                                <div class="order-item-info">
                                    <h4><?php echo $ten_sp; ?></h4>
                                    <p style="color: #888; font-size: 13px;">x<?php echo $item['so_luong']; ?></p>
                                    
                                    <!-- HIỂN THỊ NÚT ĐÁNH GIÁ NẾU ĐƠN HÀNG ĐÃ GIAO THÀNH CÔNG -->
                                    <?php if ($tt == 'Đã giao'): ?>
                                        <?php if ($is_reviewed): ?>
                                            <!-- NẾU ĐÃ ĐÁNH GIÁ: Hiện nút xám KHÔNG BẤM ĐƯỢC -->
                                            <button type="button" disabled style="background:#e0e0e0; color:#666; border:none; padding:6px 12px; border-radius:4px; font-weight:600; cursor:not-allowed; margin-top:5px; font-size:12px;">
                                                <i class="fa-solid fa-circle-check" style="color:#28a745;"></i> Đã đánh giá
                                            </button>
                                        <?php else: ?>
                                            <!-- CHƯA ĐÁNH GIÁ: Nút cam cho bấm popup -->
                                            <button type="button" class="btn-review" onclick="openReviewModal(<?php echo $sp_id; ?>, <?php echo $don_id; ?>, '<?php echo addslashes($ten_sp); ?>', event)">
                                                <i class="fa-solid fa-star"></i> Đánh giá ngay
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="order-item-price"><?php echo number_format($item['don_gia'], 0, ',', '.'); ?>đ</div>
                        </div>
                        <?php 
                            endwhile; 
                        endif;
                        ?>
                        
                        <div class="order-footer">
                            <span>Thành tiền: </span>
                            <span class="order-total-price"><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>đ</span>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="empty-order">
                        <i class="fa-solid fa-box-open"></i>
                        <h3>Bạn chưa có đơn hàng nào</h3>
                        <p>Hãy khám phá các sản phẩm của Timeless và đặt hàng nhé!</p>
                        <a href="index.php" style="display: inline-block; margin-top: 15px; padding: 10px 25px; background: #b58b5a; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;">Mua sắm ngay</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- POPUP MODAL ĐÁNH GIÁ SẢN PHẨM MỚI (5 MỨC ĐỘ CHỌN + UPLOAD ẢNH) -->
    <div id="reviewModal" class="review-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeReviewModal()">&times;</span>
            <h3 style="margin-bottom: 5px; font-size: 18px; text-align: center;">Đánh giá sản phẩm</h3>
            <p id="modalProductName" style="text-align: center; color: #b58b5a; font-size: 14px; font-weight: 600; margin-bottom: 15px;"></p>
            
            <form method="POST" action="action_danh_gia.php" enctype="multipart/form-data">
    <input type="hidden" name="id_san_pham" id="modal_sp_id">
    <input type="hidden" name="id_don_hang" id="modal_don_id">

                <!-- BỘ 5 NÚT CHỌN SAO MỨC ĐỘ CẢM XÚC -->
                <div class="star-rating-options">
                    <input type="radio" name="so_sao" value="5" id="hist_star5" checked hidden>
                    <label for="hist_star5" class="star-btn">
                        <span class="star-num">5 <i class="fa-solid fa-star"></i></span>
                        <span class="star-text">Rất tốt</span>
                    </label>

                    <input type="radio" name="so_sao" value="4" id="hist_star4" hidden>
                    <label for="hist_star4" class="star-btn">
                        <span class="star-num">4 <i class="fa-solid fa-star"></i></span>
                        <span class="star-text">Tốt</span>
                    </label>

                    <input type="radio" name="so_sao" value="3" id="hist_star3" hidden>
                    <label for="hist_star3" class="star-btn">
                        <span class="star-num">3 <i class="fa-solid fa-star"></i></span>
                        <span class="star-text">Bình thường</span>
                    </label>

                    <input type="radio" name="so_sao" value="2" id="hist_star2" hidden>
                    <label for="hist_star2" class="star-btn">
                        <span class="star-num">2 <i class="fa-solid fa-star"></i></span>
                        <span class="star-text">Tệ</span>
                    </label>

                    <input type="radio" name="so_sao" value="1" id="hist_star1" hidden>
                    <label for="hist_star1" class="star-btn">
                        <span class="star-num">1 <i class="fa-solid fa-star"></i></span>
                        <span class="star-text">Rất tệ</span>
                    </label>
                </div>

                <!-- Viết Nhận Xét -->
                <textarea name="noi_dung" rows="4" placeholder="Chia sẻ cảm nhận của bạn về chất lượng sản phẩm..." required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>

                <!-- Ô Upload Ảnh Thực Tế -->
                <div style="margin-top: 12px; text-align: left;">
                    <label style="font-size: 13px; color: #555; display: block; margin-bottom: 5px; font-weight: 500;">
                        <i class="fa-solid fa-camera"></i> Đính kèm ảnh thực tế (tùy chọn):
                    </label>
                    <input type="file" name="anh_danh_gia" accept="image/*" style="font-size: 13px;">
                </div>

                <button type="submit" name="gui_danh_gia" style="width: 100%; margin-top: 20px; padding: 12px; background: #b58b5a; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.2s;">Gửi đánh giá ngay</button>
            </form>
        </div>
    </div>

    <script>
        // JS ĐIỀU KHIỂN MODAL POPUP ĐÁNH GIÁ
        function openReviewModal(spId, donId, tenSp, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            document.getElementById('modal_sp_id').value = spId;
            document.getElementById('modal_don_id').value = donId;
            document.getElementById('modalProductName').innerText = tenSp;
            document.getElementById('hist_star5').checked = true; // Mặc định chọn 5 sao
            document.getElementById('reviewModal').style.display = 'flex';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Bấm ra ngoài Popup để đóng Modal
        window.onclick = function(event) {
            let modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeReviewModal();
            }
        }
    </script>

    <?php include 'thongbao.php'; ?>

<?php
include 'ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>