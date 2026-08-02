<?php
session_start();
include 'admin/connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khám phá - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="explore.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div id="smart-header">
        
        <header class="top-header">
            <div class="logo">
                <a href="index.php" class="logo-link">
                    <h1>TIMELESS</h1>
                    <img src="image/logo.png" alt="Timeless Icon">
                </a>
            </div>

            <div class="user-box">
                <?php 
                // Kiểm tra xem khách đã đăng nhập chưa
                if(isset($_SESSION['user_id'])) {
                    // Nếu rồi, chạy thẳng vào Database lôi cái tên ra cho chắc ăn!
                    $uid = $_SESSION['user_id'];
                    $get_name = $conn->query("SELECT ho_ten FROM nguoi_dung WHERE id = $uid");
                    $ten_ngan = "User"; // Mặc định nếu lỡ có lỗi
                    
                    if($get_name && $get_name->num_rows > 0) {
                        $row_name = $get_name->fetch_assoc();
                        // Dùng hàm explode để chẻ họ tên ra, hàm end() để chộp lấy cái tên cuối cùng
                        $mang_ten = explode(' ', trim($row_name['ho_ten']));
                        $ten_ngan = end($mang_ten); 
                    }
                ?>
                    <a href="profile.php" style="text-decoration: none;"> 
                        <button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;">
                            <?php echo $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i>
                        </button>
                    </a>
                <?php } else { ?>
                    <a href="login.php" style="text-decoration: none;"> 
                        <button class="btn-user">User <i class="fa-solid fa-circle-user"></i></button>
                    </a>
                <?php } ?>
            </div>
        </header>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">TRANG CHỦ</a></li>
                <li class="dropdown">
                    <a href="#">THƯƠNG HIỆU <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="all_rolex.php">ROLEX</a></li>
                        <li><a href="all_omega.php">OMEGA</a></li>
                        <li><a href="all_casio.php">CASIO</a></li>
                        <li><a href="all_seiko.php">SEIKO</a></li>
                        <li><a href="all_hublot.php">HUBLOT</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">SẢN PHẨM <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="Dongho_nam.php">DÀNH CHO NAM</a></li>
                        <li><a href="Dongho_nu.php">DÀNH CHO NỮ</a></li>
                    </ul>
                </li>
                <li><a href="explore.php" style="color: #b58b5a; font-weight: bold;">KHÁM PHÁ</a></li>
                <li><a href="contact.php">LIÊN HỆ</a></li>

                <li class="nav-icons">
                    <div class="search-box">
                         <form action="search.php" method="GET">
                            <input type="text" name="query" placeholder="Bạn tìm gì..." class="search-input">
                            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>
                    <a href="cart.php" class="icon-cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-text">Giỏ hàng</span>
                     </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="explore-container">
        
        <div class="explore-header">
            <h2>Tạp chí Timeless</h2>
            <p>Khám phá thế giới đồng hồ xa xỉ, kiến thức chuyên sâu và xu hướng phong cách sống thượng lưu.</p>
        </div>

        <div class="featured-post">
            <div class="featured-img">
                <img src="image/baiviet/rolex1.avif" alt="Featured Post">
            </div>
            <div class="featured-content">
                <span class="post-category">Về Rolex</span>
                <h3 class="post-title">Tính bền vững - Cam kết cho tương lai</h3>
                <p class="post-desc">Tính bền vững: Trọng tâm của mọi hoạt động. Đặt tiêu chuẩn về chất lượng, tinh thần đổi mới và các giá trị xuất sắc là trọng tâm trong các cam kết phát triển bền vững của chúng tôi....</p>
                <a href="ve-rolex.php" class="btn-readmore">Đọc tiếp bài viết <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i></a>
            </div>
        </div>

        <div class="grid-posts">
            
            <div class="grid-item">
                <a href="ve-rolex1.php" style="display: block;">
                    <div class="grid-img">
                        <img src="image/baiviet/rolex6.jpg" alt="Post 1">
                    </div>
                </a>

                <div class="grid-content">
                    <span class="post-category">Về Rolex</span>
                    
                    <a href="ve-rolex1.php" style="text-decoration: none; color: inherit;">
                        <h4 class="post-title">Phía sau vương miện</h4>
                    </a>
                    
                    <p class="post-desc">Khám phá những bí mật và lịch sử hình thành nên biểu tượng vương miện quyền lực của thương hiệu đồng hồ Rolex đình đám.</p>
                    
                    <div class="post-meta">
                        <span><i class="fa-regular fa-calendar"></i> 26/01/2026</span>
                        <span><i class="fa-regular fa-eye"></i> 2.9M lượt xem</span>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="grid-img">
                    <img src="image/anhbanner4.png" alt="Post 2">
                </div>
                <div class="grid-content">
                    <span class="post-category">Tin tức thương hiệu</span>
                    <h4 class="post-title">Omega ra mắt bộ sưu tập Seamaster phiên bản kỷ niệm</h4>
                    <p class="post-desc">Trình làng phiên bản giới hạn với mặt số xanh đại dương tuyệt đẹp, Omega một lần nữa khẳng định vị thế ông hoàng đồng hồ lặn.</p>
                    <div class="post-meta">
                        <span><i class="fa-regular fa-calendar"></i> 10/05/2026</span>
                        <span><i class="fa-regular fa-eye"></i> 856 lượt xem</span>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="grid-img">
                    <img src="image/anhbanner5.png" alt="Post 3">
                </div>
                <div class="grid-content">
                    <span class="post-category">Kiến thức chuyên môn</span>
                    <h4 class="post-title">Bao lâu thì nên đem đồng hồ cơ đi bảo dưỡng lau dầu?</h4>
                    <p class="post-desc">Để "cỗ máy thời gian" luôn hoạt động mượt mà và bền bỉ, việc bảo dưỡng định kỳ là bắt buộc. Cùng chuyên gia giải đáp thắc mắc.</p>
                    <div class="post-meta">
                        <span><i class="fa-regular fa-calendar"></i> 02/05/2026</span>
                        <span><i class="fa-regular fa-eye"></i> 2.1K lượt xem</span>
                    </div>
                </div>
            </div>

            <div class="grid-item">
                <div class="grid-img">
                    <img src="image/anhbanner6.png" alt="Post 4">
                </div>
                <div class="grid-content">
                    <span class="post-category">Kiến thức</span>
                    <a href="bai-viet-moi.php" style="text-decoration: none; color: inherit;">
                        <h4 class="post-title">Nhập tiêu đề bài viết mới của bạn vào đây</h4>
                    </a>
                    <p class="post-desc">Mô tả ngắn gọn về bài viết (khoảng 2-3 dòng) để kích thích người dùng bấm vào xem chi tiết.</p>
                    <div class="post-meta">
                        <span><i class="fa-regular fa-calendar"></i> 26/05/2026</span>
                        <span><i class="fa-regular fa-eye"></i> 500 lượt xem</span>
                    </div>
                </div>
            </div>
            </div>

    </div>


    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo">
                <img src="image/logo.png" alt="Timeless">
            </div>
            <h3 class="footer-title">TIMELESS</h3>
            <div class="footer-line"></div>
            <p>03-05 Pasteur, P. Nguyễn Thái Bình, Quận 1, TPHCM</p>
            <p><i class="fa fa-phone"></i> 0825549816</p>
            <p class="footer-desc">
                TIMELESS CHUYÊN CUNG CẤP – PHÂN PHỐI ĐỒNG HỒ CHÍNH HÃNG NHẬP KHẨU TỪ CHÂU ÂU
            </p>
            <p><i class="fa fa-envelope"></i> htha4067@gmail.com</p>
            <p class="copyright">Bản quyền 2026</p>
        </div>

        <div class="footer-right">
        
        <div class="footer-column">
            <h4>VỀ CHÚNG TÔI</h4>
            <ul>
                <li><a href="index.php">Trang chủ</a></li> 
                <li><a href="contact.php">Liên hệ</a></li>
                <li><a href="explore.php">Kênh truyền thông lớn nhất</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>CHÍNH SÁCH KHÁCH HÀNG</h4> <ul>
<li><a href="chinh_sach.php?type=trahang">Chính sách đổi trả hàng</a></li>
<li><a href="chinh_sach.php?type=baohanh">Chính sách bảo hành sản phẩm</a></li>
<li><a href="chinh_sach.php?type=vanchuyen">Chính sách vận chuyển</a></li>
<li><a href="chinh_sach.php?type=dieukhoan">Điều khoản sử dụng</a></li>
<li><a href="chinh_sach.php?type=thanhtoan">Chính sách thanh toán</a></li>
                
                </ul>
        </div>

        <div class="footer-column">
            <h4>KHÁM PHÁ THƯƠNG HIỆU</h4>
            <ul>
                <li><a href="all_rolex.php">Rolex</a></li>
                <li><a href="all_hublot.php">Hublot</a></li>
                <li><a href="all_omega.php">Omega</a></li>
                <li><a href="all_casio.php">Casio</a></li>
                <li><a href="all_seiko.php">Seiko</a></li>
            </ul>
        </div>
    </div>
</footer>

    <script>
        document.querySelectorAll('.btn-readmore').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); 
                const targetUrl = this.getAttribute('href'); 
                this.classList.add('fly-away'); 
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 400); 
            });
        });
    </script>

    <script>
        const smartHeader = document.getElementById('smart-header');
        let lastScrollTop = 0;

        window.addEventListener('scroll', function() {
            let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            if (currentScroll > lastScrollTop && currentScroll > 100) {
                smartHeader.classList.add('header-hidden');
            } else {
                smartHeader.classList.remove('header-hidden');
            }
            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; 
        });
    </script>
</body>
</html>