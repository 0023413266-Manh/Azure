<?php
session_start();
include 'admin/connect.php'; 

// 1. BẮT CÁC LỆNH TỪ URL (TỪ KHÓA, SẮP XẾP, GIÁ)
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$price = isset($_GET['price']) ? $_GET['price'] : 'all';

$result_all = null;

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    
    // 2. XỬ LÝ ĐIỀU KIỆN LỌC GIÁ
    $price_sql = "";
    if ($price == 'under10') { $price_sql = " AND gia_ban < 10000000"; } 
    elseif ($price == '10to50') { $price_sql = " AND gia_ban BETWEEN 10000000 AND 50000000"; } 
    elseif ($price == 'over50') { $price_sql = " AND gia_ban > 50000000"; }

    // 3. XỬ LÝ ĐIỀU KIỆN SẮP XẾP
    $order_sql = "ORDER BY id DESC"; 
    if ($sort == 'asc') { $order_sql = "ORDER BY gia_ban ASC"; } 
    elseif ($sort == 'desc') { $order_sql = "ORDER BY gia_ban DESC"; }

    // 4. GHÉP CHUỖI TÌM KIẾM (Bọc từ khóa trong ngoặc () để không bị lỗi Logic với AND)
    $sql = "SELECT * FROM san_pham WHERE (ten_san_pham LIKE '%$search_safe%' OR so_tham_chieu LIKE '%$search_safe%') $price_sql $order_sql";
    $result_all = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm: <?php echo htmlspecialchars($search); ?> - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
    /* BÊ NGUYÊN CSS TỪ TRANG ALL_ROLEX.PHP SANG */
    .hublot-slider-window, .slider-container { width: 100% !important; max-width: 1200px !important; overflow: visible !important; margin: 0 auto !important; }
    .product-grid, .rolex-track, .hublot-track, .omega-track, .slider-track { display: flex !important; flex-wrap: wrap !important; width: 100% !important; transform: none !important; transition: none !important; margin: 0 auto !important; padding: 0 !important; }
    .product-item { flex: 0 0 25% !important; width: 25% !important; max-width: 25% !important; padding: 15px !important; box-sizing: border-box !important; margin-bottom: 30px !important; position: relative !important; transition: transform 0.3s ease; }
    .product-item:hover { transform: translateY(-5px) !important; }
    .hublot-section { overflow: visible !important; padding-bottom: 50px !important; }
    .prev, .next, .slider-btn { display: none !important; }
    .filter-btn-link { display: inline-block; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; color: #555; text-decoration: none; font-size: 13px; transition: 0.3s; background: #fff; margin-left: 5px; margin-bottom: 5px; }
    .filter-btn-link:hover { background: #f9f6f0; border-color: #b58b5a; color: #b58b5a; }
    .filter-btn-link.active { background: #b58b5a; color: #fff; border-color: #b58b5a; font-weight: bold; }
    .filter-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px; margin-top: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee; }
    
    /* CSS RIÊNG CHO SEARCH */
    .brand-badge { display: inline-block; background: #f4f4f4; padding: 4px 10px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; color: #555; border-radius: 4px; }
    .empty-search { text-align: center; width: 100%; padding: 50px 0; color: #888; }
    .empty-search i { font-size: 50px; color: #ddd; margin-bottom: 15px; }
    </style>
</head>
<body>

<div id="smart-header">
    <header class="top-header">
        <div class="logo"><a href="index.php" class="logo-link"><h1>TIMELESS</h1><img src="image/logo.png"></a></div>
        <div class="user-box">
            <?php if(isset($_SESSION['user_id'])) { 
                $get_name = $conn->query("SELECT ho_ten FROM nguoi_dung WHERE id = ".$_SESSION['user_id']);
                $ten_ngan = "User"; if($get_name && $get_name->num_rows > 0) { $mang_ten = explode(' ', trim($get_name->fetch_assoc()['ho_ten'])); $ten_ngan = end($mang_ten); }
            ?>
                <a href="profile.php" style="text-decoration: none;"><button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;"><?php echo $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i></button></a>
            <?php } else { ?><a href="login.php" style="text-decoration: none;"><button class="btn-user">User <i class="fa-solid fa-circle-user"></i></button></a><?php } ?>
        </div>
    </header>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php">TRANG CHỦ</a></li>
            <li class="dropdown"><a href="#">THƯƠNG HIỆU <i class="fa fa-caret-down"></i></a>
                <ul class="dropdown-content"><li><a href="all_rolex.php">ROLEX</a></li><li><a href="all_omega.php">OMEGA</a></li><li><a href="all_casio.php">CASIO</a></li><li><a href="all_seiko.php">SEIKO</a></li><li><a href="all_hublot.php">HUBLOT</a></li></ul>
            </li>
            <li class="dropdown"><a href="#">SẢN PHẨM <i class="fa fa-caret-down"></i></a><ul class="dropdown-content"><li><a href="Dongho_nam.php">DÀNH CHO NAM</a></li><li><a href="Dongho_nu.php">DÀNH CHO NỮ</a></li></ul></li>
            <li><a href="explore.php">KHÁM PHÁ</a></li><li><a href="contact.php">LIÊN HỆ</a></li>
            <li class="nav-icons"><div class="search-box"><form action="search.php" method="GET"><input type="text" name="query" placeholder="Bạn tìm gì..." class="search-input" value="<?php echo htmlspecialchars($search); ?>"><button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button></form></div><a href="cart.php" class="icon-cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart-text">Giỏ hàng</span></a></li>
        </ul>
    </nav>
</div>

<div class="product-page-header" style="margin-top: 40px;">
    <h1 class="main-title" style="text-transform: none;">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search); ?>"</h1>
    
    <?php if (!empty($search)): ?>
    <div class="filter-row">
        <div class="price-filter">
            <span style="font-weight: bold; color: #555; margin-right: 5px;"><i class="fa-solid fa-filter"></i> Lọc mức giá:</span>
            <a href="?query=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&price=all" class="filter-btn-link <?php echo $price == 'all' ? 'active' : ''; ?>">Tất cả</a>
            <a href="?query=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&price=under10" class="filter-btn-link <?php echo $price == 'under10' ? 'active' : ''; ?>">Dưới 10 triệu</a>
            <a href="?query=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&price=10to50" class="filter-btn-link <?php echo $price == '10to50' ? 'active' : ''; ?>">10 - 50 triệu</a>
            <a href="?query=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&price=over50" class="filter-btn-link <?php echo $price == 'over50' ? 'active' : ''; ?>">Trên 50 triệu</a>
        </div>
        <div class="sort-filter">
            <span style="font-weight: bold; color: #555; margin-right: 5px;"><i class="fa-solid fa-sort"></i> Sắp xếp:</span>
            <a href="?query=<?php echo urlencode($search); ?>&sort=new&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'new' ? 'active' : ''; ?>">Mới nhất</a>
            <a href="?query=<?php echo urlencode($search); ?>&sort=desc&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'desc' ? 'active' : ''; ?>">Giá giảm dần</a>
            <a href="?query=<?php echo urlencode($search); ?>&sort=asc&price=<?php echo $price; ?>" class="filter-btn-link <?php echo $sort == 'asc' ? 'active' : ''; ?>">Giá tăng dần</a>
        </div>
    </div>
    <?php endif; ?>
</div>
 
<section class="hublot-section" style="padding: 20px 0; overflow: visible; margin-bottom: 80px; min-height: 40vh;">
    <div class="hublot-slider-wrapper" style="display: flex; align-items: center; justify-content: center; max-width: 1300px; margin: 0 auto; position: relative;">
        <div class="hublot-slider-window" style="width: 1100px; overflow: hidden !important; position: relative;">
            <div class="hublot-track" style="display: flex !important; width: 100%; margin: 0; padding: 0;">
                
                <?php 
                if ($result_all && $result_all->num_rows > 0): 
                    while($row = $result_all->fetch_assoc()): 
                        $gia_format = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
                        
                        $type_name = 'rolex'; 
                        if ($row['id_thuong_hieu'] == 2) { $type_name = 'hublot'; }
                        elseif ($row['id_thuong_hieu'] == 3) { $type_name = 'omega'; }
                        elseif ($row['id_thuong_hieu'] == 4) { $type_name = 'casio'; }
                        elseif ($row['id_thuong_hieu'] == 5) { $type_name = 'seiko'; }

                        $folder_path = "chi_tiet_sp/chi_tiet_" . $type_name . ".php?id=" . $row['id'];
                ?>
                    <div class="product-item">
                        <a href="<?php echo $folder_path; ?>" style="display:block; text-decoration:none; color:inherit;">
                            <div style="text-align: center; margin-bottom: 10px;">
                                <span class="brand-badge"><?php echo $type_name; ?></span>
                            </div>
                            <div class="product-image-wrapper" style="width: 100%; height: 250px; display: flex; align-items: center; justify-content: center;">
                                <img src="<?php echo $row['anh_san_pham']; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply; filter: brightness(1.05) contrast(1.05);" onerror="this.src='image/logo.png'">
                            </div>
                            <p style="font-size: 13px; margin: 10px 0; height: 40px; overflow: hidden; line-height: 1.4; text-align: center;"><?php echo $row['ten_san_pham']; ?></p>
                            <p style="font-weight: bold; color: #d4af37; text-align: center;"><?php echo $gia_format; ?></p>
                        </a>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="empty-search">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                        <h3>Không tìm thấy sản phẩm nào!</h3>
                        <p>Rất tiếc, chúng tôi không tìm thấy sản phẩm nào khớp với bộ lọc và từ khóa <b>"<?php echo htmlspecialchars($search); ?>"</b>.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
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