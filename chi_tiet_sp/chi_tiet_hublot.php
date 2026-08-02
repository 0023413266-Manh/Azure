<?php
session_start();
// 1. Nhúng file kết nối Database (từ thư mục admin)
include '../admin/connect.php';

// 2. Lấy ID sản phẩm từ URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 3. Truy vấn lấy thông tin sản phẩm từ CSDL
    $sql = "SELECT * FROM san_pham WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Định dạng tiền tệ
        $gia_ban = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
        $gia_cu = $row['gia_cu'] ? number_format($row['gia_cu'], 0, ',', '.') . ' VNĐ' : '';
    } else {
        die("<h2 style='text-align:center; margin-top:50px;'>Sản phẩm không tồn tại trong hệ thống!</h2>");
    }
} else {
    die("<h2 style='text-align:center; margin-top:50px;'>Không tìm thấy sản phẩm! Bạn hãy quay lại trang danh sách.</h2>");
}

// KIỂM TRA XEM KHÁCH HÀNG ĐÃ THÍCH SẢN PHẨM NÀY CHƯA
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $check_fav = $conn->query("SELECT id FROM yeu_thich WHERE id_nguoi_dung = $uid AND id_san_pham = $id");
    if ($check_fav && $check_fav->num_rows > 0) {
        $is_favorited = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $row['ten_san_pham']; ?> - Timeless</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="chi_tiet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        .product-story-section .story-img img {
            box-shadow: none !important;
            border-radius: 15px !important;
            border: none !important;
            outline: none !important;
        }
    </style>
</head>
<body>

    <div id="smart-header">
        <header class="top-header">
            <div class="logo">
                <a href="../index.php" class="logo-link">
                    <h1>TIMELESS</h1>
                    <img src="../image/logo.png" alt="Timeless Icon">
                </a>
            </div>
            
            <div class="user-box">
                <?php 
                if(isset($_SESSION['user_id'])) {
                    $uid = $_SESSION['user_id'];
                    $get_name = $conn->query("SELECT ho_ten FROM nguoi_dung WHERE id = $uid");
                    $ten_ngan = "User";
                    if($get_name && $get_name->num_rows > 0) {
                        $row_name = $get_name->fetch_assoc();
                        $mang_ten = explode(' ', trim($row_name['ho_ten']));
                        $ten_ngan = end($mang_ten); 
                    }
                ?>
                    <a href="../profile.php" style="text-decoration: none;"> 
                        <button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;">
                            <?php echo $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i>
                        </button>
                    </a>
                <?php } else { ?>
                    <a href="../login.php" style="text-decoration: none;"> 
                        <button class="btn-user">User <i class="fa-solid fa-circle-user"></i></button>
                    </a>
                <?php } ?>
            </div>
        </header>

        <nav class="main-nav">
            <ul>
                <li><a href="../index.php">TRANG CHỦ</a></li>
                <li class="dropdown">
                    <a href="#">THƯƠNG HIỆU <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="../all_rolex.php">ROLEX</a></li>
                        <li><a href="../all_omega.php">OMEGA</a></li>
                        <li><a href="../all_casio.php">CASIO</a></li>
                        <li><a href="../all_seiko.php">SEIKO</a></li>
                        <li><a href="../all_hublot.php">HUBLOT</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">SẢN PHẨM <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="../Dongho_nam.php">ĐỒNG HỒ NAM</a></li>
                        <li><a href="../Dongho_nu.php">ĐỒNG HỒ NỮ</a></li>
                    </ul>
                </li>
                <li><a href="../explore.php">KHÁM PHÁ</a></li>
                <li><a href="../contact.php">LIÊN HỆ</a></li>
                <li class="nav-icons">
                    <div class="search-box">
                         <form action="../search.php" method="GET">
                            <input type="text" name="query" placeholder="Bạn tìm gì..." class="search-input">
                            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>
                    <a href="../cart.php" class="icon-cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="cart-text">Giỏ hàng</span>
                     </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <div style="background-color: #f9f9f9; padding: 0;">
       <div class="product-detail-container" style="padding-top: 20px; padding-bottom: 40px;">
        
           <div class="product-gallery" style="border:none; padding: 0;">
            
            <div class="main-image-container">
                <div class="gallery-nav">
                    <i class="fa-solid fa-chevron-left" id="prev-btn"></i>
                    <i class="fa-solid fa-chevron-right" id="next-btn"></i>
                </div>
                <img id="main-product-img" src="../<?php echo $row['anh_san_pham']; ?>" alt="<?php echo $row['ten_san_pham']; ?>" style="max-width: 350px;">
            </div>

            <div class="thumbnail-slider">
                <img src="../<?php echo $row['anh_san_pham']; ?>" class="thumb active" onclick="changeImage(0)" alt="Ảnh chính">

                <?php
                // Định nghĩa danh sách ảnh phụ cho từng sản phẩm
                $gallery = [
                    21 => ['hublot1_mat_so.png', 'hublot1_than.png', 'hublot1_lung.png','hublot1_day.png', 'hublot1_anpham.png'],
                    22 => ['hublot2-1.png', 'hublot2-2.png', 'hublot2-3.png', 'hublot2-4.png', 'hublot1_anpham.png'],
                    23 => ['hublot3-1.png', 'hublot3-2.png', 'hublot3-3.png', 'hublot3-4.png', 'hublot1_anpham.png'],
                    24 => ['hublot4-1.png', 'hublot4-2.png', 'hublot4-3.png', 'hublot4-4.png', 'hublot1_anpham.png'],
                    25 => ['hublot5-1.png', 'hublot5-2.png', 'hublot5-3.png', 'hublot5-4.png', 'hublot1_anpham.png'],
                    26 => ['hublot6-1.png', 'hublot6-2.png', 'hublot6-3.png', 'hublot6-4.png', 'hublot1_anpham.png'],
                    27 => ['hublot7-1.png', 'hublot7-2.png', 'hublot7-3.png', 'hublot7-4.png', 'hublot1_anpham.png'],
                    28 => ['hublot8-1.png', 'hublot8-2.png', 'hublot8-3.png', 'hublot8-4.png', 'hublot1_anpham.png'],
                    29 => ['hublot9-1.png', 'hublot9-2.png', 'hublot9-3.png', 'hublot9-4.png', 'hublot1_anpham.png'],
                    30 => ['hublot10-1.png', 'hublot10-2.png', 'hublot10-3.png', 'hublot10-4.png', 'hublot1_anpham.png'],
                    31 => ['hublot11-1.png', 'hublot11-2.png', 'hublot11-3.png', 'hublot11-4.png', 'hublot1_anpham.png'],
                    32 => ['hublot12-1.png', 'hublot12-2.png', 'hublot12-3.png', 'hublot12-4.png', 'hublot1_anpham.png'],
                ];

                $current_id = $row['id'];
                if (isset($gallery[$current_id])) {
                    foreach ($gallery[$current_id] as $index => $file_name) {
                        echo '<img src="../image/chitiet_hublot/' . $file_name . '" class="thumb" onclick="changeImage(' . ($index + 1) . ')" alt="Ảnh chi tiết">';
                    }
                }
                ?>
            </div>

            <?php
            $content_map = [
                'Đồng Hồ Hublot Big Bang Ceramic Blue 44mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Ceramic Blue được trang bị cửa sổ hiển thị ngày tại vị trí 3 giờ, mang lại khả năng quan sát rõ ràng và trực quan. Chức năng lịch ngày được vận hành bởi bộ máy cơ tự động, đảm bảo độ chính xác và ổn định trong quá trình sử dụng hằng ngày, đồng thời giữ trọn vẻ mạnh mẽ và thể thao đặc trưng của dòng Big Bang.',
                    'color' => '#0c6a3f'
                ],
                ' Hublot Big Bang e Titanium White Diamonds 42mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Lịch ngày được tái hiện sắc nét trên màn hình AMOLED cao cấp với khả năng tự động cập nhật chính xác tuyệt đối theo múi giờ toàn cầu. Bạn có thể dễ dàng tùy biến giao diện hiển thị lịch độc quyền của Hublot, mang lại sự tiện nghi vượt trội ngay trên một kiệt tác xa xỉ.',
                    'color' => '#000'
                ],
                'Hublot Big Bang Ferrari Unico Magic Gold 45mm 402.MX.0138.WR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Ferrari Unico Magic Gold 45mm – 402.MX.0138.WR sở hữu lịch ngày tại vị trí 3 giờ, vận hành bởi bộ máy HUB1241 UNICO tự động, đảm bảo độ chính xác và phong cách thể thao Ferrari mạnh mẽ',
                    'color' => '#a88d34'
                ],
                'Hublot Big Bang Gold White Diamonds 41mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Tiện ích tinh tế ngay trên mặt số Ô lịch ngày được đặt khéo léo tại vị trí 4h30, giúp người dùng dễ dàng theo dõi thời gian trong nháy mắt mà không làm phá vỡ cấu trúc cân xứng của mặt số. Từng con số được in sắc nét, vận hành bởi bộ máy tự động chính xác, minh chứng cho sự cầu toàn trong từng chi tiết nhỏ nhất.',
                    'color' => '#6b1823'
                ],
                'Đồng Hồ Hublot Big Bang Meca-10 Black Magic 45mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Meca-10 Black Magic 45mm sở hữu mặt số skeleton cơ khí, nổi bật với thang hiển thị mức dự trữ năng lượng trực quan – đặc trưng của dòng Meca-10. Đồng hồ được vận hành bởi bộ máy in-house HUB1201 lên cót tay, mang lại độ chính xác cao cùng dự trữ năng lượng lên đến 10 ngày, thể hiện trọn vẹn tinh thần cơ khí mạnh mẽ và hiện đại của Hublot.',
                    'color' => '#602a2e'
                ],
                'Hublot Big Bang Ferrari Unico Carbon Red Ceramic 45mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Ferrari Unico Carbon Red Ceramic 45mm được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị rõ ràng trên mặt số skeleton đậm chất cơ khí. Chức năng này được vận hành bởi bộ máy in-house HUB1241 UNICO chronograph tự động, đảm bảo độ chính xác cao và tinh thần thể thao Ferrari mạnh mẽ.',
                    'color' => '#5f4a05'
                ],
                ' Hublot Big Bang Gold Blue 44mm 301.PX.7180.LR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Gold Blue 44mm – 301.PX.7180.LR được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị rõ ràng và cân đối trên mặt số xanh sang trọng. Chức năng này được vận hành bởi bộ máy cơ tự động Thụy Sĩ, đảm bảo độ chính xác ổn định và tính tiện dụng trong sử dụng hằng ngày.',
                    'color' => '#15844b'
                ],
                ' Hublot Big Bang Gold Ceramic 41mm 341.PB.131.RX' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Gold Ceramic 41mm – 341.PB.131.RX được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị rõ ràng và cân đối trên mặt số. Chức năng này được vận hành bởi bộ máy cơ tự động Thụy Sĩ, đảm bảo độ chính xác ổn định và tính tiện dụng trong sinh hoạt hằng ngày.',
                    'color' => '#245f8c'
                ],
                'Đồng Hồ Hublot Big Bang Ferrari 1000 GP Carbon Ceramic 45mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang Ceramic Blue 44mm – 301.CI.7170.RX được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, cho khả năng quan sát rõ ràng và tiện dụng. Chức năng này được vvận hành bởi bộ máy cơ tự động, đảm bảo độ chính xác và giữ trọn phong cách thể thao mạnh mẽ đặc trưng Ferrari.',
                    'color' => '#d4af37'
                ],
                ' Hublot Big Bang Gold White Diamonds 38mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Ô lịch ngày được đặt sắc nét tại vị trí 3 giờ, giúp chủ nhân quản lý lịch trình một cách dễ dàng nhất. Từng chi tiết nhỏ trên mặt số đều được gia công chuẩn xác, vận hành bởi bộ máy Quartz Thụy Sĩ cao cấp, đảm bảo sự chính xác tuyệt đối mà không cần điều chỉnh thường xuyên.',
                    'color' => '#4b3e14'
                ],
                ' Hublot Big Bang One Click King Gold White Pavé 39mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Sự tiện nghi hòa quyện cùng nghệ thuật Ô lịch ngày được đặt tinh tế tại vị trí 3 giờ với chữ số hiển thị rõ nét trên nền trắng đồng bộ. Mọi hoạt động của lịch và bộ kim đều được vận hành bởi bộ máy tự động (Automatic) cao cấp, mang lại độ chính xác tin cậy và sự đẳng cấp của kỹ thuật cơ khí Thụy Sĩ.',
                    'color' => '#70684d'
                ],
                'Đồng Hồ Hublot Big Bang One Click Sang Bleu King Gold Grey Diamonds 39mm' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hublot Big Bang One Click Sang Bleu King Gold Grey Diamonds – 465.OS.7048.VR.1204.MXM20 được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị tinh tế trên mặt số hình học đặc trưng Sang Bleu. Chức năng này được vận hành bởi bộ máy tự động HUB1710, đảm bảo độ chính xác ổn định và hài hòa giữa nghệ thuật đương đại cùng đẳng cấp chế tác Thụy Sĩ.',
                    'color' => '#c6b88b'
                ],
            ];

            $current_content = [
                'title' => 'CHI TIẾT SẢN PHẨM',
                'desc'  => 'Thông tin sản phẩm đang được cập nhật.',
                'color' => '#333'
            ];

            foreach ($content_map as $key => $value) {
                if (strpos($row['ten_san_pham'], $key) !== false) {
                    $current_content = $value;
                    break;
                }
            }
            ?>

            <div class="highlight-box" style="border-left-color: #e5e5e5;">
                <h4 style="color: <?php echo $current_content['color']; ?>; font-size: 22px;">
                    <?php echo $current_content['title']; ?>
                </h4>
                <p style="font-size: 18px; line-height: 1.5; color: #000;">
                    <?php echo $current_content['desc']; ?>
                </p>
            </div>

            <div class="ref-number">
                Số tham chiếu: <?php echo $row['so_tham_chieu']; ?>
            </div>
        </div>

    <?php
        $gallery_data = [
            21 => ['hublot1_mat_so.png', 'hublot1_than.png', 'hublot1_lung.png','hublot1_day.png', 'hublot1_anpham.png'],
            22 => ['hublot2-1.png', 'hublot2-2.png', 'hublot2-3.png', 'hublot2-4.png', 'hublot1_anpham.png'],
            23 => ['hublot3-1.png', 'hublot3-2.png', 'hublot3-3.png', 'hublot3-4.png', 'hublot1_anpham.png'],
            24 => ['hublot4-1.png', 'hublot4-2.png', 'hublot4-3.png', 'hublot4-4.png', 'hublot1_anpham.png'],
            25 => ['hublot5-1.png', 'hublot5-2.png', 'hublot5-3.png', 'hublot5-4.png', 'hublot1_anpham.png'],
            26 => ['hublot6-1.png', 'hublot6-2.png', 'hublot6-3.png', 'hublot6-4.png', 'hublot1_anpham.png'],
            27 => ['hublot7-1.png', 'hublot7-2.png', 'hublot7-3.png', 'hublot7-4.png', 'hublot1_anpham.png'],
            28 => ['hublot8-1.png', 'hublot8-2.png', 'hublot8-3.png', 'hublot8-4.png', 'hublot1_anpham.png'],
            29 => ['hublot9-1.png', 'hublot9-2.png', 'hublot9-3.png', 'hublot9-4.png', 'hublot1_anpham.png'],
            30 => ['hublot10-1.png', 'hublot10-2.png', 'hublot10-3.png', 'hublot10-4.png', 'hublot1_anpham.png'],
            31 => ['hublot11-1.png', 'hublot11-2.png', 'hublot11-3.png', 'hublot11-4.png', 'hublot1_anpham.png'],
            32 => ['hublot12-1.png', 'hublot12-2.png', 'hublot12-3.png', 'hublot12-4.png', 'hublot1_anpham.png'],
        ];

        $current_id = $row['id'];
        $folder = "chitiet_hublot";
        $js_images = ["../" . $row['anh_san_pham']];

        if (isset($gallery_data[$current_id])) {
            foreach ($gallery_data[$current_id] as $file) {
                $js_images[] = "../image/$folder/$file";
            }
        }
        ?>
        <script> 
            const images = <?php echo json_encode($js_images); ?>;
            let currentIndex = 0; 
            const mainImg = document.getElementById('main-product-img');
            const thumbs = document.querySelectorAll('.thumb');

            function changeImage(index) {
                if (index < 0 || index >= images.length) return;
                currentIndex = index;
                mainImg.style.opacity = 0.8;
                setTimeout(() => {
                    mainImg.src = images[currentIndex];
                    mainImg.style.opacity = 1;
                }, 100); 
                thumbs.forEach((thumb, i) => {
                    if(i === currentIndex) thumb.classList.add('active');
                    else thumb.classList.remove('active');
                });
            }

            document.getElementById('next-btn').addEventListener('click', function() {
                currentIndex = (currentIndex + 1) % images.length; 
                changeImage(currentIndex);
            });

            document.getElementById('prev-btn').addEventListener('click', function() {
                currentIndex = (currentIndex - 1 + images.length) % images.length; 
                changeImage(currentIndex);
            });
        </script>

        <div class="product-info-detail">
            <div class="breadcrumb-detail" style="display: flex; align-items: center; gap: 5px; font-size: 13px; color: #666; margin-bottom: 15px;">
                <a href="../index.php" style="text-decoration: none; color: #666;">Trang chủ</a> 
                <span style="color: #ccc;">/</span> 
                <a href="../all_hublot.php" style="text-decoration: none; color: #666;">Hublot</a> 
                <span style="color: #ccc;">/</span> 
                <span style="color: #333;"><?php echo $row['ten_san_pham']; ?></span>
            </div>
            
            <h1 class="product-title"><?php echo $row['ten_san_pham']; ?></h1>
            
            <div class="product-meta">
                <span id="fav-btn" style="cursor: pointer; transition: 0.3s;" onclick="toggleFav(<?php echo $row['id']; ?>)">
                    <?php if($is_favorited): ?>
                        <i class="fa-solid fa-heart" style="color: #d9534f;"></i> <span style="color: #d9534f; font-weight: bold;">Đã thích</span>
                    <?php else: ?>
                        <i class="fa-regular fa-heart"></i> Yêu thích
                    <?php endif; ?>
                </span>
                <span><i class="fa-regular fa-comments"></i> Hỏi đáp</span>
                <span><i class="fa-solid fa-check"></i> Chính hãng</span>
            </div>

            <?php if (!empty($row['gia_cu']) && $row['gia_cu'] != $row['gia_ban']): ?>
            <div style="background: #e0e0e0; padding: 15px; border-radius: 5px; margin-bottom: 10px;">
                <p style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">Giá gốc</p>
                <p style="font-size: 18px; font-weight: 700; color: #333; text-decoration: line-through;"><?php echo $gia_cu; ?></p>
            </div>
            <?php endif; ?>

            <div style="background: #eef2ff; padding: 15px; border-radius: 5px; border: 1px solid #2b6cb0; margin-bottom: 20px;">
                <p style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">Giá sản phẩm</p>
                <p style="font-size: 24px; font-weight: 700; color: #333;"><?php echo $gia_ban; ?></p>
            </div>

<div class="btn-group">
    <?php if ($row['ton_kho'] > 0): ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn-buy-now" onclick="window.location.href='../cart.php?action=buynow&id=<?php echo $row['id']; ?>'">MUA NGAY</button>
            <button type="button" onclick="addToCartSilent(<?php echo $row['id']; ?>)" class="btn-add-cart-detail">THÊM VÀO GIỎ</button>
        <?php else: ?>
            <button class="btn-buy-now" onclick="requireLogin()">MUA NGAY</button>
            <button type="button" onclick="requireLogin()" class="btn-add-cart-detail">THÊM VÀO GIỎ</button>
        <?php endif; ?>
        <p style="color: #28a745; font-weight: bold; margin-top: 10px;"><i class="fa-solid fa-check"></i> Tình trạng: Còn hàng</p>
    <?php else: ?>
        <button class="btn-buy-now" style="background: #ccc; cursor: not-allowed;" disabled>HẾT HÀNG</button>
        <button class="btn-add-cart-detail" style="background: #eee; color: #999; cursor: not-allowed;" disabled>THÊM VÀO GIỎ</button>
        <p style="color: #d9534f; font-weight: bold; margin-top: 10px;"><i class="fa-solid fa-circle-xmark"></i> Tình trạng: Tạm hết hàng</p>
    <?php endif; ?>
</div>

            <div class="product-short-info">
                <p><i class="fa-solid fa-box-open"></i> <strong>Tình trạng:</strong> Mới 100%, Fullbox, đầy đủ hộp, sổ, thẻ bảo hành.</p>
                <p><i class="fa-solid fa-shield-halved"></i> <strong>Bảo hành:</strong> 3 năm quốc tế Hublot & Bảo hành trọn đời tại Timeless.</p>
                <p><i class="fa-solid fa-location-dot"></i> <strong>Địa điểm:</strong> Bảo hiểm hàng hóa 100% & Miễn phí vận chuyển toàn quốc.</p>
            </div>

            <?php
            $all_specs = [
                21 => [
                    'Bộ máy' => 'HUB4100 Self-winding Chronograph Movement (tự động, chronograph)',
                    'Kính' => 'Sapphire nguyên khối, chống trầy xước',
                    'Đường kính' => '44 mm',
                    'Chất liệu vỏ' => 'Thép',
                    'Dây đeo' => 'Cao su cao cấp (Rubber Strap)',
                    'Độ chịu nước' => '10 ATM (100 mét)',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                22 => [
                    'Bộ máy' => 'HUB4100 / Unico Self-winding Chronograph Movement',
                    'Kính' => 'Kính Sapphire',
                    'Đường kính' => '42 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                23 => [
                    'Bộ máy' => 'HUB1241 UNICO Manufacture Self-winding Chronograph Flyback Movement',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '45 mm',
                    'Chất liệu dây' => 'Dây cao su/da cao cấp',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                24 => [
                    'Bộ máy' => 'HUB4300 / Unico Self-winding Chronograph Movement',
                    'Kính' => 'Kính Sapphire',
                    'Đường kính' => '41 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                25 => [
                    'Bộ máy' => 'HUB1201 Manual-winding Skeleton Movement (lên cót tay, skeleton)',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '45 mm',
                    'Chất liệu dây' => 'Dây Cao su',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                26 => [
                    'Bộ máy' => 'HUB1241 UNICO Manufacture Self-winding Chronograph Flyback Movement (tự động flyback)',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '45 mm',
                    'Chất liệu dây' => 'Dây Cao su',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                27 => [
                    'Bộ máy' => 'HUB4100 / Unico Self-winding Chronograph Movement (nhà sản xuất Hublot – chronograph)',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '44 mm',
                    'Chất liệu dây' => 'Dây cao su/da cao cấp',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                28 => [
                    'Bộ máy' => 'HUB4100 / Unico Self-winding Chronograph Movement',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '41 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                29 => [
                    'Bộ máy' => 'Unico Manufacture Self-winding Chronograph',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '45 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                30 => [
                    'Bộ máy' => 'HUB2900 / Unico Self-winding Chronograph Movement',
                    'Kính' => 'Kính Sapphire',
                    'Đường kính' => '38 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                31 => [
                    'Bộ máy' => 'HUB4100 / Unico Self-winding Chronograph Movement',
                    'Kính' => 'Kính Sapphire',
                    'Đường kính' => '42 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
                32 => [
                    'Bộ máy' => 'Unico Manufacture Self-winding Chronograph',
                    'Kính' => 'Sapphire nguyên khối, chống phản chiếu',
                    'Đường kính' => '45 mm',
                    'Chất liệu dây' => 'Dây Cao su/da',
                    'Độ chịu nước' => '100 m (10 ATM)',
                    'Bảo hành' => '5 năm',
                    'Xuất xứ' => 'Thụy Sỹ (Swiss Made)'
                ],
            ];

            $current_id = $row['id']; 
            $my_specs = isset($all_specs[$current_id]) ? $all_specs[$current_id] : [];
            ?>
            <h3 style="margin-top: 30px; font-size: 18px;">Thông số kỹ thuật</h3>
            <table class="specs-table">
                <?php if (!empty($my_specs)): ?>
                    <?php foreach ($my_specs as $label => $value): ?>
                        <tr>
                            <td><?php echo $label; ?></td>
                            <td><?php echo $value; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2">Thông số đang được cập nhật...</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

<?php
$type = $row['id'];
$allData = [
    // SP 1
    21 => [
        "bg" => "#a3b19b",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Biểu tượng của công nghệ chế tác đỉnh cao từ Hublot.",
                "content" => [
                    "Sản phẩm  khẳng định đẳng cấp với bộ vỏ ceramic xanh công nghệ cao, được sản xuất thông qua quy trình nung ở nhiệt độ cực cao, tạo nên kết cấu siêu cứng, chống trầy xước vượt trội và giữ màu bền bỉ theo thời gian.",
                    "Vật liệu ceramic độc quyền của Hublot không chỉ nhẹ hơn thép truyền thống mà còn có khả năng chống ăn mòn và chịu va đập ấn tượng, mang lại cảm giác đeo chắc chắn nhưng vẫn thoải mái. Với đường kính 44mm, thiết kế Big Bang mạnh mẽ, thể thao kết hợp cùng sắc xanh hiện đại tạo nên dấu ấn cá tính, sang trọng và đầy cuốn hút cho cổ tay phái mạnh."
                ],
                "img" => "../image/chitiet_hublot/hublot1_mat_so.png"
            ],
            [
                "h3" => "VÀNH BEZEL CERAMIC & DÂY CAO SU TỰ NHIÊN",
                "h2" => "Sự kết hợp hoàn hảo giữa công nghệ và tính ứng dụng cao.",
                "content" => [
                    "Sản phẩm sở hữu vành bezel ceramic xanh công nghệ cao, nổi bật với thiết kế Big Bang đặc trưng cùng 6 vít titan hình chữ H – dấu ấn nhận diện không thể nhầm lẫn của Hublot. Chất liệu ceramic cao cấp mang lại khả năng chống trầy xước vượt trội, bền màu và giữ vẻ đẹp hoàn hảo theo thời gian.",
                    "Kết hợp với đó là dây đeo cao su tự nhiên màu xanh, mềm mại, đàn hồi tốt và ôm sát cổ tay, mang đến cảm giác đeo thoải mái ngay cả khi sử dụng trong thời gian dài. Sự hòa quyện giữa vẻ đẹp thể thao mạnh mẽ và tính sang trọng hiện đại giúp chiếc đồng hồ phù hợp cả trong môi trường công sở lẫn các hoạt động thường ngày năng động."
                ],
                "img" => "../image/chitiet_hublot/hublot1_than.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ CERAMIC CÔNG NGHỆ CAO & TITANIUM",
                "h2" => "Cam kết về độ bền và giá trị vượt thời gian",
                "content" => [
                    "Sản phẩm là  minh chứng rõ nét cho triết lý “Art of Fusion” của Hublot – nơi các vật liệu tiên tiến được kết hợp hoàn hảo. Bộ vỏ được chế tác từ ceramic xanh công nghệ cao, nổi bật với khả năng chống trầy xước, chống ăn mòn và giữ màu bền bỉ theo thời gian.",
                    "Các chi tiết như nút bấm chronograph, khóa dây và vít bezel được làm từ titanium nhẹ và siêu bền, gia công chính xác đến từng micromet, đảm bảo khả năng bảo vệ tối ưu cho bộ máy bên trong trước các tác động từ môi trường. Toàn bộ cấu trúc thể hiện sự bền bỉ, ổn định và đẳng cấp – xứng tầm một cỗ máy thời gian xa xỉ dành cho những người yêu công nghệ chế tác Thụy Sĩ."
                ],
                "img" => "../image/chitiet_hublot/hublot1_lung.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Dây cao su tự nhiên màu xanh – dấu ấn đặc trưng của Hublot – được tinh luyện theo tiêu chuẩn Thụy Sĩ, mang lại độ mềm mại, đàn hồi cao và trọng lượng nhẹ, giúp ôm sát cổ tay mà vẫn thoải mái.
Thiết kế công thái học kết hợp bề mặt chống thấm mồ hôi, hạn chế bám bụi, không gây kích ứng da cùng khóa gập chắc chắn, đảm bảo cảm giác đeo an tâm, linh hoạt và đẳng cấp trong suốt ngày dài."
                ],
                "img" => "../image/chitiet_hublot/hublot1_day.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT HUBLOT BIG BANG CERAMIC BLUE",
                "h2" => "Sự kết hợp hoàn hảo giữa hiệu năng và nghệ thuật chế tác Thụy Sĩ",
                "content" => [
                    "Không chỉ gây ấn tượng bởi thiết kế mạnh mẽ, Hublot Big Bang Ceramic Blue 44mm – 301.CI.7170.RX còn sở hữu những tính năng vượt trội của dòng đồng hồ thể thao cao cấp:",
                    "Khả năng chống nước 100 mét (10 ATM): Đáp ứng tốt các hoạt động thường ngày như rửa tay, đi mưa hoặc bơi nhẹ.",
                    "Bộ máy chronograph tự động Thụy Sĩ: Hoạt động chính xác, ổn định, thể hiện đẳng cấp cơ khí cao cấp của Hublot.",
                    "Kính sapphire phủ chống phản xạ: Giúp quan sát mặt số rõ nét trong mọi điều kiện ánh sáng, đồng thời chống trầy xước hiệu quả.",
                    "Nút bấm chronograph & núm vặn screw-down: Tăng cường khả năng kín nước và độ an toàn khi sử dụng.",
                    "Dạ quang trên kim và cọc số: Hỗ trợ xem giờ dễ dàng trong điều kiện thiếu sáng."
                ],
                "img" => "../image/sp1-hublot.png"
            ]
        ]
    ],

    // SP 2
    22 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ TITANIUM NẬM KIM CƯƠNG TINH KHIẾT",
                "h2" => "Sự kết hợp hài hòa giữa sang trọng và thể thao",
                "content" => [
                    "Kiệt tác rạng rỡ trên cổ tay Chiếc Big Bang e sở hữu bộ vỏ 42mm được chế tác từ Titanium cấp độ 5, nổi tiếng với đặc tính siêu nhẹ nhưng cực kỳ bền bỉ. Điểm nhấn đẳng cấp nằm ở 108 viên kim cương được nạm thủ công tinh xảo trên vành bezel và vỏ, tạo nên hiệu ứng ánh sáng lấp lánh, biến một thiết bị công nghệ trở thành món trang sức xa xỉ hàng đầu."
                ],
                "img" => "../image/chitiet_hublot/hublot2-1.png"
            ],
            [
                "h3" => "CHẾ TÁC TỪ VẬT LIỆU CAO CẤP NHẤT",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "Sự bền bỉ của Titanium và vẻ đẹp vĩnh cửu Không chỉ có kim cương, các chi tiết như ốc vít hình chữ H đặc trưng và núm vặn đều được gia công tỉ mỉ từ Titanium và nhựa composite đen. Đây là minh chứng cho triết lý 'The Art of Fusion' (Nghệ thuật của sự kết hợp), nơi những vật liệu truyền thống và hiện đại hòa quyện hoàn hảo."
                ],
                "img" => "../image/chitiet_hublot/hublot2-2.png"
            ],
            [
                "h3" => "MÀN HÌNH CẢM ỨNG ĐỘ PHÂN GIẢI CAO",
                "h2" => "Trải nghiệm kỹ thuật số đỉnh cao",
                "content" => [
                    "Trải nghiệm kỹ thuật số đỉnh cao Ẩn sau mặt kính Sapphire chống trầy xước là màn hình AMOLED độ nét cao. Người dùng có thể tùy biến các mặt đồng hồ độc quyền của Hublot, từ các thiết kế skeleton biểu tượng đến các tính năng theo dõi sức khỏe hiện đại, mang lại sự linh hoạt tối đa cho mọi sự kiện."
                ],
                "img" => "../image/chitiet_hublot/hublot2-3.png"
            ],
            [
                "h3" => "CƠ CHẾ THAY DÂY NHANH 'ONE CLICK'",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Biến hóa phong cách trong tích tắc Sở hữu hệ thống thay dây One Click độc quyền của Hublot, bạn có thể dễ dàng thay đổi diện mạo đồng hồ chỉ bằng một nút bấm. Kết hợp cùng dây cao su trắng cao cấp có cấu trúc sọc dọc, đồng hồ mang lại cảm giác năng động, trẻ trung nhưng vẫn giữ trọn nét quý phái."
                ],
                "img" => "../image/chitiet_hublot/hublot2-4.png"
            ],
            [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG 41MM",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Cổng thông tin hiện đại trên cổ tay Hublot Big Bang e Titanium White Diamonds không chỉ đẹp mà còn là một trợ lý đắc lực với hệ điều hành Wear OS:",
                    "Hiển thị thông báo thông minh: Cuộc gọi, tin nhắn và ứng dụng mạng xã hội ngay trên mặt số.",
                    "Theo dõi sức khỏe & vận động: Tích hợp các cảm biến đo nhịp tim, bước chân và các chế độ tập luyện chuyên sâu.",
                    "Khả năng chống nước: Đạt mức 30m (3 ATM), an toàn trong sử dụng hàng ngày và các buổi tiệc sang trọng.",
                    "Thời lượng pin tối ưu: Hỗ trợ sạc nhanh, đảm bảo duy trì kết nối suốt ngày dài năng động."
                ],
                "img" => "../image/chitiet_hublot/hublot2-6.png"
            ]
        ]
    ],
    //SP 3
    23 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ MAGIC GOLD & TITANIUM",
                "h2" => "Biểu tượng vật liệu độc quyền của Hublot – tinh thần Ferrari thuần khiết",
                "content" => [
                    "Sản phẩm sở hữu bộ vỏ Magic Gold 18K độc quyền của Hublot, hợp kim vàng chống trầy xước đầu tiên trên thế giới, kết hợp Titanium siêu nhẹ. Cấu trúc này mang lại độ bền vượt trội, khả năng chống va đập cao và vẻ đẹp sang trọng mang đậm DNA Ferrari."
                ],
                "img" => "../image/chitiet_hublot/hublot3-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL MAGIC GOLD & DÂY CAO SU TỰ NHIÊN",
                "h2" => "Sự giao thoa giữa xa xỉ và hiệu năng đường đua",
                "content" => [
                    "Vành bezel Magic Gold với thiết kế Big Bang đặc trưng, cố định bằng vít chữ H biểu tượng. Kết hợp cùng dây cao su tự nhiên cao cấp, mang lại cảm giác đeo êm ái, linh hoạt và đậm chất thể thao Ferrari."
                ],
                "img" => "../image/chitiet_hublot/hublot3-2.png"
            ],
             [
                "h3" => "CHẾ TÁC MAGIC GOLD & TITANIUM",
                "h2" => "Cam kết độ bền và đẳng cấp theo thời gian",
                "content" => [
                    "Sản phẩm là hiện thân rõ nét của triết lý “Art of Fusion”, kết hợp các vật liệu hiệu suất cao lấy cảm hứng từ công nghệ đường đua Ferrari. Bộ vỏ được chế tác từ Carbon Ceramic, mang lại độ cứng vượt trội, trọng lượng nhẹ, khả năng chống trầy xước và chịu va đập ấn tượng.",
                    "Các chi tiết như nút bấm chronograph, khóa dây và vỏ phụ được chế tác từ Titanium, tối ưu trọng lượng và độ bền. Tổng thể cấu trúc thể hiện trình độ chế tác cao cấp và triết lý “Art of Fusion” đặc trưng Hublot."
                ],
                "img" => "../image/chitiet_hublot/hublot3-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Hublot Big Bang Ferrari 1000 GP Carbon Ceramic 45mm – Ref. 402.QC.0112.NR được trang bị dây cao su mềm mại, đàn hồi tốt, ôm sát cổ tay, phù hợp đeo dài ngày. Bề mặt xử lý tinh tế giúp hạn chế mồ hôi, đảm bảo sự thoải mái và ổn định trong mọi điều kiện sử dụng."
                ],
                "img" => "../image/chitiet_hublot/hublot3-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG FERRARI UNICO",
                "h2" => "Sức mạnh cơ khí mang tinh thần Ferrari",
                "content" => [
                    "Bộ máy in-house HUB1241 UNICO chronograph tự động",
                    "Dự trữ năng lượng ~72 giờ",
                    "Khả năng chống nước 100m (10 ATM)",
                    "Mặt số skeleton thể thao, dễ quan sát",
                    "Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng"
                ],
                "img" => "../image/chitiet_hublot/hublot3-6.png"
            ],
        ]
    ],

     //SP 4
    24 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "Vẻ Đẹp Vượt Thời Gian Trong Sự Giao Thoa Giữa Vàng Và Kim Cương",
                "h2" => "KHUNG VỎ VÀNG HỒNG 18K ĐẲNG CẤP",
                "content" => [
                    "Sự sang trọng tinh tế trên từng đường nét Sở hữu bộ vỏ 38mm lý tưởng cho cổ tay phụ nữ Á Đông, sản phẩm được chế tác từ Vàng hồng 18K (King Gold) với sắc thái ấm áp độc quyền. Sự kết hợp giữa các bề mặt đánh bóng gương và phay xước tỉ mỉ không chỉ tạo nên vẻ ngoài lộng lẫy mà còn khẳng định giá trị vĩnh cửu của một món trang sức cao cấp."
                ],
                "img" => "../image/chitiet_hublot/hublot4-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL NẬM 126 VIÊN KIM CƯƠNG",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "Hào quang rực rỡ thu hút mọi ánh nhìn Điểm nhấn kiêu sa nhất chính là vành bezel được đính 126 viên kim cương tinh khiết (tổng trọng lượng ~0.87 carat). Kỹ thuật nạm đá thủ công bậc thầy của Hublot giúp các viên kim cương bắt sáng tối đa, bao quanh mặt số trắng thanh khiết để tạo nên một tổng thể xa hoa và đầy nữ tính."
                ],
                "img" => "../image/chitiet_hublot/hublot4-2.png"
            ],
             [
                "h3" => "THIẾT KẾ MẶT SỐ TRẮNG TRANG NHÃ",
                "h2" => "Sự tinh giản mang phong cách thượng lưu",
                "content" => [
                    " Mặt số màu trắng tinh khôi được thiết kế tối giản, làm nổi bật bộ kim và các cọc số bằng vàng hồng có phủ dạ quang. Với phiên bản 38mm, mặt số trở nên thanh thoát hơn khi không có các mặt số phụ chronograph, tập trung hoàn toàn vào vẻ đẹp của sự cân đối và dễ dàng quan sát thời gian."
                ],
                "img" => "../image/chitiet_hublot/hublot4-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TRẮNG CẤU TRÚC ĐỘC ĐÁO",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Đồng hồ sử dụng dây đeo cao su trắng cao cấp với bề mặt được dập vân cấu trúc sọc dọc đặc trưng. Chất liệu này không chỉ mang lại vẻ ngoài trẻ trung, năng động mà còn cực kỳ mềm mại, ôm sát cổ tay và bền bỉ trong mọi điều kiện thời tiết, đúng với triết lý 'Fusion' của hãng."
                ],
                "img" => "../image/chitiet_hublot/hublot4-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG 41MM",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Sự hội tụ của nghệ thuật kim hoàn và độ bền:",
                    "Bộ máy Quartz Thụy Sĩ: Đảm bảo độ chính xác cực cao, cực kỳ tiện lợi cho phái đẹp vì không cần lên dây cót hàng ngày.",
                    "Chống nước 100 mét (10 bar): An toàn tuyệt đối khi rửa tay, đi mưa hoặc tham gia các hoạt động bơi lội nhẹ nhàng.",
                    "Kính Sapphire chống phản quang: Bảo vệ mặt số khỏi mọi va chạm và trầy xước, duy trì độ trong suốt vĩnh cửu.",
                    "Khóa gập vàng 18K & Thép PVD: Hệ thống khóa gập chắc chắn, giúp việc đeo và tháo đồng hồ trở nên dễ dàng và sang trọng.",
                    "Bên cạnh đó, Hublot Big Bang Gold White Diamonds 38mm 361.PE.2010.RW.1104 còn được trang bị bộ máy Quartz chạy bằng pin của Hublot có tên gọi Cal HUB2900 có thể vận hành tốt và bền bỉ trong khoảng thời gian 4-5 năm để thay hệ thống pin mới, đây là một sự lựa chọn hoàn hảo và tiện lợi dành cho các quý cô khi không phải lên dây cót thường xuyên cho đồng hồ."

                ],
                "img" => "../image/chitiet_hublot/hublot4-6.png"
            ],
        ]
    ],

 //SP 5
    25 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ BLACK CERAMIC HIỆU SUẤT CAO",
                "h2" => "Sức mạnh cơ khí hiện đại trong diện mạo tối giản",
                "content" => [
                    "Mẫu Hublot Big Bang Meca-10 Black Magic sở hữu bộ vỏ Ceramic đen cao cấp, nổi bật với khả năng chống trầy xước vượt trội, nhẹ và bền bỉ. Tông đen mờ mang đến vẻ ngoài mạnh mẽ, hiện đại, đúng tinh thần kỹ thuật cao của Hublot."
                ],
                "img" => "../image/chitiet_hublot/hublot5-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL BLACK CERAMIC & DÂY CAO SU",
                "h2" => "Thiết kế Big Bang biểu tượng – đậm chất thể thao",
                "content" => [
                    "Vành bezel Ceramic đen được cố định bằng vít chữ H đặc trưng, tạo nên nhận diện không thể nhầm lẫn của Hublot. Kết hợp cùng dây cao su tự nhiên, mang lại cảm giác đeo linh hoạt, thoải mái và phù hợp với phong cách thể thao cao cấp."
                ],
                "img" => "../image/chitiet_hublot/hublot5-2.png"
            ],
             [
                "h3" => "CHẾ TÁC CERAMIC & CƠ KHÍ LỘ MÁY",
                "h2" => "Tôn vinh nghệ thuật cơ học đương đại",
                "content" => [
                    "Cấu trúc vỏ và các chi tiết được hoàn thiện tỉ mỉ, làm nổi bật bộ máy cơ khí lộ máy đầy mạnh mẽ. Thiết kế skeleton cho phép quan sát trực tiếp hoạt động cơ học, thể hiện trọn vẹn triết lý “Art of Fusion” của Hublot."
                ],
                "img" => "../image/chitiet_hublot/hublot5-4.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Thoải mái và bền bỉ cho sử dụng hằng ngày",
                "content" => [
                    "Dây cao su mềm mại, đàn hồi tốt, ôm sát cổ tay và hạn chế thấm mồ hôi. Thiết kế tối giản giúp đồng hồ dễ dàng kết hợp với nhiều phong cách, từ thường ngày đến thể thao cao cấp."
                ],
                "img" => "../image/chitiet_hublot/hublot5-3.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG MECA-10",
                "h2" => "Sức mạnh cơ khí thuần túy",
                "content" => [
                    "Bộ máy in-house HUB1201 Meca-10 lên cót tay",
                    "Dự trữ năng lượng lên đến 10 ngày (240 giờ) – hiển thị trực quan trên mặt số",
                    "Mặt số skeleton cơ khí ấn tượng",
                    "Khả năng chống nước 100m (10 ATM)",
                    "Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng"
                ],
                "img" => "../image/chitiet_hublot/hublot5-6.png"
            ],
        ]
],

 //SP 6
    26 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON & RED CERAMIC HIỆU SUẤT CAO",
                "h2" => "Biểu tượng công nghệ chế tác mang tinh thần Ferrari",
                "content" => [
                    "Đồng hồ sở hữu bộ vỏ kết hợp Carbon siêu nhẹ và Ceramic đỏ hiệu suất cao, mang lại độ bền vượt trội, khả năng chống trầy xước và trọng lượng tối ưu. Sự tương phản giữa carbon đen và ceramic đỏ tạo nên diện mạo mạnh mẽ, táo bạo và đậm chất tốc độ Ferrari."
                ],
                "img" => "../image/chitiet_hublot/hublot6-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CARBON & DÂY CAO SU TỰ NHIÊN",
                "h2" => "Sự giao thoa giữa công nghệ đường đua và tính ứng dụng cao",
                "content" => [
                    "Vành bezel Carbon với thiết kế Big Bang đặc trưng, cố định bằng vít titanium chữ H biểu tượng. Kết hợp cùng dây cao su tự nhiên cao cấp, mang lại cảm giác đeo linh hoạt, thoải mái và phong cách motorsport rõ nét."
                ],
                "img" => "../image/chitiet_hublot/hublot6-2.png"
            ],
             [
                "h3" => "CHẾ TÁC CARBON, CERAMIC & TITANIUM",
                "h2" => "Cam kết độ bền và hiệu năng vượt thời gian",
                "content" => [
                    "Bộ vỏ và các chi tiết như nút bấm chronograph, vít bezel và khóa dây được chế tác từ Titanium, tối ưu trọng lượng và độ bền. Tổng thể cấu trúc thể hiện rõ triết lý “Art of Fusion” cùng DNA cơ khí mạnh mẽ của Ferrari."
                ],
                "img" => "../image/chitiet_hublot/hublot6-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Dây cao su mềm mại, đàn hồi tốt, ôm sát cổ tay và hạn chế thấm mồ hôi. Thiết kế thể thao giúp đồng hồ phù hợp cho cả sử dụng hằng ngày lẫn các hoạt động năng động."
                ],
                "img" => "../image/chitiet_hublot/hublot6-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG FERRARI UNICO",
                "h2" => "Sức mạnh cơ khí mang tinh thần tốc độ",
                "content" => [
                    "Bộ máy in-house HUB1241 UNICO chronograph tự động",
                    "Dự trữ năng lượng khoảng 72 giờ",
                    "Mặt số skeleton thể thao, dễ quan sát",
                    "Khả năng chống nước 100m (10 ATM)",
                    "Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng"
                ],
                "img" => "../image/chitiet_hublot/hublot6-6.png"
            ],
        ]
],
    //SP 7
    27 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ KING GOLD 18K SANG TRỌNG",
                "h2" => "Biểu tượng đẳng cấp chế tác của Hublot",
                "content" => [
                    "Đồng hồ sở hữu bộ vỏ King Gold 18K độc quyền của Hublot, nổi bật với sắc vàng ấm pha hợp kim đặc biệt giúp tăng độ bền và giữ màu vượt trội. Kích thước 44mm mang lại diện mạo mạnh mẽ nhưng vẫn tinh tế, phù hợp phong cách thể thao sang trọng."
                ],
                "img" => "../image/chitiet_hublot/hublot7-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL KING GOLD & DÂY DA CAO CẤP",
                "h2" => "Sự cân bằng giữa thể thao và thanh lịch",
                "content" => [
                    "Vành bezel King Gold được cố định bằng vít chữ H biểu tượng, tạo nên dấu ấn Big Bang đặc trưng. Kết hợp cùng dây da cao cấp màu xanh, mang lại cảm giác đeo êm ái, sang trọng và dễ phối trang phục."
                ],
                "img" => "../image/chitiet_hublot/hublot7-2.png"
            ],
             [
                "h3" => "CHẾ TÁC KING GOLD & THÉP/TITANIUM",
                "h2" => "Độ bền và thẩm mỹ theo thời gian",
                "content" => [
                    "Các chi tiết vỏ, núm chỉnh giờ và khóa dây được hoàn thiện tinh xảo, đảm bảo độ chắc chắn và tính thẩm mỹ cao. Tổng thể đồng hồ thể hiện rõ triết lý “Art of Fusion” – kết hợp hài hòa giữa vật liệu quý và kỹ thuật chế tác hiện đại."
                ],
                "img" => "../image/chitiet_hublot/hublot7-3.png"
            ],
             [
                "h3" => "DÂY DA CAO CẤP",
                "h2" => "Thoải mái và lịch lãm trên cổ tay",
                "content" => [
                    "Dây da mềm mại, ôm sát cổ tay, mang lại sự thoải mái khi đeo lâu. Tông xanh trang nhã góp phần tôn lên vẻ sang trọng và đẳng cấp của mẫu Big Bang Gold Blue."
                ],
                "img" => "../image/chitiet_hublot/hublot7-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG GOLD BLUE",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng",
                    "Vẻ đẹp hoàn mỹ của chiếc đồng hồ Hublot Big Bang Gold Blue Diamonds 41mm, là siêu phẩm đồng hồ của nhà Hublot. Màu xanh navy bắt mắt được ví là màu sắc yêu thích nhất của Hublot, phiên bản bán chạy nhất của hãng Hublot dòng BigBang. Ông Carlo Crocco đã tạo ra kiệt tác Big Bang Gold Diamonds với đường nét tinh xảo, tuyển chọn từng viên kim cương đính trên vành bezel đẹp hoàn hảo, tỏa sáng thu hút mọi ánh nhìn."
                ],
                "img" => "../image/chitiet_hublot/hublot7-6.png"
            ],
        ]
],
//SP 8
    28 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ KING GOLD & BLACK CERAMIC",
                "h2" => "Sự kết hợp hài hòa giữa sang trọng và thể thao",
                "content" => [
                    "Mẫu đồng hồ sở hữu bộ vỏ King Gold 18K kết hợp cùng Ceramic đen cao cấp, mang lại độ bền cao, khả năng chống trầy xước tốt và vẻ ngoài mạnh mẽ, hiện đại. Kích thước 41mm gọn gàng, phù hợp cổ tay thanh lịch nhưng vẫn giữ trọn tinh thần Big Bang."

                ],
                "img" => "../image/chitiet_hublot/hublot8-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CERAMIC & DÂY CAO SU",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "Vành bezel Ceramic đen được cố định bằng vít chữ H đặc trưng của Hublot, tạo điểm nhấn nhận diện rõ nét. Kết hợp cùng dây cao su tự nhiên, mang lại cảm giác đeo êm ái, linh hoạt và phù hợp sử dụng hằng ngày."
                ],
                "img" => "../image/chitiet_hublot/hublot8-2.png"
            ],
             [
                "h3" => "CHẾ TÁC KING GOLD, CERAMIC & THÉP/TITANIUM",
                "h2" => "Độ bền và thẩm mỹ theo thời gian",
                "content" => [
                    "Các chi tiết vỏ, núm chỉnh giờ và khóa dây được hoàn thiện tinh xảo, đảm bảo độ chắc chắn và độ bền lâu dài. Tổng thể thiết kế thể hiện rõ triết lý “Art of Fusion”, kết hợp vật liệu quý cùng kỹ thuật chế tác hiện đại của Hublot."
                ],
                "img" => "../image/chitiet_hublot/hublot8-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Hublot Big Bang Gold Ceramic 41mm được trang bị dây cao su tự nhiên cao cấp, nhẹ, mềm và có độ đàn hồi cao, giúp ôm sát cổ tay nhưng vẫn mang lại cảm giác thoải mái khi đeo lâu dài. Thiết kế công thái học kết hợp bề mặt hạn chế thấm mồ hôi, chống bám bụi cùng khóa gập chắc chắn, đảm bảo sự linh hoạt, an tâm và phong cách thể thao sang trọng đặc trưng của Hublot."
                ],
                "img" => "../image/chitiet_hublot/hublot8-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG 41MM",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Bộ dây cao su từ lâu vốn đã là đặc trưng của Hublot, và thật sự thì ít có thương hiệu có thể tự tin rằng họ có thể sản xuất dây cao su chất lượng hơn Hublot. Bộ dây này được cố định bởi khóa cài khắc tên thương hiệu Hublot. Giống với bộ vỏ, khóa dây cũng được kết hợp hai chất liệu Titanium và Ceramic với hai tông màu đen/xám hiện đại.",
                    "Như đã nói ở phần đầu bài viết, kích thước 41mm của chiếc Hublot Big Bang Unico Titanium Ceramic cực kỳ phù hợp với cổ tay người châu Á. Thiết kế này sẽ thực sự hoàn hảo nếu kết hợp với những bộ đồ thể thao năng động, nhưng bạn cũng có thể thấy rằng việc mặc vest đeo chiếc đồng hồ này cũng không tồi chút nào."
                ],
                "img" => "../image/chitiet_hublot/hublot8-6.png"
            ],
        ]
],
//SP 9
    29 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ BLACK CERAMIC HIỆU SUẤT CAO",
                "h2" => "Sức mạnh cơ khí hiện đại trong diện mạo tối giản",
                "content" => [
                    "Mẫu Hublot Big Bang Meca-10 Black Magic sở hữu bộ vỏ Ceramic đen cao cấp, nổi bật với khả năng chống trầy xước vượt trội, nhẹ và bền bỉ. Tông đen mờ mang đến vẻ ngoài mạnh mẽ, hiện đại, đúng tinh thần kỹ thuật cao của Hublot.",
                    "Kết quả là một loại vật liệu có độ cứng vượt xa thép không gỉ, giúp chiếc đồng hồ gần như 'bất tử' trước những va chạm hay trầy xước từ môi trường bên ngoài. Sau nhiều năm sử dụng, trong khi các dòng đồng hồ kim loại có thể hiện rõ những vết xước dăm, lớp vỏ Ceramic đen mờ này vẫn giữ nguyên vẻ sắc sảo như ngày đầu tiên rời xưởng."
                ],
                "img" => "../image/chitiet_hublot/hublot9-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL BLACK CERAMIC & DÂY CAO SU",
                "h2" => "Thiết kế Big Bang biểu tượng – đậm chất thể thao",
                "content" => [
                    "Vành bezel Ceramic đen được cố định bằng vít chữ H đặc trưng, tạo nên nhận diện không thể nhầm lẫn của Hublot. Kết hợp cùng dây cao su tự nhiên, mang lại cảm giác đeo linh hoạt, thoải mái và phù hợp với phong cách thể thao cao cấp.",
                    "Khả năng chịu nhiệt và tính trơ hóa học tuyệt vời; nó không bị oxy hóa, không phai màu dưới tác động của tia UV hay nước biển, biến chiếc đồng hồ trở thành một người đồng hành bền bỉ trong mọi điều kiện khắc nghiệt nhất."
                ],
                "img" => "../image/chitiet_hublot/hublot9-2.png"
            ],
             [
                "h3" => "CHẾ TÁC CERAMIC & CƠ KHÍ LỘ MÁY",
                "h2" => "Tôn vinh nghệ thuật cơ học đương đại",
                "content" => [
                    "Cấu trúc vỏ và các chi tiết được hoàn thiện tỉ mỉ, làm nổi bật bộ máy cơ khí lộ máy đầy mạnh mẽ. Thiết kế skeleton cho phép quan sát trực tiếp hoạt động cơ học, thể hiện trọn vẹn triết lý “Art of Fusion” của Hublot.",
                    "Đặc biệt, vật liệu này có khả năng thích ứng nhiệt rất nhanh với cơ thể người đeo. Bạn sẽ không cảm thấy cái lạnh buốt của kim loại vào mùa đông hay cảm giác bết dính vào mùa hè, mà thay vào đó là một sự mượt mà, thoải mái đến kỳ lạ."
                ],
                "img" => "../image/chitiet_hublot/hublot9-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TỰ NHIÊN CAO CẤP",
                "h2" => "Thoải mái và bền bỉ cho sử dụng hằng ngày",
                "content" => [
                    "Dây cao su mềm mại, đàn hồi tốt, ôm sát cổ tay và hạn chế thấm mồ hôi. Thiết kế tối giản giúp đồng hồ dễ dàng kết hợp với nhiều phong cách, từ thường ngày đến thể thao cao cấp. Phương pháp này tạo ra một lớp hoàn thiện mịn màng, không bóng loáng phô trương nhưng lại có khả năng hấp thụ ánh sáng cực tốt, làm nổi bật các góc cạnh cơ khí của dòng Big Bang. Sắc đen sâu thẳm này không chỉ mang tính thẩm mỹ mà còn là một phần của triết lý 'Invisible Visibility' – sự hiện diện đầy quyền lực nhưng không cần lên tiếng của dòng dõi Black Magic."
                ],
                "img" => "../image/chitiet_hublot/hublot9-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG MECA-10",
                "h2" => "Sức mạnh cơ khí thuần túy",
                "content" => [
                    "Điểm nhấn đắt giá nhất nằm ở cấu trúc Skeleton lộ cơ hoàn toàn, phô diễn bộ máy in-house HUB1201 đầy phức tạp. Thay vì những cầu nối truyền thống, Meca-10 sử dụng hệ thống thanh răng, bánh xe và cầu nối được sắp xếp theo bố cục hình học tầng lớp, gợi liên tưởng đến những bộ mô hình lắp ráp kỹ thuật Meccano kinh điển. Sự chuyển động nhịp nhàng của các chi tiết cơ khí dưới lớp kính Sapphire không chỉ là một bộ đếm thời gian, mà còn là một màn trình diễn công nghệ đầy mê hoặc ngay trên cổ tay.",
                    "Dự trữ năng lượng lên đến 10 ngày (240 giờ) – hiển thị trực quan trên mặt số",
                    "Mặt số skeleton cơ khí ấn tượng",
                    "Khả năng chống nước 100m (10 ATM)",
                    "Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng"
                                    ],
                "img" => "../image/sp9-hublot.png"
            ],
        ]
],

//SP 10
    30 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KKHUNG VỎ VÀNG HỒNG 18K ĐẲNG CẤP",
                "h2" => "Vẻ Đẹp Vượt Thời Gian Trong Sự Giao Thoa Giữa Vàng Và Kim Cương",
                "content" => [
                    "Sự sang trọng tinh tế trên từng đường nét Sở hữu bộ vỏ 38mm lý tưởng cho cổ tay phụ nữ Á Đông, sản phẩm được chế tác từ Vàng hồng 18K (King Gold) với sắc thái ấm áp độc quyền. Sự kết hợp giữa các bề mặt đánh bóng gương và phay xước tỉ mỉ không chỉ tạo nên vẻ ngoài lộng lẫy mà còn khẳng định giá trị vĩnh cửu của một món trang sức cao cấp.",
                    ". Với kích thước 38mm vừa vặn, chiếc đồng hồ không quá phô trương nhưng vẫn đủ sức làm bừng sáng cổ tay, mang lại vẻ đẹp kiêu sa, hiện đại cho những quý cô sành điệu."
                ],
                "img" => "../image/chitiet_hublot/hublot10-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL NẬM 126 VIÊN KIM CƯƠNG",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "Hào quang rực rỡ thu hút mọi ánh nhìn Điểm nhấn kiêu sa nhất chính là vành bezel được đính 126 viên kim cương tinh khiết (tổng trọng lượng ~0.87 carat). Kỹ thuật nạm đá thủ công bậc thầy của Hublot giúp các viên kim cương bắt sáng tối đa, bao quanh mặt số trắng thanh khiết để tạo nên một tổng thể xa hoa và đầy nữ tính.",
                    "Điểm nhấn đắt giá nhất chính là vành bezel được nạm 126 viên kim cương lấp lánh, được tuyển chọn kỹ lưỡng về độ trong và màu sắc. Dưới ánh sáng tự nhiên hay ánh đèn của những buổi tiệc đêm, những viên kim cương này tạo ra hiệu ứng tán sắc rực rỡ, ôm trọn lấy mặt số trắng trang nhã."
                ],
                "img" => "../image/chitiet_hublot/hublot10-2.png"
            ],
             [
                "h3" => "THIẾT KẾ MẶT SỐ TRẮNG TRANG NHÃ",
                "h2" => "Sự tinh giản mang phong cách thượng lưu",
                "content" => [
                    " Mặt số màu trắng tinh khôi được thiết kế tối giản, làm nổi bật bộ kim và các cọc số bằng vàng hồng có phủ dạ quang. Với phiên bản 38mm, mặt số trở nên thanh thoát hơn khi không có các mặt số phụ chronograph, tập trung hoàn toàn vào vẻ đẹp của sự cân đối và dễ dàng quan sát thời gian. Sáu chiếc đinh vít hình chữ H đặc trưng bằng Titanium mọc lên giữa 'rừng' kim cương, tạo nên sự tương phản thú vị giữa nét nữ tính mềm mại và tinh thần thể thao mạnh mẽ."
                  
                ],
                "img" => "../image/chitiet_hublot/hublot10-3.png"
            ],
             [
                "h3" => "DÂY CAO SU TRẮNG CẤU TRÚC ĐỘC ĐÁO",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Đồng hồ sử dụng dây đeo cao su trắng cao cấp với bề mặt được dập vân cấu trúc sọc dọc đặc trưng. Chất liệu này không chỉ mang lại vẻ ngoài trẻ trung, năng động mà còn cực kỳ mềm mại, ôm sát cổ tay và bền bỉ trong mọi điều kiện thời tiết, đúng với triết lý 'Fusion' của hãng.".
                    "Dù mang vẻ ngoài của một món trang sức cao cấp, chiếc Big Bang này vẫn giữ trọn DNA năng động nhờ bộ dây cao su trắng đặc trưng. Chất liệu cao su mềm mại, không thấm mồ hôi và cực kỳ bền bỉ giúp người đeo cảm thấy nhẹ nhàng, thoải mái dù là trong những buổi họp quan trọng hay những chuyến du lịch xa. Khóa gấp bằng vàng hồng 18K và thép mạ đen không chỉ đảm bảo sự chắc chắn mà còn là chi tiết hoàn thiện cuối cùng cho sự đẳng cấp."
                    ],

                "img" => "../image/chitiet_hublot/hublot10-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG 41MM",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Sự hội tụ của nghệ thuật kim hoàn và độ bền",
                    "Bộ máy Quartz Thụy Sĩ: Đảm bảo độ chính xác cực cao, cực kỳ tiện lợi cho phái đẹp vì không cần lên dây cót hàng ngày.",
                    "Chống nước 100 mét (10 bar): An toàn tuyệt đối khi rửa tay, đi mưa hoặc tham gia các hoạt động bơi lội nhẹ nhàng.",
                    "Kính Sapphire chống phản quang: Bảo vệ mặt số khỏi mọi va chạm và trầy xước, duy trì độ trong suốt vĩnh cửu.",
                    "Khóa gập vàng 18K & Thép PVD: Hệ thống khóa gập chắc chắn, giúp việc đeo và tháo đồng hồ trở nên dễ dàng và sang trọng.",
                    "Bên cạnh đó, Hublot Big Bang Gold White Diamonds 38mm 361.PE.2010.RW.1104 còn được trang bị bộ máy Quartz chạy bằng pin của Hublot có tên gọi Cal HUB2900 có thể vận hành tốt và bền bỉ trong khoảng thời gian 4-5 năm để thay hệ thống pin mới, đây là một sự lựa chọn hoàn hảo và tiện lợi dành cho các quý cô khi không phải lên dây cót thường xuyên cho đồng hồ.",
                                    ],
                "img" => "../image/sp10-hublot.png"
            ],
        ]
],
//SP 11
    31 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP NẠM KIM CƯƠNG TOÀN THÂN (PAVÉ)",
                "h2" => "Sự kết hợp hài hòa giữa sang trọng và thể thao",
                "content" => [
                    "Sự lộng lẫy bao phủ mọi góc nhìn Sở hữu kích thước 39mm hoàn hảo cho mọi cổ tay, phiên bản này gây ấn tượng mạnh mẽ với lớp vỏ bằng thép không gỉ được nạm kín kim cương tinh khiết. Kỹ thuật nạm 'Pavé' (nạm thảm) giúp các viên đá quý xếp sát nhau, tạo nên một bề mặt rực rỡ, biến chiếc đồng hồ thành một khối ánh sáng kiêu sa trên cổ tay chủ nhân.",
                    "Hublot Big Bang One Click King Gold White Pavé 39mm, chúng ta không chỉ nói về một chiếc đồng hồ, mà là một 'bữa tiệc ánh sáng' thực thụ. So với bản 38mm bạn vừa xem, bản 39mm này nâng tầm sự xa hoa lên một bậc với kỹ thuật nạm kim cương Full Pavé dày đặc và tính năng thay dây thông minh."
                ],
                "img" => "../image/chitiet_hublot/hublot11-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL KIM CƯƠNG ĐẶC TRƯNG",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "Hào quang rực rỡ từ sự tỉ mỉ Vành bezel được đính thêm 42 viên kim cương lấp lánh, cố định bởi 6 ốc vít hình chữ H biểu tượng của Hublot. Sự kết hợp giữa thép sáng bóng và kim cương không chỉ mang lại vẻ đẹp thanh lịch mà còn thể hiện sự bền bỉ, hiện đại của dòng đồng hồ thể thao xa xỉ bậc nhất thế giới.",
                    "Ánh sáng không chỉ lấp lánh trên vành bezel mà còn len lỏi qua từng kẽ hở của bộ vỏ King Gold, tạo nên một hiệu ứng thị giác rực rỡ và lộng lẫy đến choáng ngợp."
                ],
                "img" => "../image/chitiet_hublot/hublot11-2.png"
            ],
             [
                "h3" => "MẶT SỐ TRẮNG TINH KHÔI SANG TRỌNG",
                "h2" => "Trải nghiệm kỹ thuật số đỉnh cao",
                "content" => [
                    " Nét đẹp thuần khiết và dễ quan sát Mặt số màu trắng mịn màng làm nền hoàn hảo cho các cọc số và kim chỉ giờ phủ dạ quang. Thiết kế tối giản nhưng tinh tế giúp việc xem giờ trở nên dễ dàng, đồng thời tôn vinh nét nữ tính, sang trọng mà mọi quý cô đều khao khát sở hữu.",
                    "Sắc vàng hồng nồng nàn này đóng vai trò như một phông nền hoàn hảo, làm tôn lên độ trong suốt và tinh khiết của những viên kim cương trắng. Đây là sự kết hợp màu sắc kinh điển, đại diện cho sự vương giả, thịnh vượng và gu thẩm mỹ không thỏa hiệp."
                ],
                "img" => "../image/chitiet_hublot/hublot11-3.png"
            ],
             [
                "h3" => "CÔNG NGHỆ THAY DÂY 'ONE CLICK' ĐỘC QUYỀN",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Biến hóa phong cách chỉ trong một lần nhấn Đúng như tên gọi One Click, chiếc đồng hồ cho phép bạn thay đổi dây đeo một cách nhanh chóng và an toàn chỉ với một thao tác bấm nút đơn giản. Đi kèm là dây cao su trắng cao cấp với vân cấu trúc sọc dọc, mang lại sự êm ái tuyệt đối và phong cách năng động cho ngày dài.",
                    "Tính năng này cho phép quý cô biến hóa từ phong cách thể thao, năng động ban ngày sang vẻ sang trọng, quyến rũ cho những buổi tiệc tối chỉ trong vài giây mà không cần bất kỳ công cụ hỗ trợ nào."
                    ],

                "img" => "../image/chitiet_hublot/hublot11-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG 41MM",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Cỗ máy thời gian bền bỉ và đẳng cấp",
                    "- Bộ máy Automatic HUB1710: Khả năng dự trữ năng lượng ấn tượng lên đến 50 giờ, vận hành mượt mà và bền bỉ.",
                    "- Chống nước 100 mét (10 bar): An toàn tuyệt đối trong các hoạt động hàng ngày như rửa tay, đi mưa hoặc bơi lội nhẹ nhàng.",
                    "- Kính Sapphire nguyên khối: Khả năng chống trầy xước gần như tuyệt đối với lớp phủ chống phản quang cao cấp.",
                    "- Hệ thống nạm đá thủ công: Từng viên kim cương đều được tuyển chọn và đính bằng tay bởi những nghệ nhân kim hoàn hàng đầu.",
                    "Bên cạnh đó, Hublot Big Bang One Click King Gold White Pavé 39mm 465.SE.2010.RW.1604 còn được trang bị bộ máy tự động HUB1710, được chế tác 27 chân kính bao gồm 185 bộ phận và có khả năng dự trữ năng lượng lên đến 50 giờ khi được lên cót tự động.",
                    ],
                "img" => "../image/sp11-hublot.png"
            ],
        ]
],
//SP 12
    32 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ KING GOLD & TITANIUM",
                "h2" => "Nghệ thuật hình học đỉnh cao mang dấu ấn Sang Bleu",
                "content" => [
                    "Đồng hồ sở hữu bộ vỏ King Gold 18K độc quyền của Hublot, sắc vàng ấm pha hợp kim đặc biệt giúp tăng độ bền và giữ màu vượt trội. Kết hợp cùng Titanium ở cấu trúc bên trong, mang lại sự cân bằng hoàn hảo giữa sang trọng, chắc chắn và trọng lượng tối ưu.",
                    "Mẫu Hublot Big Bang One Click Sang Bleu King Gold Grey Diamonds không chỉ là đồng hồ, nó là một hình xăm bằng vàng và kim cương trên cổ tay, được thiết kế bởi nghệ sĩ xăm hình nổi tiếng Maxime Plescia-Büchi."
                ],
                "img" => "../image/chitiet_hublot/hublot12-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL ĐÍNH KIM CƯƠNG & DÂY CAO SU CAO CẤP",
                "h2" => "Sự giao thoa giữa nghệ thuật đương đại và xa xỉ hiện đại",
                "content" => [
                    "Vành bezel được đính kim cương xám (Grey Diamonds) theo bố cục hình học đặc trưng Sang Bleu, tạo hiệu ứng thị giác mạnh mẽ và đầy chiều sâu. Đi kèm là dây cao su cao cấp mang lại cảm giác đeo êm ái, linh hoạt và hiện đại.",
                    "Từng góc cạnh của King Gold được đánh bóng xen kẽ chải xước mịn màng, phản chiếu ánh sáng đa chiều. Đây là minh chứng cho trình độ gia công thượng thừa của Hublot khi có thể xử lý một vật liệu quý hiếm và cứng cáp thành những đường cắt sắc lẹm như dao cạo."
                ],
                "img" => "../image/chitiet_hublot/hublot12-2.png"
            ],
             [
                "h3" => "CHẾ TÁC KING GOLD, TITANIUM & KIM CƯƠNG",
                "h2" => "Cam kết thẩm mỹ và đẳng cấp bền vững theo thời gian",
                "content" => [
                    "Các chi tiết như vỏ, bezel và núm chỉnh giờ được hoàn thiện tinh xảo, kết hợp giữa King Gold, Titanium và kim cương tuyển chọn. Mỗi góc cạnh hình học đều thể hiện triết lý “Art of Fusion” cùng tinh thần thiết kế độc bản của Sang Bleu.",
                    "Trên nền chất liệu King Gold 18K với sắc đỏ nồng nàn, Hublot đã khéo léo nạm những viên kim cương tinh tuyển chạy dọc theo vành bezel và các đường cắt của bộ vỏ. Điểm đặc biệt ở phiên bản này chính là sự kết hợp với tông màu Grey (Xám) chủ đạo của mặt số và dây đeo. Sắc xám trung tính, hiện đại giúp kìm hãm bớt sự phô trương của vàng hồng, đồng thời làm tôn lên ánh lửa rực rỡ của kim cương, tạo nên một tổng thể sang trọng nhưng vô cùng tinh tế và lạnh theo cách rất riêng."
                ],
                "img" => "../image/chitiet_hublot/hublot12-3.png"
            ],
             [
                "h3" => "DÂY CAO SU CAO CẤP",
                "h2" => "Thoải mái và thanh lịch trên cổ tay",
                "content" => [
                    "Dây cao su mềm mại, đàn hồi tốt, ôm sát cổ tay và phù hợp đeo hàng ngày. Tông xám trung tính giúp tổng thể đồng hồ giữ được vẻ sang trọng, cá tính và dễ phối phong cách.",
                    "Sự kết hợp giữa bộ máy tự động HUB1710 bền bỉ và khả năng chống nước ấn tượng giúp Sang Bleu King Gold Grey Diamonds không chỉ để ngắm nhìn trong tủ kính, mà còn sẵn sàng đồng hành cùng chủ nhân trong mọi sự kiện đẳng cấp."
                    ],

                "img" => "../image/chitiet_hublot/hublot12-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG ONE CLICK SANG BLEU",
                "h2" => "Nghệ thuật cơ khí trong diện mạo trang sức cao cấp",
                "content" => [
                    "Sự khác biệt lớn nhất của dòng Sang Bleu chính là việc loại bỏ hoàn toàn các kim đồng hồ truyền thống để thay thế bằng hệ thống ba đĩa xoay hình bát giác xếp chồng lên nhau. Những đường nét hình học sắc sảo, đan xen theo tỉ lệ vàng tạo nên một cấu trúc không gian ba chiều đầy mê hoặc. Mỗi khi thời gian trôi qua, sự dịch chuyển của các khối đa diện này tạo ra những hình thái đối xứng mới, biến mặt số đồng hồ thành một tác phẩm nghệ thuật động lực học không bao giờ lặp lại.",
                    "- Dự trữ năng lượng khoảng 50 giờ",
                    "- Khả năng chống nước 100m (10 ATM)",
                    "- Mặt số hình học đa chiều mang dấu ấn Sang Bleu",
                    "- Kim và cọc số phủ dạ quang, hỗ trợ xem giờ trong điều kiện thiếu sáng"
                   
                    ],
                "img" => "../image/sp12-hublot.png"
            ],
        ]
],
];

$current = $allData[$type] ?? null;
?>

<?php if ($current): ?>
<section class="product-story-section" style="background-color: <?= $current['bg'] ?>;">
    <div class="story-container">
        <?php foreach ($current['items'] as $item): ?>
            <div class="story-block">
                <div class="story-text">
                    <h3><?= $item['h3'] ?></h3>
                    <h2><?= $item['h2'] ?></h2>
                    <?php foreach ($item['content'] as $p): ?>
                        <p><?= $p ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="story-img">
                    <img src="<?= $item['img'] ?>" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

    <?php
        $publicationImages = [
            21 => "hublot1_anpham.png",
            22 => "hublot1_anpham.png",
            23 => "hublot1_anpham.png",
            24 => "hublot1_anpham.png",
            25 => "hublot1_anpham.png",
            26 => "hublot1_anpham.png",
            27 => "hublot1_anpham.png",
            28 => "hublot1_anpham.png",
            29 => "hublot1_anpham.png",
            30 => "hublot1_anpham.png",
            31 => "hublot1_anpham.png",
            32 => "hublot1_anpham.png",
        ];

        $productId = $row['id'];
        $image = $publicationImages[$productId] ?? "default.jpg";
    ?>
    <section class="bottom-info-section">
        <h3 class="cert-title">Chứng nhận</h3>
        <p class="cert-desc">Superlative Chronometer (chứng nhận COSC + Hublot sau khi lắp vỏ)</p>

        <h3 class="pub-title" style="text-align: center;">Ấn phẩm</h3>
        <a href="#" class="download-link">
            <i class="fa-solid fa-download"></i> Tải ấn phẩm
        </a>
        
        <img src="../image/chitiet_hublot/<?php echo $image; ?>" 
            style="max-width:300px; box-shadow: none !important; border-radius: 15px !important; border: none !important; outline: none !important;" 
            alt="Ấn phẩm Hublot" 
            class="publication-img"
            onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
    </section>

    <style>
        .comment-section { max-width: 1200px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
        .comment-title { font-size: 20px; border-bottom: 2px solid #b58b5a; padding-bottom: 10px; margin-bottom: 20px; color: #333; font-family: 'Playfair Display', serif; }
        .comment-form { display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px; }
        .comment-form textarea { width: 100%; height: 90px; padding: 15px; border: 1px solid #ccc; border-radius: 5px; resize: none; font-family: inherit; outline: none; }
        .comment-form textarea:focus { border-color: #b58b5a; }
        .comment-form button { align-self: flex-end; background: #b58b5a; color: #fff; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s;}
        .comment-form button:hover { background: #967045; }
        
        .comment-list { display: flex; flex-direction: column; gap: 20px; }
        .comment-item { display: flex; gap: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .comment-avatar { width: 45px; height: 45px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #b58b5a; font-size: 20px; flex-shrink: 0;}
        .comment-content { flex: 1; }
        .comment-name { font-weight: bold; color: #333; margin-bottom: 5px; display: flex; justify-content: space-between; }
        .comment-date { font-size: 12px; color: #888; font-weight: normal; }
        .comment-text { color: #555; line-height: 1.6; font-size: 14px; }
        
        .login-prompt { background: #f9f9f9; padding: 20px; text-align: center; border-radius: 5px; border: 1px dashed #ccc; margin-bottom: 30px; }
        .login-prompt a { color: #b58b5a; font-weight: bold; text-decoration: none; }

        .comment-actions { margin-top: 8px; font-size: 12px; }
        .comment-actions button { background: none; border: none; cursor: pointer; color: #888; margin-right: 15px; padding: 0; font-family: inherit; transition: 0.3s; }
        .comment-actions button:hover { color: #b58b5a; }
        .comment-actions .btn-delete:hover { color: #d9534f; }
        
        .edit-box { display: none; margin-top: 10px; }
        .edit-box textarea { width: 100%; height: 70px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: none; margin-bottom: 5px; font-family: inherit; outline: none; }
        .edit-box button { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; }
        .btn-save-edit { background: #b58b5a; color: white; margin-right: 5px; }
        .btn-cancel-edit { background: #eee; color: #333; }

        /* CSS CHO MODAL XÁC NHẬN XÓA */
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 350px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .btn-confirm-del { background: #d9534f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px 5px; transition: 0.3s; }
        .btn-confirm-del:hover { background: #c9302c; }
        .btn-cancel-del { background: #eee; color: #333; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px 5px; transition: 0.3s; }
        .btn-cancel-del:hover { background: #ddd; }
    </style>

    <div class="comment-section">
        <h3 class="comment-title"><i class="fa-regular fa-comments"></i> Bình luận và Đánh giá</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <form action="../action_binhluan.php" method="POST" class="comment-form">
                <input type="hidden" name="id_san_pham" value="<?php echo $row['id']; ?>">
                <textarea name="noi_dung" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này..." required></textarea>
                <button type="submit" name="submit_comment"><i class="fa-solid fa-paper-plane"></i> Gửi bình luận</button>
            </form>
        <?php else: ?>
            <div class="login-prompt">
                Vui lòng <a href="../login.php">Đăng nhập</a> hoặc <a href="../login.php">Đăng ký</a> để tham gia bình luận về sản phẩm.
            </div>
        <?php endif; ?>

        <div class="comment-list">
            <?php
            $sp_id_hien_tai = $row['id'];
            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            // Lấy bình luận từ CSDL
            $sql_bl = "SELECT b.*, n.ho_ten 
                       FROM binh_luan b 
                       JOIN nguoi_dung n ON b.id_nguoi_dung = n.id 
                       WHERE b.id_san_pham = $sp_id_hien_tai AND b.trang_thai = 'Hiển thị' 
                       ORDER BY b.id DESC";
            $result_bl = $conn->query($sql_bl);
            
            if($result_bl && $result_bl->num_rows > 0):
                while($bl = $result_bl->fetch_assoc()):
            ?>
                <div class="comment-item" id="comment-<?php echo $bl['id']; ?>">
                    <div class="comment-avatar"><i class="fa-solid fa-user"></i></div>
                    <div class="comment-content">
                        <div class="comment-name">
                            <?php echo htmlspecialchars($bl['ho_ten']); ?>
                            <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($bl['ngay_binh_luan'])); ?></span>
                        </div>
                        
                        <div class="comment-text" id="text-<?php echo $bl['id']; ?>">
                            <?php echo nl2br(htmlspecialchars($bl['noi_dung'])); ?>
                        </div>

                        <?php if($current_user_id == $bl['id_nguoi_dung']): ?>
                            <div class="comment-actions">
                                <button onclick="showEditBox(<?php echo $bl['id']; ?>)"><i class="fa-solid fa-pen"></i> Sửa</button>
                                <button class="btn-delete" onclick="deleteComment(<?php echo $bl['id']; ?>)"><i class="fa-solid fa-trash"></i> Xóa</button>
                            </div>

                            <div class="edit-box" id="edit-box-<?php echo $bl['id']; ?>">
                                <textarea id="edit-input-<?php echo $bl['id']; ?>"><?php echo htmlspecialchars($bl['noi_dung']); ?></textarea>
                                <div>
                                    <button class="btn-save-edit" onclick="saveEdit(<?php echo $bl['id']; ?>)">Lưu thay đổi</button>
                                    <button class="btn-cancel-edit" onclick="hideEditBox(<?php echo $bl['id']; ?>)">Hủy</button>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php 
                endwhile;
            else: 
            ?>
                <p id="no-comment-msg" style="text-align: center; color: #888; font-style: italic;">Chưa có bình luận nào. Hãy là người đầu tiên đánh giá sản phẩm này!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="deleteModal" class="glass-modal">
        <div class="glass-modal-content">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 40px; color: #d9534f; margin-bottom: 10px;"></i>
            <h3 style="margin: 0; margin-bottom: 10px;">Xác nhận xóa?</h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Bình luận của bạn sẽ biến mất vĩnh viễn.</p>
            <input type="hidden" id="temp_del_id">
            <button class="btn-confirm-del" onclick="processDelete()">Xóa ngay</button>
            <button class="btn-cancel-del" onclick="document.getElementById('deleteModal').style.display='none'">Hủy</button>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo"><img src="../image/logo.png" alt="Timeless"></div>
            <h3 class="footer-title">TIMELESS</h3>
            <div class="footer-line"></div>
            <p>03-05 Pasteur, P. Nguyễn Thái Bình, Quận 1, TPHCM</p>
            <p><i class="fa fa-phone"></i> 0825549816</p>
            <p class="footer-desc">TIMELESS CHUYÊN CUNG CẤP – PHÂN PHỐI ĐỒNG HỒ CHÍNH HÃNG NHẬP KHẨU TỪ CHÂU ÂU</p>
            <p><i class="fa fa-envelope"></i> cskh@timeless.com</p>
            <p class="copyright">Bản quyền 2026</p>
        </div>
        <div class="footer-right">
            <div class="footer-column">
                <h4>VỀ CHÚNG TÔI</h4>
                <ul><li><a href="../index.php">Trang chủ</a></li><li><a href="../contact.php">Liên hệ</a></li><li><a href="../explore.php">Kênh truyền thông lớn nhất</a></li></ul>
            </div>
            <div class="footer-column">
            <h4>CHÍNH SÁCH KHÁCH HÀNG</h4>
            <ul>
                <li><a href="../chinh_sach.php?type=trahang">Chính sách đổi trả hàng</a></li>
                <li><a href="../chinh_sach.php?type=baohanh">Chính sách bảo hành sản phẩm</a></li>
                <li><a href="../chinh_sach.php?type=vanchuyen">Chính sách vận chuyển</a></li>
                <li><a href="../chinh_sach.php?type=dieukhoan">Điều khoản sử dụng</a></li>
                <li><a href="../chinh_sach.php?type=thanhtoan">Chính sách thanh toán</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>KHÁM PHÁ THƯƠNG HIỆU</h4>
            <ul>
                <li><a href="../all_rolex.php">Rolex</a></li>
                <li><a href="../all_omega.php">Omega</a></li>
                <li><a href="../all_casio.php">Casio</a></li>
                <li><a href="../all_seiko.php">Seiko</a></li>
                <li><a href="../all_hublot.php">Hublot</a></li>
            </ul>
        </div>
        </div>
    </footer>
    <div class="sticky-product-bar">
        <div class="sticky-bar-content">
            <div class="sticky-info">
                <img src="../<?php echo $row['anh_san_pham']; ?>" alt="Hublot" onerror="this.src='../image/logo.png'">
                <h4 class="sticky-title"><?php echo $row['ten_san_pham']; ?></h4>
            </div>
            <div class="sticky-price-box">
                <?php if (!empty($row['gia_cu']) && $row['gia_cu'] != $row['gia_ban']): ?>
                    <span class="sticky-old-price"><?php echo $gia_cu; ?></span>
                <?php endif; ?>
                <span class="sticky-new-price"><?php echo $gia_ban; ?></span>
            </div>
<div class="sticky-buttons">
    <?php if ($row['ton_kho'] > 0): ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn-sticky-buy" onclick="window.location.href='../cart.php?action=buynow&id=<?php echo $row['id']; ?>'">MUA NGAY</button>
            <button type="button" onclick="addToCartSilent(<?php echo $row['id']; ?>)" class="btn-sticky-add">THÊM VÀO GIỎ</button>
        <?php else: ?>
            <button class="btn-sticky-buy" onclick="requireLogin()">MUA NGAY</button>
            <button type="button" onclick="requireLogin()" class="btn-sticky-add">THÊM VÀO GIỎ</button>
        <?php endif; ?>
    <?php else: ?>
        <button class="btn-sticky-buy" style="background: #ccc;" disabled>HẾT HÀNG</button>
        <button class="btn-sticky-add" style="background: #eee; color: #999;" disabled>HẾT HÀNG</button>
    <?php endif; ?>
</div>
        </div>
    </div>

    <script>
        function toggleFav(productId) {
            fetch('../action_yeuthich.php?action=toggle&id=' + productId)
            .then(res => res.text())
            .then(data => {
                if(data.trim() === 'not_logged_in') {
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Vui lòng đăng nhập để lưu sản phẩm!', 'fa-triangle-exclamation', '#d9534f');
                    setTimeout(() => { window.location.href = '../login.php'; }, 2000);
                } else if(data.trim() === 'added') {
                    document.getElementById('fav-btn').innerHTML = '<i class="fa-solid fa-heart" style="color: #d9534f;"></i> <span style="color: #d9534f; font-weight: bold;">Đã thích</span>';
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã lưu vào danh sách yêu thích!', 'fa-heart', '#d9534f');
                } else if(data.trim() === 'removed') {
                    document.getElementById('fav-btn').innerHTML = '<i class="fa-regular fa-heart"></i> Yêu thích';
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã bỏ thích sản phẩm!', 'fa-heart-crack', '#888');
                }
            });
        }

        function showEditBox(id) {
            document.getElementById('text-' + id).style.display = 'none';
            document.getElementById('edit-box-' + id).style.display = 'block';
        }

        function hideEditBox(id) {
            document.getElementById('edit-box-' + id).style.display = 'none';
            document.getElementById('text-' + id).style.display = 'block';
        }

        function saveEdit(id) {
            let newContent = document.getElementById('edit-input-' + id).value.trim();
            if(newContent === '') { alert('Nội dung không được để trống!'); return; }

            let formData = new URLSearchParams();
            formData.append('action', 'edit');
            formData.append('comment_id', id);
            formData.append('noi_dung_moi', newContent);

            fetch('../action_binhluan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if(data !== 'error') {
                    document.getElementById('text-' + id).innerHTML = data.replace(/\n/g, '<br>');
                    hideEditBox(id);
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã cập nhật bình luận!', 'fa-check', '#28a745');
                } else {
                    alert('Lỗi: Bạn không có quyền sửa bình luận này!');
                }
            });
        }

        function deleteComment(id) {
            document.getElementById('temp_del_id').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function processDelete() {
            let id = document.getElementById('temp_del_id').value;
            let formData = new URLSearchParams();
            formData.append('action', 'delete');
            formData.append('comment_id', id);

            fetch('../action_binhluan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if(data.trim() === 'success') {
                    document.getElementById('deleteModal').style.display = 'none';
                    let commentItem = document.getElementById('comment-' + id);
                    commentItem.style.transition = "opacity 0.5s ease";
                    commentItem.style.opacity = "0";
                    setTimeout(() => { commentItem.remove(); }, 500);
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã xóa bình luận!', 'fa-trash', '#888');
                } else {
                    alert('Lỗi: Không thể xóa!');
                }
            });
        }

        function requireLogin() {
            if (typeof showGlassPrismToast === "function") showGlassPrismToast('Vui lòng đăng nhập để mua hàng!', 'fa-triangle-exclamation', '#d9534f');
            setTimeout(() => { window.location.href = '../login.php'; }, 2000);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const originalBtnGroup = document.querySelector('.btn-group'); 
            const stickyBar = document.querySelector('.sticky-product-bar'); 

            window.addEventListener('scroll', function() {
                if (!originalBtnGroup) return;
                const rect = originalBtnGroup.getBoundingClientRect();
                if (rect.bottom < 0) {
                    stickyBar.classList.add('show'); 
                } else {
                    stickyBar.classList.remove('show'); 
                }
            });
        });

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
    
    <?php include '../thongbao.php'; ?>
</body>
</html>