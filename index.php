<?php 
    session_start();
// 1. Nhúng file kết nối Database vào trang danh sách (Từ thư mục gốc trỏ vào thư mục admin)
    include 'admin/connect.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Timeless - Thế giới đồng hồ</title>
    <link rel="stylesheet" href="style.css">
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
            <li><a href="index.php" style="color: #b58b5a;">TRANG CHỦ</a></li>
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
                    <li><a href="Dongho_nam.php">DÀNH CHO NAM2</a></li>
                    <li><a href="Dongho_nu.php">DÀNH CHO NỮ</a></li>
                </ul>
            </li>
            <li><a href="explore.php">KHÁM PHÁ</a></li>
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
</div> <section class="banner">
    <div class="banner-slider">
        <div class="banner-item active">
            <img src="image/dh_rolex1.jpg" alt="Rolex Premium 1">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner7.jpg" alt="Rolex Premium 2">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner2.png" alt="Rolex Premium 3">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner3.jpg" alt="Rolex Premium 4">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner4.png" alt="Rolex Premium 5">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner5.png" alt="Rolex Premium 6">
        </div>
        <div class="banner-item">
            <img src="image/anhbanner6.png" alt="Rolex Premium 6">
        </div>
    </div>
</section>

<section class="brands-section">
    <h3>XEM SẢN PHẨM THEO THƯƠNG HIỆU</h3>
    <div class="brand-list">
        <div class="brand-item">
            <li style="list-style: none;">
                <a href="all_rolex.php">
                    <img src="image/rolex.png" alt="Rolex" style="width: 65%; height: 100%;">
                </a>
            </li>
        </div>

        <div class="brand-item">
            <li style="list-style: none;">
                <a href="all_hublot.php">
                    <img src="image/Hublot.jpg" alt="Hublot" style="width: 65%; height: 100%;">
                </a>
            </li>
        </div>

        <div class="brand-item">
            <li style="list-style: none;">
                <a href="all_omega.php">
                    <img src="image/OMEGA2.png" alt="Omega" style="width: 65%; height: 100%;">
                </a>
            </li>
        </div>

        <div class="brand-item">
            <li style="list-style: none;">
                <a href="all_seiko.php">
                    <img src="image/Seiko.jpg" alt="Seiko" style="width: 70%; height: 60%;">
                </a>
            </li>
        </div>

        <div class="brand-item">
            <li style="list-style: none;">
                <a href="all_casio.php">
                    <img src="image/casio-logo.svg" alt="Casio" style="width: 80%; height: 80%;">
                </a>
            </li>
        </div>
    </div>
</section>

<section class="featured-section" style="padding: 40px 0; overflow: hidden;">
    <h2 class="section-title" style="text-align: center; margin-bottom: 30px; letter-spacing: 2px;">SẢN PHẨM NỔI BẬT</h2>

    <div class="slider-wrapper" style="display: flex; align-items: center; justify-content: center; max-width: 1300px; margin: 0 auto; position: relative;">
        
        <button class="nav-btn" onclick="moveSlider(-1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-right: 10px;">❮</button>
        
        <div class="slider-container" style="width: 1100px; overflow: hidden !important; position: relative;">
    
    <div class="product-grid" id="sliderTrack" style="display: flex !important; transition: transform 0.5s ease-in-out; width: calc((1100px / 4) * 7); margin: 0; padding: 0;">
        
        <?php
        // 1. Danh sách các ID sản phẩm bạn muốn chọn làm "Nổi bật"
        // Bạn có thể thêm bao nhiêu ID tùy thích, cách nhau bằng dấu phẩy
        $list_id_noi_bat = "4, 12, 20, 25, 30, 35, 50"; 

        // 2. Truy vấn lấy đúng những sản phẩm nằm trong danh sách ID trên
        // ORDER BY FIELD giúp giữ đúng thứ tự ID bạn đã nhập ở trên
        $sql_featured = "SELECT id, ten_san_pham, gia_ban, anh_san_pham, id_thuong_hieu 
                        FROM san_pham 
                        WHERE id IN ($list_id_noi_bat)
                        ORDER BY FIELD(id, $list_id_noi_bat)";

        $result_featured = $conn->query($sql_featured);

        if ($result_featured && $result_featured->num_rows > 0) {
            while($row = $result_featured->fetch_assoc()) {
                // Map ID thương hiệu sang tên hãng để tạo link chi tiết
                $brand_map = [
                    1 => 'rolex', 
                    2 => 'hublot', 
                    3 => 'omega', 
                    4 => 'casio', 
                    5 => 'seiko'
                ];
                
                $brand_name = isset($brand_map[$row['id_thuong_hieu']]) ? $brand_map[$row['id_thuong_hieu']] : 'generic';
                $link = "chi_tiet_sp/chi_tiet_" . $brand_name . ".php?id=" . $row['id'];
                $gia_format = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
        ?>
                <div class="product-item" style="flex: 0 0 calc(1100px / 4); width: calc(1100px / 4); box-sizing: border-box; padding: 15px; text-align: center;">
                    <a href="<?php echo $link; ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="product-image-wrapper" style="width: 100%; height: 250px; display: flex; align-items: center; justify-content: center; background: #fff;">
                            <img src="<?php echo $row['anh_san_pham']; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <p style="font-size: 13px; margin: 10px 0; height: 40px; overflow: hidden; line-height: 1.4;">
                            <?php echo $row['ten_san_pham']; ?>
                        </p>
                        <p style="font-weight: bold; color: #d4af37;"><?php echo $gia_format; ?></p>
                    </a>
                </div>
        <?php
            }
        } else {
            echo "<p style='text-align:center; width:100%;'>Vui lòng kiểm tra lại danh sách ID sản phẩm.</p>";
        }
        ?>
        </div>
</div>

        <button class="nav-btn" onclick="moveSlider(1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-left: 10px;">❯</button>
    </div>
</section>



<section class="rolex-section" style="padding: 40px 0; overflow: hidden;">
    <h2 class="rolex-title" style="text-align: center; margin-bottom: 30px; letter-spacing: 2px;">ROLEX</h2>

    <div class="rolex-slider-wrapper" style="display: flex; align-items: center; justify-content: center; max-width: 1300px; margin: 0 auto; position: relative;">
        
        <button class="nav-btn" onclick="moveRolex(-1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-right: 10px;">❮</button>

        <div class="rolex-slider-window" style="width: 1100px; overflow: hidden !important; position: relative;">
            
            <div class="rolex-track" id="rolexTrack" style="display: flex !important; transition: transform 0.5s ease-in-out; width: calc((1100px / 4) * 6); margin: 0; padding: 0;">
                
                <?php
                // 1. Danh sách ID Rolex bạn muốn hiển thị
                $list_rolex_id = "1, 3, 7, 10, 12, 15"; 

                // 2. Truy vấn lấy sản phẩm
                $sql_rolex = "SELECT id, ten_san_pham, gia_ban, anh_san_pham 
                              FROM san_pham 
                              WHERE id IN ($list_rolex_id) 
                              ORDER BY FIELD(id, $list_rolex_id) 
                              LIMIT 6";

                $result_rolex = $conn->query($sql_rolex);

                if ($result_rolex && $result_rolex->num_rows > 0) {
                    while($row = $result_rolex->fetch_assoc()) {
                        $gia_hien_thi = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
                        $link_detail = "chi_tiet_sp/chi_tiet_rolex.php?id=" . $row['id'];
                ?>
                        <div class="product-item" style="flex: 0 0 calc(1100px / 4); width: calc(1100px / 4); box-sizing: border-box; padding: 15px; text-align: center;">
                            <a href="<?php echo $link_detail; ?>" style="display:block; text-decoration:none; color:inherit;">
                                
                                <div class="product-image-wrapper" style="width: 100%; height: 250px; display: flex; align-items: center; justify-content: center; background: transparent;">
                                    <img src="<?php echo $row['anh_san_pham']; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply; filter: brightness(1.05) contrast(1.05);">
                                </div>
                                
                                <p style="font-size: 13px; margin: 10px 0; height: 40px; overflow: hidden; line-height: 1.4;">
                                    <?php echo $row['ten_san_pham']; ?>
                                </p>
                                <p style="font-weight: bold; color: #d4af37;"><?php echo $gia_hien_thi; ?></p>
                            </a>
                        </div>
                <?php
                    }
                } else {
                    echo "<p style='text-align:center; width:100%;'>Vui lòng kiểm tra lại các ID Rolex.</p>";
                }
                ?>
            </div> 
        </div> 
        <button class="nav-btn" onclick="moveRolex(1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-left: 10px;">❯</button>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="all_rolex.php" class="btn-view-all" style="text-decoration: none; padding: 10px 20px; border: 1px solid #000; color: #000;">Xem tất cả</a>
    </div>
</section>


<section class="hublot-section" style="padding: 40px 0; overflow: hidden;">
    <h2 class="hublot-title" style="text-align: center; margin-bottom: 30px; letter-spacing: 2px;">HUBLOT</h2>

    <div class="hublot-slider-wrapper" style="display: flex; align-items: center; justify-content: center; max-width: 1300px; margin: 0 auto; position: relative;">
        
        <button class="nav-btn" onclick="moveHublot(-1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-right: 10px;">❮</button>

        <div class="hublot-slider-window" style="width: 1100px; overflow: hidden !important; position: relative;">
            <div class="hublot-track" id="hublotTrack" style="display: flex !important; transition: transform 0.5s ease-in-out; width: calc((1100px / 4) * 6); margin: 0; padding: 0;">
                
                <?php
                // 1. Điền các mã ID sản phẩm HUBLOT bạn muốn hiển thị vào đây
                $list_hublot_id = "21, 23, 24, 25, 26, 27"; 

                $sql_hublot = "SELECT id, ten_san_pham, gia_ban, anh_san_pham 
                               FROM san_pham 
                               WHERE id IN ($list_hublot_id) 
                               ORDER BY FIELD(id, $list_hublot_id) 
                               LIMIT 6";

                $result_hublot = $conn->query($sql_hublot);

                if ($result_hublot && $result_hublot->num_rows > 0) {
                    while($row = $result_hublot->fetch_assoc()) {
                        $gia_hublot = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
                        $link_hublot = "chi_tiet_sp/chi_tiet_hublot.php?id=" . $row['id'];
                ?>
                        <div class="product-item" style="flex: 0 0 calc(1100px / 4); width: calc(1100px / 4); box-sizing: border-box; padding: 15px; text-align: center;">
                            <a href="<?php echo $link_hublot; ?>" style="display:block; text-decoration:none; color:inherit;">
                                <div class="product-image-wrapper" style="width: 100%; height: 250px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                    <img src="<?php echo $row['anh_san_pham']; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                                <p style="font-size: 13px; margin: 10px 0; height: 40px; overflow: hidden; line-height: 1.4;">
                                    <?php echo $row['ten_san_pham']; ?>
                                </p>
                                <p style="font-weight: bold; color: #d4af37;"><?php echo $gia_hublot; ?></p>
                            </a>
                        </div>
                <?php
                    }
                } else {
                    echo "<p style='text-align:center; width:100%;'>Vui lòng kiểm tra lại ID sản phẩm Hublot.</p>";
                }
                ?>

            </div> </div> <button class="nav-btn" onclick="moveHublot(1)" style="background: white; border: 1px solid #ddd; font-size: 25px; cursor: pointer; padding: 10px 15px; z-index: 10; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-left: 10px;">❯</button>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="all_hublot.php" class="btn-view-all" style="text-decoration: none; padding: 10px 25px; border: 1px solid #d4af37; color: #000; border-radius: 5px;">Xem tất cả</a>
    </div>
</section>

<section class="testimonial-section">
    <h2 class="testimonial-title">NHẬN XÉT KHÁCH HÀNG</h2>
    <div class="testimonial-list">
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Tôi rất hài lòng với chiếc Rolex vừa mua."</p>
            </div>
            <div class="testimonial-user">
                 <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Dịch vụ tuyệt vời! Đồng hồ Hublot đẹp hơn cả trong hình. Giao hàng nhanh và đóng gói rất cẩn thận."</p>
            </div>
            <div class="testimonial-user">
                 <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Cửa hàng tư vấn rất kỹ về cách bảo quản và chế độ bảo hành. Chắc chắn sẽ quay lại!</p>
            </div>
            <div class="testimonial-user">
                 <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
    </div>
    <div class="testimonial-info">
        <div>📞<span>0825549816</span></div>
        <div>📍<span>03-05 Pasteur, P. Nguyễn Thái Bình, Q.1, TP. Hồ Chí Minh</span></div>
        <div>✉️<span>htha4067@gmail.com</span></div>
    </div>
</section>

<footer class="footer">
    <div class="footer-left">
        <div class="footer-logo">
            <img src="image/logo.png" alt="Timeless">
        </div>
        <h3 class="footer-title">TIMELESS</h3>
        <div class="footer-line"></div>
        <p>03-05 Pasteur, P. Nguyễn Thái Bình, Quận 1, TPHCM</p>
        <p><i class="fa fa-phone"></i> 0825549816</p>
        <p class="footer-desc">TIMELESS CHUYÊN CUNG CẤP – PHÂN PHỐI ĐỒNG HỒ CHÍNH HÃNG NHẬP KHẨU TỪ CHÂU ÂU</p>
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
document.addEventListener("DOMContentLoaded", function() {
    const slides = document.querySelectorAll(".banner-item");
    let currentSlide = 0;
    const slideInterval = 3000; 

    function nextSlide() {
        slides[currentSlide].classList.remove("active");
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add("active");
    }
    setInterval(nextSlide, slideInterval);
});

const track = document.getElementById("sliderTrack");
const itemsToShow = 4;
const originalItems = track.querySelectorAll(".product-item");
const totalOriginal = originalItems.length;
const itemWidth = 1100 / itemsToShow; 
let index = 0;
for (let i = 0; i < itemsToShow; i++) {
    track.appendChild(originalItems[i].cloneNode(true));
}
track.style.width = (totalOriginal + itemsToShow) * itemWidth + "px";

function moveSlider(step) {
    index += step;
    track.style.transition = "transform 0.5s ease";
    track.style.transform = `translateX(-${index * itemWidth}px)`;
    if (index >= totalOriginal) {
        setTimeout(() => {
            track.style.transition = "none"; 
            index = 0;
            track.style.transform = "translateX(0px)";
        }, 500); 
    }
    if (index < 0) {
        track.style.transition = "none";
        index = totalOriginal; 
        track.style.transform = `translateX(-${index * itemWidth}px)`;
        setTimeout(() => {
            track.style.transition = "transform 0.5s ease";
            index = totalOriginal - 1;
            track.style.transform = `translateX(-${index * itemWidth}px)`;
        }, 20); 
    }
}

const rolexTrack = document.getElementById("rolexTrack");
const rolexItemsToShow = 4;
const rolexOriginalItems = rolexTrack.querySelectorAll(".product-item");
const rolexTotalOriginal = rolexOriginalItems.length;
const rolexItemWidth = 1100 / rolexItemsToShow; 
let rolexIndex = 0;
for (let i = 0; i < rolexItemsToShow; i++) {
    rolexTrack.appendChild(rolexOriginalItems[i].cloneNode(true));
}
rolexTrack.style.width = (rolexTotalOriginal + rolexItemsToShow) * rolexItemWidth + "px";

function moveRolex(step) {
    rolexIndex += step;
    rolexTrack.style.transition = "transform 0.5s ease";
    rolexTrack.style.transform = `translateX(-${rolexIndex * rolexItemWidth}px)`;
    if (rolexIndex >= rolexTotalOriginal) {
        setTimeout(() => {
            rolexTrack.style.transition = "none"; 
            rolexIndex = 0; 
            rolexTrack.style.transform = "translateX(0px)";
        }, 500); 
    }
    if (rolexIndex < 0) {
        rolexTrack.style.transition = "none";
        rolexIndex = rolexTotalOriginal; 
        rolexTrack.style.transform = `translateX(-${rolexIndex * rolexItemWidth}px)`;
        setTimeout(() => {
            rolexTrack.style.transition = "transform 0.5s ease";
            rolexIndex = rolexTotalOriginal - 1;
            rolexTrack.style.transform = `translateX(-${rolexIndex * rolexItemWidth}px)`;
        }, 20); 
    }
}

const hublotTrack = document.getElementById("hublotTrack");
const hublotItemsToShow = 4;
const hublotOriginalItems = hublotTrack.querySelectorAll(".product-item");
const hublotTotalOriginal = hublotOriginalItems.length;
const hublotItemWidth = 1100 / hublotItemsToShow; 
let hublotIndex = 0;
for (let i = 0; i < hublotItemsToShow; i++) {
    hublotTrack.appendChild(hublotOriginalItems[i].cloneNode(true));
}
hublotTrack.style.width = (hublotTotalOriginal + hublotItemsToShow) * hublotItemWidth + "px";

function moveHublot(step) {
    hublotIndex += step;
    hublotTrack.style.transition = "transform 0.5s ease";
    hublotTrack.style.transform = `translateX(-${hublotIndex * hublotItemWidth}px)`;
    if (hublotIndex >= hublotTotalOriginal) {
        setTimeout(() => {
            hublotTrack.style.transition = "none";
            hublotIndex = 0;
            hublotTrack.style.transform = "translateX(0px)";
        }, 500);
    }
    if (hublotIndex < 0) {
        hublotTrack.style.transition = "none";
        hublotIndex = hublotTotalOriginal; 
        hublotTrack.style.transform = `translateX(-${hublotIndex * hublotItemWidth}px)`;
        setTimeout(() => {
            hublotTrack.style.transition = "transform 0.5s ease";
            hublotIndex = hublotTotalOriginal - 1;
            hublotTrack.style.transform = `translateX(-${hublotIndex * hublotItemWidth}px)`;
        }, 20); 
    }
}

const omegaTrack = document.getElementById("omegaTrack");
const omegaItemsToShow = 4;
const omegaOriginalItems = omegaTrack.querySelectorAll(".product-item");
const omegaTotalOriginal = omegaOriginalItems.length;
const omegaItemWidth = 1100 / omegaItemsToShow; 
let omegaIndex = 0;
for (let i = 0; i < omegaItemsToShow; i++) {
    omegaTrack.appendChild(omegaOriginalItems[i].cloneNode(true));
}
omegaTrack.style.width = (omegaTotalOriginal + omegaItemsToShow) * omegaItemWidth + "px";

window.moveOmega = function(step) {
    omegaIndex += step;
    omegaTrack.style.transition = "transform 0.5s ease";
    omegaTrack.style.transform = `translateX(-${omegaIndex * omegaItemWidth}px)`;
    if (omegaIndex >= omegaTotalOriginal) {
        setTimeout(() => {
            omegaTrack.style.transition = "none"; 
            omegaIndex = 0; 
            omegaTrack.style.transform = "translateX(0px)";
        }, 500);
    }
    if (omegaIndex < 0) {
        omegaTrack.style.transition = "none";
        omegaIndex = omegaTotalOriginal; 
        omegaTrack.style.transform = `translateX(-${omegaIndex * omegaItemWidth}px)`;
        setTimeout(() => {
            omegaTrack.style.transition = "transform 0.5s ease";
            omegaIndex = omegaTotalOriginal - 1;
            omegaTrack.style.transform = `translateX(-${omegaIndex * omegaItemWidth}px)`;
        }, 20); 
    }
};
</script>

<script>
    const smartHeader = document.getElementById('smart-header');
    if (smartHeader) {
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
    }
</script>

</body>
</html>