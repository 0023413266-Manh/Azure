<?php
session_start();
include 'admin/connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Lấy thông tin khách hàng
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM nguoi_dung WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user_data = $result_user->fetch_assoc();

if (!$user_data) {
    header("Location: logout.php");
    exit();
}

// 3. THỐNG KÊ ĐƠN HÀNG
$count_order = $conn->query("SELECT COUNT(*) as total, SUM(tong_tien) as sum_money FROM don_hang WHERE id_nguoi_dung = $user_id")->fetch_assoc();
$tong_don = $count_order['total'] ? $count_order['total'] : 0;
$tich_luy = $count_order['sum_money'] ? number_format($count_order['sum_money'], 0, ',', '.') : '0';

// 4. LẤY ĐƠN HÀNG GẦN NHẤT
$sql_recent = "SELECT * FROM don_hang WHERE id_nguoi_dung = $user_id ORDER BY ngay_dat DESC LIMIT 1";
$result_recent = $conn->query($sql_recent);

// 5. LẤY DANH SÁCH SẢN PHẨM YÊU THÍCH 
$sql_fav = "SELECT p.*, y.id as id_yeu_thich 
            FROM yeu_thich y 
            JOIN san_pham p ON y.id_san_pham = p.id 
            WHERE y.id_nguoi_dung = $user_id 
            ORDER BY y.id DESC";
$result_fav = $conn->query($sql_fav);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ của tôi - Timeless</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="profile.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .sidebar ul li a { cursor: pointer; }
        
        /* CSS cho thanh cuộn ngang của Sản phẩm yêu thích */
        .fav-scroll-container { display: flex; gap: 15px; overflow-x: auto; padding: 15px 5px; scroll-behavior: smooth; }
        .fav-scroll-container::-webkit-scrollbar { height: 8px; }
        .fav-scroll-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
        .fav-scroll-container::-webkit-scrollbar-thumb:hover { background: #b58b5a; }
        
        .fav-item { min-width: 160px; max-width: 160px; border: 1px solid #eee; border-radius: 8px; padding: 15px; text-align: center; transition: 0.3s; background: #fff; position: relative;}
        .fav-item:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-color: #b58b5a; }
        .fav-item img { width: 100%; height: 120px; object-fit: contain; margin-bottom: 10px; }
        .fav-item h4 { font-size: 13px; color: #333; height: 36px; overflow: hidden; margin-bottom: 5px; line-height: 1.4; }
        .fav-item .price { color: #d4af37; font-weight: bold; font-size: 14px; margin-bottom: 10px;}
        .btn-remove-fav { display: inline-block; padding: 5px 10px; background: #ffeeee; color: #d9534f; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.3s; }
        .btn-remove-fav:hover { background: #d9534f; color: #fff; }
    </style>
</head>
<body>
    
    <div class="profile-header" id="smart-profile-header">
        <a href="index.php" class="header-logo" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
    <img src="image/logo.png"> TIMELESS
</a>
        <form action="search.php" method="GET" class="search-bar">
            <input type="text" name="query" placeholder="Bạn muốn tìm gì hôm nay?" required style="border: none; outline: none; background: transparent; width: 100%;">
            <button type="submit" style="border: none; background: transparent; cursor: pointer; color: #888; padding: 0;">
                <i class="fa fa-search"></i>
            </button>
        </form>
        <a href="cart.php" class="header-cart" style="text-decoration: none; color: inherit;">Giỏ hàng <i class="fa fa-shopping-cart"></i></a>
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
            
            <div class="user-stat">
                <div class="stat-icon"><i class="fa fa-check"></i></div>
                <div class="stat-info"><h2><?php echo $tong_don; ?></h2><p>Tổng số đơn hàng</p></div>
            </div>
            
            <div class="user-stat">
                <div class="stat-icon"><i class="fa fa-dollar-sign"></i></div>
                <div class="stat-info"><h2><?php echo $tich_luy; ?>đ</h2><p>Tiền tích lũy</p></div>
            </div>
        </div>

        <div class="main-content">
            <div class="sidebar">
                <ul>
                    <li><a href="index.php"><i class="fa fa-arrow-left"></i> Trang chủ</a></li>
                    <li><a href="profile.php" class="menu-link active"><i class="fa fa-home"></i> Tổng quan</a></li>
                    <li><a href="profile-history.php" class="menu-link"><i class="fa fa-history"></i> Lịch sử mua hàng</a></li>
                    <li><a href="profile-info.php" class="menu-link"><i class="fa fa-user"></i> Thông tin tài khoản</a></li>
                   <li><a href="chinh_sach.php?type=baohanh"><i class="fa fa-file-alt"></i> Chính sách bảo hành</a></li>
                    <li><a onclick="openTab(event, 'tab-feedback')" class="menu-link"><i class="fa fa-envelope"></i> Phản hồi - Hỗ Trợ</a></li>
                    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>

            <div class="content-area">
                
                <div id="tab-overview" class="tab-content active">
                    <div class="overview-grid">
                        
                        <div class="card" style="position: relative;">
                            <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">Đơn hàng gần đây</h4>
                            <?php if($result_recent && $result_recent->num_rows > 0): 
                                $recent = $result_recent->fetch_assoc(); 
                                
                                $tt = !empty($recent['trang_thai']) ? $recent['trang_thai'] : 'Chờ xác nhận';
                                $bg_color = '#fff3cd'; $text_color = '#856404'; $icon = 'fa-clock-rotate-left';
                                if ($tt == 'Đang giao') { $bg_color = '#cce5ff'; $text_color = '#004085'; $icon = 'fa-truck-fast'; }
                                if ($tt == 'Đã giao') { $bg_color = '#d4edda'; $text_color = '#155724'; $icon = 'fa-check-circle'; }
                                if ($tt == 'Đã hủy') { $bg_color = '#f8d7da'; $text_color = '#721c24'; $icon = 'fa-times-circle'; }
                            ?>
                                <p style="margin-bottom: 5px;"><strong>Mã đơn: #<?php echo $recent['id']; ?></strong> <span style="color: #888; font-size: 13px;">(<?php echo date('d/m/Y', strtotime($recent['ngay_dat'])); ?>)</span></p>
                                <p style="margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">Trạng thái: 
                                    <span style="background: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>; padding: 3px 8px; border-radius: 10px; font-size: 12px; font-weight: bold;">
                                        <i class="fa-solid <?php echo $icon; ?>"></i> <?php echo $tt; ?>
                                    </span>
                                </p>
                                <p style="font-size: 18px; color: #d9534f; font-weight: bold; margin-top: 10px;"><?php echo number_format($recent['tong_tien'], 0, ',', '.'); ?>đ</p>
                                <a href="profile-history.php" style="position: absolute; top: 20px; right: 20px; color: #2b6cb0; font-size: 13px; text-decoration: none;">Xem tất cả &rarr;</a>
                            <?php else: ?>
                                <p style="color: #888; margin-top: 20px;"><i class="fa-solid fa-box-open"></i> Bạn chưa có đơn hàng nào.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card"><h4>Ưu đãi của bạn</h4><p style="color: #888; margin-top: 15px;"><i class="fa-solid fa-ticket"></i> Bạn chưa có mã giảm giá nào.</p></div>
                        
                        <div class="card full-width">
                            <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 5px;">
                                <i class="fa-solid fa-heart" style="color: #d9534f;"></i> Sản phẩm yêu thích của bạn
                            </h4>
                            
                            <?php if($result_fav && $result_fav->num_rows > 0): ?>
                                <div class="fav-scroll-container">
                                    <?php while($fav = $result_fav->fetch_assoc()): 
                                        // Xác định đường dẫn chi tiết
                                        $type_name = 'rolex'; 
                                        if ($fav['id_thuong_hieu'] == 2) { $type_name = 'hublot'; }
                                        elseif ($fav['id_thuong_hieu'] == 3) { $type_name = 'omega'; }
                                        elseif ($fav['id_thuong_hieu'] == 4) { $type_name = 'casio'; }
                                        elseif ($fav['id_thuong_hieu'] == 5) { $type_name = 'seiko'; }
                                        $link_sp = "chi_tiet_sp/chi_tiet_" . $type_name . ".php?id=" . $fav['id'];
                                    ?>
                                    <div class="fav-item">
                                        <a href="<?php echo $link_sp; ?>" style="text-decoration: none; display: block;">
                                            <img src="<?php echo $fav['anh_san_pham']; ?>" onerror="this.src='image/logo.png'">
                                            <h4><?php echo $fav['ten_san_pham']; ?></h4>
                                            <p class="price"><?php echo number_format($fav['gia_ban'], 0, ',', '.'); ?>đ</p>
                                        </a>
                                        <a href="action_yeuthich.php?action=remove&id=<?php echo $fav['id_yeu_thich']; ?>" class="btn-remove-fav">
                                            <i class="fa-solid fa-trash-can"></i> Bỏ thích
                                        </a>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #888; margin-top: 20px; text-align: center;"><i class="fa-regular fa-heart" style="font-size: 24px; display: block; margin-bottom: 10px; color: #ccc;"></i> Bạn chưa thêm sản phẩm nào vào mục yêu thích.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <div id="tab-feedback" class="tab-content">
                    <div class="card">
                        <div class="support-section">
                            <div class="section-head" style="font-weight: bold; margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #b58b5a; padding-bottom: 10px; display: inline-block;">
                                Liên hệ Hỗ trợ & Khiếu nại
                            </div>
                            
                            <div class="support-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 15px;">
                                
                                <div class="support-card" style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid #eee; border-radius: 8px; background: #fdfbf9; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                                    <div class="support-icon" style="font-size: 35px; color: #b58b5a;"><i class="fa fa-headset"></i></div>
                                    <div class="support-info">
                                        <h4 style="margin: 0 0 5px 0; font-size: 15px; color: #555;">Tư vấn mua hàng (7h30 - 22h00)</h4>
                                        <p style="margin: 0; color: #b58b5a; font-weight: bold; font-size: 20px;">0123.456.789</p>
                                    </div>
                                </div>
                                
                                <div class="support-card" style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid #fee; border-radius: 8px; background: #fff5f5; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                                    <div class="support-icon" style="font-size: 35px; color: #d9534f;"><i class="fa-solid fa-phone-volume"></i></div>
                                    <div class="support-info">
                                        <h4 style="margin: 0 0 5px 0; font-size: 15px; color: #555;">Khiếu nại (8h00 - 21h30)</h4>
                                        <p style="margin: 0; color: #d9534f; font-weight: bold; font-size: 20px;">1800.2063</p>
                                    </div>
                                </div>

                                <div class="support-card" style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid #f4f8ff; border-radius: 8px; background: #f8fbff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                                    <div class="support-icon" style="font-size: 35px; color: #2b6cb0;"><i class="fa-regular fa-envelope"></i></div>
                                    <div class="support-info">
                                        <h4 style="margin: 0 0 5px 0; font-size: 15px; color: #555;">Gửi Email cho chúng tôi</h4>
                                        <p style="margin: 0; color: #2b6cb0; font-weight: bold; font-size: 18px;">cskh@timeless.com</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                        <div class="feedback-area" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                            <div class="section-head" style="font-weight: bold;">Góp ý - Phản hồi</div>
                            <p style="color: #666; font-size: 14px; margin: 10px 0;">
                                Mời Quý khách đánh giá mức độ hài lòng về trải nghiệm của mình.
                            </p>
                            <div style="background: #f9f9f9; padding: 30px; text-align: center; border-radius: 8px; color: #999; border: 1px dashed #ccc;">
                                BIỂU MẪU ĐÁNH GIÁ SẼ HIỂN THỊ TẠI ĐÂY
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, menulinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            menulinks = document.getElementsByClassName("menu-link");
            for (i = 0; i < menulinks.length; i++) {
                menulinks[i].className = menulinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            if (evt) {
                evt.currentTarget.className += " active";
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const myTab = urlParams.get('tab');
            if (myTab === 'feedback') {
                openTab(null, 'tab-feedback');
                let feedbackBtn = document.querySelector('a[onclick*="tab-feedback"]');
                if (feedbackBtn) feedbackBtn.className += " active";
                document.querySelector('a[href="profile.php"]').className = "menu-link";
            }
        }
    </script>
    
    <?php include 'thongbao.php'; ?>

</body>
</html>