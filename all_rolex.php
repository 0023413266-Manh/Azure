<?php
// 1. Khai báo biến đường dẫn
$path_prefix = ''; 

// 2. Nhúng Header chung
include $path_prefix . 'header.php';

// 3. BẮT LỆNH SẮP XẾP VÀ LỌC GIÁ
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$price = isset($_GET['price']) ? $_GET['price'] : 'all';

$price_sql = "";
if ($price == 'under10') { $price_sql = " AND gia_ban < 10000000"; } 
elseif ($price == '10to50') { $price_sql = " AND gia_ban BETWEEN 10000000 AND 50000000"; } 
elseif ($price == 'over50') { $price_sql = " AND gia_ban > 50000000"; }

$order_sql = "ORDER BY id DESC"; 
if ($sort == 'asc') { $order_sql = "ORDER BY gia_ban ASC"; } 
elseif ($sort == 'desc') { $order_sql = "ORDER BY gia_ban DESC"; }

// ROLEX = ID 1
$sql = "SELECT * FROM san_pham WHERE id_thuong_hieu = 1 $price_sql $order_sql";
$result = $conn->query($sql);
?>

<!-- CSS TỐI ƯU CHUẨN GRID - CHỐNG VỠ GIAO DIỆN KHI DỊCH ĐA NGÔN NGỮ -->
<style>
.rolex-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 15px;
}
.product-grid-custom {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 20px;
    justify-content: flex-start;
    margin-top: 20px;
}
.product-item-custom {
    flex: 0 0 calc(25% - 15px); /* Cố định 4 sản phẩm / 1 hàng */
    max-width: calc(25% - 15px);
    box-sizing: border-box;
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.product-item-custom:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.product-image-wrapper {
    width: 100%;
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.product-title-text {
    font-size: 13px;
    margin: 10px 0;
    height: 38px;
    overflow: hidden;
    line-height: 1.4;
    text-align: center;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.filter-btn-link { 
    display: inline-block; 
    padding: 6px 12px; 
    border: 1px solid #ccc; 
    border-radius: 4px; 
    color: #555; 
    text-decoration: none; 
    font-size: 13px; 
    transition: 0.3s; 
    background: #fff; 
    margin-left: 5px; 
    margin-bottom: 5px; 
}
.filter-btn-link:hover { background: #f9f6f0; border-color: #b58b5a; color: #b58b5a; }
.filter-btn-link.active { background: #b58b5a; color: #fff; border-color: #b58b5a; font-weight: bold; }
.filter-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px; margin-top: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee; }

/* Responsive cho màn hình nhỏ / Mobile */
@media (max-width: 992px) {
    .product-item-custom { flex: 0 0 calc(33.333% - 14px); max-width: calc(33.333% - 14px); }
}
@media (max-width: 768px) {
    .product-item-custom { flex: 0 0 calc(50% - 10px); max-width: calc(50% - 10px); }
}
</style>

<section class="banner">
    <div class="banner-slider">
        <div class="banner-item active">
            <img src="image/nenrolex.jpg" alt="Rolex Premium" style="width:100%; height:auto;">
        </div>
    </div>
</section>

<div class="rolex-container">
    <nav class="breadcrumb"><a href="index.php">Home</a> >> <span class="current-page">Đồng Hồ Rolex Chính Hãng Giá Tốt Nhất</span></nav>
    <h1 class="main-title" style="margin-top: 10px;">Đồng Hồ Rolex Chính Hãng</h1>
    <div class="brand-description">
        <p>Đồng hồ Rolex nổi tiếng với các BST Rolex Datejust, Rolex Day-Date... Mua đồng hồ Rolex chính hãng tại Timeless với mức giá tốt nhất.</p>
    </div>
    
    <!-- BỘ LỌC VÀ SẮP XẾP -->
    <div class="filter-row">
        <div class="price-filter">
            <span style="font-weight: bold; color: #555; margin-right: 5px;"><i class="fa-solid fa-filter"></i> Lọc mức giá:</span>
            <a href="?sort=<?php echo $sort; ?>&price=all" class="filter-btn-link <?php echo $price == 'all' ? 'active' : ''; ?>">Tất cả</a>
            <a href="?sort=<?php echo $sort; ?>&price=under10" class="filter-btn-link <?php echo $price == 'under10' ? 'active' : ''; ?>">Dưới 10 triệu</a>
            <a href="?sort=<?php echo $sort; ?>&price=10to50" class="filter-btn-link <?php echo $price == '10to50' ? 'active' : ''; ?>">10 - 50 triệu</a>
            <a href="?sort=<?php echo $sort; ?>&price=over50" class="filter-btn-link <?php echo $price == 'over50' ? 'active' : ''; ?>">Trên 50 triệu</a>
        </div>
        <div class="sort-filter">
            <span style="font-weight: bold; color: #555; margin-right: 5px;"><i class="fa-solid fa-sort"></i> Sắp xếp:</span>
            <a href="?sort=new&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'new' ? 'active' : ''; ?>">Mới nhất</a>
            <a href="?sort=desc&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'desc' ? 'active' : ''; ?>">Giá giảm dần</a>
            <a href="?sort=asc&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'asc' ? 'active' : ''; ?>">Giá tăng dần</a>
        </div>
    </div>

    <!-- DANH SÁCH SẢN PHẨM ROLEX -->
    <div class="product-grid-custom">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-item-custom">
                <a href="chi_tiet_sp/chi_tiet_rolex.php?id=<?php echo $row['id']; ?>" style="display:block; text-decoration:none; color:inherit;">
                    <div class="product-image-wrapper">
                        <img src="<?php echo htmlspecialchars(function_exists('show_img_url') ? show_img_url($row['anh_san_pham']) : $row['anh_san_pham']); ?>" 
     style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply;">
                    </div>
                    <p class="product-title-text"><?php echo $row['ten_san_pham']; ?></p>
                    <p style="font-weight: bold; color: #d4af37; text-align: center; margin: 0;"><?php echo number_format($row['gia_ban'], 0, ',', '.'); ?> VNĐ</p>
                </a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style='width: 100%; text-align: center; margin: 40px 0; color: #d9534f; font-weight: bold;'><i class="fa-solid fa-box-open"></i> Không tìm thấy sản phẩm nào phù hợp với bộ lọc này!</p>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- FOOTER CHUẨN CỦA WEB TIMELESS -->
<!-- ========================================== -->
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
        <p><i class="fa fa-envelope"></i> cskh@timeless.com</p>
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
            <h4>CHÍNH SÁCH KHÁCH HÀNG</h4> 
            <ul>
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

<?php
include 'ai-chatbot.php';
include $path_prefix . 'footer.php'; 
?>