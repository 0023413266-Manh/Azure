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
                // 2. Định nghĩa danh sách ảnh phụ cho từng sản phẩm (Dựa trên ID trong Database)
                // Bạn chỉ cần thêm ID và danh sách ảnh tương ứng vào đây
                $gallery = [
                    33 => [ 
                        'casio1-1.png', 'casio1-2.png', 'casio1-3.png', 'casio1-4.png', 'casio1-anpham.png',
                    ],
                    34 => [ 
                        'casio2-1.png', 'casio2-2.png', 'casio2-3.png', 'casio2-4.png', 'casio2-anpham.png'
                    ],
                    35 => [ // Sản phẩm ID = 3
                        'casio3-1.png', 'casio3-2.png', 'casio3-3.png', 'casio3-4.png', 'casio3-anpham.png'
                    ],
                    36 => [ // Sản phẩm ID = 3
                        'casio4-1.png', 'casio4-2.png', 'casio4-3.png', 'casio4-4.png', 'casio4-anpham.png'
                    ],
                    37 => [ // Sản phẩm ID = 3
                        'casio5-1.png', 'casio5-2.png', 'casio5-3.png', 'casio5-4.png', 'casio5-anpham.png'
                    ],
                    38 => [ // Sản phẩm ID = 3
                        'casio6-1.png', 'casio6-2.png', 'casio6-3.png', 'casio6-4.png', 'casio6-anpham.png'
                    ],
                    39 => [ // Sản phẩm ID = 3
                        'casio7-1.png', 'casio7-2.png', 'casio7-3.png', 'casio7-4.png', 'casio3-anpham.png'
                    ],
                    40 => [ // Sản phẩm ID = 3
                       'casio8-1.png', 'casio8-2.png', 'casio8-3.png', 'casio8-4.png', 'casio8-anpham.png'
                    ],
                ];

                // 3. Lấy ID của sản phẩm hiện tại
                $current_id = $row['id'];

                // 4. Kiểm tra xem ID này có trong danh sách gallery không
                if (isset($gallery[$current_id])) {
                    foreach ($gallery[$current_id] as $index => $file_name) {
                        // $index + 1 vì ảnh chính đã là số 0 rồi
                        echo '<img src="../image/chitiet_casio/' . $file_name . '" class="thumb" onclick="changeImage(' . ($index + 1) . ')" alt="Ảnh chi tiết">';
                    }
                }
                ?>
            </div>
            

            <?php
            // 1. Tạo danh sách nội dung cho từng dòng sản phẩm
            $content_map = [
                //1
                'Casio 49.7mm Nam MTG-B2000XD-1ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => ' ĐỒNG HỒ CASIO 49.7mm  MTG-B2000XD-1ADR được tối ưu hóa qua ô lịch ngày độc lập tại vị trí 4 giờ và lịch thứ tích hợp tại mặt số phụ, tất cả đều được vận hành bởi hệ thống lịch hoàn toàn tự động đến tận năm 2099.',
                    'color' => '#0c6a3f'
                ],
                //2
                'Casio 49.7mm Nam MTG-B2000YBD-1ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => ' Hệ thống lịch của MTG-B2000YBD được thiết kế để hiển thị trực quan với ô lịch ngày tại góc 4 giờ và lịch thứ tích hợp tinh tế trên mặt số phụ. Nhờ công nghệ Bluetooth kết nối với điện thoại, đồng hồ tự động cập nhật ngày tháng chính xác đến từng giây và điều chỉnh lịch hoàn toàn tự độngđến năm 2099.',
                    'color' => '#000'
                ],
                //3
                'Casio 50.9mm Nam MTG-B3000FR-1ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => ' Hệ thống hiển thị của MTG-B3000FR được thiết kế thông minh với ô lịch ngày đặt tại vị trí 4 giờ và kim chỉ thứ trong tuần tại mặt số phụ lớn. Nhờ tính năng lịch hoàn toàn tự động đến năm 2099 và khả năng tự động đồng bộ thời gian qua Bluetooth, đồng hồ luôn hiển thị ngày tháng chính xác.',
                    'color' => '#a88d34'
                ],
                //4
                'Casio 51mm Nam MTG-B2000D-1ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => ' ĐỒNG HỒ CASIO 51mm  MTG-B2000D-1ADR được tối ưu hóa qua ô lịch ngày độc lập tại vị trí 4 giờ và lịch thứ tích hợp tại mặt số phụ, tất cả đều được vận hành bởi hệ thống lịch hoàn toàn tự động đến tận năm 2099. ',
                    'color' => '#6b1823'
                ],
                //5
                'Casio 46.6mm Nam GWR-B1000-1A1DR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => ' Mặt số được thiết kế khoa học với ô hiển thị ngày tại vị trí 3 giờ và kim chỉ chế độ/thứ tại mặt số phụ. Nhờ công nghệ kết nối Bluetooth và Multiband 6, đồng hồ tự động cập nhật lịch hoàn toàn chính xác đến năm 2099, cho phép bạn theo dõi ngày tháng một cách nhanh chóng mà không cần bất kỳ thao tác điều chỉnh thủ công nào.',
                    'color' => '#602a2e'
                ],
                //6
                ' Casio 52.4mm Nam PRX-8000T-7ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hệ thống hiển thị ngày được tích hợp thông minh qua màn hình LCD độ tương phản cao tại góc 6 giờ. Nhờ công nghệ lịch tự động và đồng bộ sóng vô tuyến, ngày tháng luôn được cập nhật chính xác tuyệt đối mà không cần điều chỉnh thủ công.',
                    'color' => '#5f4a05'
                ],
                //7
                ' Casio 55mm Nam GBD-H1000-1A4DR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hệ thống hiển thị ngày được tích hợp thông minh qua màn hình LCD độ tương phản cao tại góc 6 giờ. Nhờ công nghệ lịch tự động và đồng bộ sóng vô tuyến, ngày tháng luôn được cập nhật chính xác tuyệt đối mà không cần điều chỉnh thủ công.',
                    'color' => '#15844b'
                ],
                //8
                ' Casio 53.8mm Nam GST-B100XA-1ADR' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Hệ thống lịch ngày được tích hợp tinh tế tại vị trí 4 giờ trên mặt số. Nhờ kết nối Bluetooth đồng bộ với điện thoại, lịch hoàn toàn tự động cập nhật chính xác đến năm 2099 mà không cần điều chỉnh thủ công.',
                    'color' => '#245f8c'
                ],
                
                
            ];

            // 2. Tìm xem tên sản phẩm hiện tại thuộc dòng nào trong danh sách trên
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
        // 1. Định nghĩa ảnh phụ cho từng sản phẩm theo ID (Dễ quản lý nhất)
        $gallery_data = [
            33 => [  'casio1-1.png', 'casio1-2.png', 'casio1-3.png', 'casio1-4.png', 'casio1-anpham.png'],
            34 => [ 'casio2-1.png', 'casio2-2.png', 'casio2-3.png', 'casio2-4.png', 'casio2-anpham.png'],
            35 => [ 'casio3-1.png', 'casio3-2.png', 'casio3-3.png', 'casio3-4.png', 'casio3-anpham.png'],
             36 => [ 'casio4-1.png', 'casio4-2.png', 'casio4-3.png', 'casio4-4.png', 'casio4-anpham.png'],
              37 => [ 'casio5-1.png', 'casio5-2.png', 'casio5-3.png', 'casio5-4.png', 'casio5-anpham.png'],
               38 => ['casio6-1.png', 'casio6-2.png', 'casio6-3.png', 'casio6-4.png', 'casio6-anpham.png'],
                39 => [ 'casio7-1.png', 'casio7-2.png', 'casio7-3.png', 'casio7-4.png', 'casio3-anpham.png'],
                 40 => [ 'casio8-1.png', 'casio8-2.png', 'casio8-3.png', 'casio8-4.png', 'casio8-anpham.png'],
            // Bạn cứ thêm 4 => [...], 5 => [...] cho đến 12 ở đây
        ];

        // 2. Lấy ID hiện tại
        $current_id = $row['id'];
        $folder = ($row['id_thuong_hieu'] == 4) ? "chitiet_casio" : "chitiet_rolex";

        // 3. Tạo mảng ảnh hoàn chỉnh
        $js_images = ["../" . $row['anh_san_pham']]; // Ảnh chính luôn ở đầu (index 0)

        if (isset($gallery_data[$current_id])) {
            foreach ($gallery_data[$current_id] as $file) {
                $js_images[] = "../image/$folder/$file";
            }
        }
        ?>
        <script> 
            // PHP tự động truyền mảng ảnh đã lọc vào đây
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
                
                // Cập nhật trạng thái active cho ảnh nhỏ
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
                <a href="../all_rolex.php" style="text-decoration: none; color: #666;">Casio</a> 
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

            <?php if (!empty($row['gia_cu'])): ?>
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
                <p>
                    <i class="fa-solid fa-box-open"></i>
                    <strong>Tình trạng:</strong> Mới 100%, Fullbox, đầy đủ hộp, sổ, thẻ bảo hành.
                </p>
                <p>
                    <i class="fa-solid fa-shield-halved"></i>
                    <strong>Bảo hành:</strong> 2 năm quốc tế Seiko & Bảo hành trọn đời tại Timeless.
                </p>
                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <strong>Địa điểm:</strong> Bảo hiểm hàng hóa 100% & Miễn phí vận chuyển toàn quốc.
                </p>
            </div>



                <?php
                    // Định nghĩa thông số cho từng sản phẩm dựa trên ID trong Database
                    $all_specs = [
                        33 => [ // ID = 1 (Rolex Datejust 31)
                            'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '49.7 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Kim Loại',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        34 => [ // ID = 2 (Rolex Submariner 41)
                          'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '49.7 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Kim Loại',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        35 => [ // ID = 2 (Rolex Submariner 41)
                            'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '50.9 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Nhựa phối kim loại',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        36 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '49.7 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Kim Loại',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        37 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '46.6 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Dây Nhựa',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        38 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '52.4 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Dây Thép',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        39 => [ // ID = 2 (Rolex Submariner 41)
                         'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '55 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Dây Nhựa',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        40 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Năng lượng ánh sáng',
                            'Kính' => 'Kính Sapphire',
                            'Đường kính' => '53.8 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => ' Kim Loại',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        
                    ];

                    // Lấy ID của sản phẩm hiện tại từ database
                    $current_id = $row['id']; 

                    // Lấy bộ thông số tương ứng, nếu không có ID trong mảng thì để trống
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
$type = $row['id']; // mỗi sản phẩm là 1 type riêng
?>


<?php
$allData = [

    // SP 1
    33 => [
        "bg" => "#e9f2ae",
        "items" => [
            [
                "h3" => "MẶT SỐ SỢI CARBON ĐA CHIỀU",
                "h2" => "Sự giao thoa giữa sức mạnh và công nghệ",
                "content" => [
                    "Mặt số của MTG-B2000XD sở hữu cấu trúc đa lớp với các chi tiết được hoàn thiện tỉ mỉ, mang lại chiều sâu thị giác ấn tượng. Với tông màu đen chủ đạo kết hợp cùng các điểm nhấn kim loại, thiết kế này kế thừa DNA bền bỉ của dòng G-Shock nhưng được nâng tầm lên một tiêu chuẩn sang trọng mới.",
                    "Việc ứng dụng các lớp sợi carbon không chỉ giúp giảm trọng lượng mà còn tạo nên hiệu ứng thị giác độc đáo dưới các góc sáng khác nhau. Từng vạch số và kim đồng hồ được cắt gọt sắc sảo, phủ lớp phản quang Neobrite, đảm bảo khả năng đọc giờ hoàn hảo trong mọi điều kiện ánh sáng."
                ],
                "img" => "../image/chitiet_casio/casio1-1.png"
            ],
            [
                "h3" => "VÀNH BEZEL CARBON TÍCH HỢP",
                "h2" => "Tuyệt tác từ vật liệu tiên tiến",
                "content" => [
                    "Điểm nhấn nổi bật nhất của phiên bản này chính là vành bezel được chế tác từ sự kết hợp giữa sợi carbon và sợi thủy tinh nhiều lớp. Các nghệ nhân tại Yamagata Japan đã sử dụng kỹ thuật cắt lớp đặc biệt để lộ ra các vân carbon tinh tế, khiến mỗi chiếc đồng hồ đều có một diện mạo độc nhất.",
                    "Bên cạnh vẻ thẩm mỹ, cấu trúc này còn giúp:
Tối ưu trọng lượng: Nhẹ hơn đáng kể so với vành thép truyền thống.
Chống va đập vượt trội: Cấu trúc Carbon Core Guard bảo vệ tuyệt đối bộ máy bên trong.
Độ bền bỉ: Khả năng chống trầy xước và ăn mòn từ môi trường khắc nghiệt."
                ],
                "img" => "../image/chitiet_casio/casio1-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Sử dụng cấu trúc Dual Core Guard, Casio kết hợp lớp vỏ nhựa gia cố carbon với các thành phần kim loại sáng bóng. Toàn bộ bề mặt thép được xử lý bằng kỹ thuật đánh bóng Zaratsu thủ công – kỹ thuật thường chỉ thấy trên các dòng đồng hồ cao cấp nhất của Nhật Bản, tạo ra bề mặt gương không tì vết.",
                    "Sự tỉ mỉ này mở rộng đến từng chiếc ốc vít và nút bấm, tất cả đều được kiểm định nghiêm ngặt trong môi trường phòng thí nghiệm để đảm bảo khả năng chống rung động và va chạm mạnh (Triple G Resist)."
                ],
                "img" => "../image/chitiet_casio/casio1-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo của MTG-B2000XD là sự kết hợp thông minh giữa các mắt xích kim loại bên ngoài và lớp nhựa cao cấp ở mặt trong. Thiết kế này mang lại vẻ ngoài lịch lãm của một chiếc đồng hồ kim loại nhưng vẫn giữ được sự nhẹ nhàng, êm ái và không gây cảm giác lạnh da khi đeo.",
                    "Hệ thống chốt gập ba lớp cùng cơ chế tháo lắp nhanh cho phép người dùng dễ dàng thay đổi phong cách, phản ánh sự linh hoạt và hiện đại của chủ nhân."
                ],
                "img" => "../image/chitiet_casio/casio1-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG ĐỒNG HỒ CASIO MTG-B2000XD-1ADR",
                "h2" => "Trái tim bền bỉ và kết nối thông minh",
                "content" => [
                    "Linh hồn của MTG-B2000XD là bộ máy Tough Solar mạnh mẽ, tự động sạc năng lượng từ bất kỳ nguồn sáng nào. Đồng hồ không chỉ duy trì độ chính xác cực cao nhờ kết nối Bluetooth® với điện thoại thông minh mà còn nhận tín hiệu sóng vô tuyến (Multiband 6) để cập nhật giờ thế giới tự động.",
                    "- Kết nối Smartphone: Thiết lập giờ thế giới cho hơn 300 thành phố, tìm điện thoại và tự động điều chỉnh thời gian.",
                    "- Độ bền vượt trội: Khả năng chống nước ở độ sâu 200 mét (20 bar), phù hợp cho mọi hoạt động từ đời thường đến thể thao mạo hiểm.",
                    "- Mặt kính Sapphire: Lớp kính sapphire với lớp phủ chống phản quang giúp chống trầy xước tuyệt đối và tăng cường độ trong suốt.",
                    "- Đèn LED cực tím (Super Illuminator): Chiếu sáng cường độ cao giúp quan sát rõ ràng toàn bộ mặt số trong đêm tối.",
                    "Với chứng nhận chế tác tại Nhật Bản (Made in Japan), MTG-B2000XD-1ADR không chỉ là một công cụ xem giờ mà còn là biểu tượng của công nghệ tiên tiến và phong cách sống mạnh mẽ, đẳng cấp."
                ],
                "img" => "../image/sp1-casio.png"
            ]
        ]
    ],

    // SP 2
    34 => [
        "bg" => "#cfb1b1",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "MTG-B2000YBD sở hữu cấu trúc khung vỏ mới lạ được tạo thành từ các lớp sợi carbon và sợi thủy tinh xếp chồng lên nhau, giúp giảm 77% trọng lượng so với thép nhưng vẫn giữ được độ cứng cáp tuyệt vời. Thiết kế này không chỉ mang lại vẻ ngoài hiện đại, đậm chất thể thao mà còn là minh chứng cho trình độ chế tác bậc thầy của Casio tại nhà máy Yamagata."
                ],
                "img" => "../image/chitiet_casio/casio2-1.png"
            ],
            [
                "h3" => "VÀNH BEZEL VÀ DÂY ĐEO LÕI TỔNG HỢP",
                "h2" => "Sự giao thoa giữa độ bền và sự tinh tế",
                "content" => [
                    "Mẫu đồng hồ này sử dụng dây đeo Layered Composite, kết hợp giữa các mắt xích kim loại chắc chắn bên ngoài và nhựa cao cấp ở mặt trong, tạo cảm giác nhẹ nhàng và thoải mái khi đeo suốt ngày dài. Vành bezel được mạ ion (IP) màu đen sang trọng, kết hợp cùng các chi tiết đỏ đặc trưng, tạo nên một diện mạo mạnh mẽ và đẳng cấp cho nam giới."
                ],
                "img" => "../image/chitiet_casio/casio2-2.png"
            ],
            [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Mẫu MTG-B2000YBD là minh chứng cho sự kết hợp hoàn hảo giữa độ cứng cáp của kim loại và tính linh hoạt của nhựa cao cấp. Các kỹ sư tại Casio đã khéo léo sử dụng thép không gỉ cho các bộ phận chịu lực chính, kết hợp với các thành phần nhựa gia cố để tối ưu hóa trọng lượng mà vẫn đảm bảo độ bền vĩnh cửu theo thời gian."
                ],
                "img" => "../image/chitiet_casio/casio2-3.png"
            ],
            [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo của MTG-B2000YBD là một kiệt tác về kỹ thuật công thái học, sử dụng cấu trúc lõi tổng hợp tiên tiến. Các mắt xích bên ngoài được chế tác từ thép không gỉ mạ ion đen (IP) mang lại vẻ ngoài lịch lãm và kiên cố, trong khi mặt trong được lót bằng các lớp nhựa cao cấp có trọng lượng nhẹ."
                ],
                "img" => "../image/chitiet_casio/casio2-4.png"
            ],
            [
                "h3" => "TÍNH NĂNG VƯỢT TRỘI MTG-B2000YBD-1ADR",
                "h2" => "Trái tim Tough Solar và Kết nối Thông minh",
                "content" => [
                    "Trái tim Tough Solar và Kết nối Thông minh
                    Được trang bị bộ máy Caliber 5636, chiếc đồng hồ này là sự hội tụ của những công nghệ đỉnh cao nhất từ G-Shock:",
                    "Tough Solar: Hệ thống sạc năng lượng mặt trời giúp đồng hồ hoạt động bền bỉ nhiều năm mà không cần thay pin.",
                    "Triple G Resist: Khả năng chống va đập, chống lực ly tâm và chống rung động vượt trội, bảo vệ bộ máy trong mọi điều kiện khắc nghiệt.",
                    "Mặt kính Sapphire: Chống trầy xước hoàn hảo với lớp phủ chống phản quang, giúp mặt số luôn rõ nét dưới ánh sáng mạnh.",
                    "Chống nước 200m: Thoải mái sử dụng khi đi bơi, đi lặn hoặc tham gia các hoạt động dưới nước chuyên nghiệp.",
                    "Hiển thị Day–Date: Tiện lợi, rõ ràng"
                ],
                "img" => "../image/sp2-casio.png"
            ]
        ]
    ],
    //SP 3
    35 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "MTG-B3000FR sở hữu cấu trúc bảo vệ lõi kép bằng carbon (Carbon Core Guard) giúp tối ưu hóa trọng lượng và tăng cường khả năng chống va đập tuyệt đối. Các lớp sợi carbon được ép nhiệt với nhựa lân quang, tạo nên một bộ khung không chỉ cứng cáp như thép mà còn có khả năng phát sáng độc đáo trong bóng đêm, tượng trưng cho năng lượng vô tận từ vũ trụ."
                ],
                "img" => "../image/chitiet_casio/casio3-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL VÀ DÂY ĐEO LÕI TỔNG HỢP",
                "h2" => "Sự giao thoa giữa độ bền và sự tinh tế",
                "content" => [
                    "Điểm nhấn rực rỡ nhất của phiên bản này là vành bezel đa lớp được chế tác từ sợi carbon và sợi thủy tinh màu sắc, mô phỏng những vệt lửa trên bề mặt mặt trời. Kết hợp với các chi tiết kim loại được mạ ion (IP) màu vàng hồng, chiếc đồng hồ toát lên vẻ ngoài nghệ thuật, tinh xảo nhưng vẫn giữ được sự mạnh mẽ, gai góc đặc trưng của dòng G-Shock cao cấp."
                ],
                "img" => "../image/chitiet_casio/casio3-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Sự kết hợp giữa thép không gỉ cao cấp và nhựa lân quang không chỉ mang lại giá trị thẩm mỹ mà còn là lời khẳng định về độ bền vĩnh cửu. Mọi bề mặt kim loại đều được hoàn thiện bằng kỹ thuật đánh bóng tinh vi, đảm bảo khả năng chống ăn mòn và duy trì vẻ đẹp sáng bóng theo thời gian, bảo vệ bộ máy an toàn trước mọi tác động khắc nghiệt từ môi trường bên ngoài."
                ],
                "img" => "../image/chitiet_casio/casio3-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo của MTG-B3000FR được làm từ vật liệu urethane mềm mại nhưng cực kỳ bền bỉ, kết hợp cùng khóa thép không gỉ chắc chắn. Thiết kế dây đeo chú trọng vào tính công thái học, mang lại cảm giác nhẹ nhàng và ôm sát cổ tay, giúp người dùng thoải mái vận động trong mọi điều kiện thời tiết mà không gây cảm giác khó chịu hay bám dính."
                ],
                "img" => "../image/chitiet_casio/casio3-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG VƯỢT TRỘI MTG-B2000YBD-1ADR",
                "h2" => "Trái tim Tough Solar và Kết nối Thông minh",
                "content" => [
                    "Vượt xa một chiếc đồng hồ thông thường, MTG-B3000FR hội tụ những tinh hoa công nghệ hiện đại nhất của Casio:",
                    "Tough Solar: Hệ thống sạc năng lượng từ ánh sáng, duy trì hoạt động ổn định và bảo vệ môi trường.",
"Smartphone Link: Kết nối Bluetooth giúp thiết lập giờ thế giới, tìm điện thoại và tự động điều chỉnh giờ theo vị trí thực tế.",
"Triple G Resist: Khả năng chống va đập, lực ly tâm và rung động mạnh mẽ.",
"Mặt kính Sapphire: Chống trầy xước tuyệt đối với lớp phủ không phản quang, giúp quan sát mặt số rõ nét dưới mọi góc độ.",
                    "Với chứng nhận chế tác tại Nhật Bản (Made in Japan),không chỉ là một công cụ xem giờ mà còn là biểu tượng của công nghệ tiên tiến và phong cách sống mạnh mẽ, đẳng cấp."
                ],
                "img" => "../image/sp3-casio.png"
            ],
        ]
    ],

     //SP 4
    36 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "MẶT SỐ SỢI CARBON ĐA CHIỀU",
                "h2" => "Sự giao thoa giữa sức mạnh và công nghệ",
                "content" => [
                    "Mặt số của MTG-B2000D sở hữu cấu trúc đa lớp với các chi tiết được hoàn thiện tỉ mỉ, mang lại chiều sâu thị giác ấn tượng. Với tông màu đen chủ đạo kết hợp cùng các điểm nhấn kim loại, thiết kế này kế thừa DNA bền bỉ của dòng G-Shock nhưng được nâng tầm lên một tiêu chuẩn sang trọng mới.",
                    "Việc ứng dụng các lớp sợi carbon không chỉ giúp giảm trọng lượng mà còn tạo nên hiệu ứng thị giác độc đáo dưới các góc sáng khác nhau. Từng vạch số và kim đồng hồ được cắt gọt sắc sảo, phủ lớp phản quang Neobrite, đảm bảo khả năng đọc giờ hoàn hảo trong mọi điều kiện ánh sáng."
                ],
                "img" => "../image/chitiet_casio/casio4-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CARBON TÍCH HỢP",
                "h2" => "Tuyệt tác từ vật liệu tiên tiến",
                "content" => [
                    "Điểm nhấn nổi bật nhất của phiên bản này chính là vành bezel được chế tác từ sự kết hợp giữa sợi carbon và sợi thủy tinh nhiều lớp. Các nghệ nhân tại Yamagata Japan đã sử dụng kỹ thuật cắt lớp đặc biệt để lộ ra các vân carbon tinh tế, khiến mỗi chiếc đồng hồ đều có một diện mạo độc nhất.",
                    "Bên cạnh vẻ thẩm mỹ, cấu trúc này còn giúp:
Tối ưu trọng lượng: Nhẹ hơn đáng kể so với vành thép truyền thống.
Chống va đập vượt trội: Cấu trúc Carbon Core Guard bảo vệ tuyệt đối bộ máy bên trong.
Độ bền bỉ: Khả năng chống trầy xước và ăn mòn từ môi trường khắc nghiệt."
                ],
                "img" => "../image/chitiet_casio/casio4-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    " Sử dụng cấu trúc Dual Core Guard, Casio kết hợp lớp vỏ nhựa gia cố carbon với các thành phần kim loại sáng bóng. Toàn bộ bề mặt thép được xử lý bằng kỹ thuật đánh bóng Zaratsu thủ công – kỹ thuật thường chỉ thấy trên các dòng đồng hồ cao cấp nhất của Nhật Bản, tạo ra bề mặt gương không tì vết.",
                    "Sự tỉ mỉ này mở rộng đến từng chiếc ốc vít và nút bấm, tất cả đều được kiểm định nghiêm ngặt trong môi trường phòng thí nghiệm để đảm bảo khả năng chống rung động và va chạm mạnh (Triple G Resist)."
                ],
                "img" => "../image/chitiet_casio/casio4-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo của MTG-B2000D là sự kết hợp thông minh giữa các mắt xích kim loại bên ngoài và lớp nhựa cao cấp ở mặt trong. Thiết kế này mang lại vẻ ngoài lịch lãm của một chiếc đồng hồ kim loại nhưng vẫn giữ được sự nhẹ nhàng, êm ái và không gây cảm giác lạnh da khi đeo.",
                    "Hệ thống chốt gập ba lớp cùng cơ chế tháo lắp nhanh cho phép người dùng dễ dàng thay đổi phong cách, phản ánh sự linh hoạt và hiện đại của chủ nhân."
                ],
                "img" => "../image/chitiet_casio/casio4-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG ĐỒNG HỒ CASIO MTG-B2000XD-1ADR",
                "h2" => "Trái tim bền bỉ và kết nối thông minh",
                "content" => [
                    "Linh hồn của MTG-B2000D là bộ máy Tough Solar mạnh mẽ, tự động sạc năng lượng từ bất kỳ nguồn sáng nào. Đồng hồ không chỉ duy trì độ chính xác cực cao nhờ kết nối Bluetooth® với điện thoại thông minh mà còn nhận tín hiệu sóng vô tuyến (Multiband 6) để cập nhật giờ thế giới tự động.",
                    "Kết nối Smartphone: Thiết lập giờ thế giới cho hơn 300 thành phố, tìm điện thoại và tự động điều chỉnh thời gian.",
"Độ bền vượt trội: Khả năng chống nước ở độ sâu 200 mét (20 bar), phù hợp cho mọi hoạt động từ đời thường đến thể thao mạo hiểm.",
"Mặt kính Sapphire: Lớp kính sapphire với lớp phủ chống phản quang giúp chống trầy xước tuyệt đối và tăng cường độ trong suốt.",
"Đèn LED cực tím (Super Illuminator): Chiếu sáng cường độ cao giúp quan sát rõ ràng toàn bộ mặt số trong đêm tối.",
                    "Với chứng nhận chế tác tại Nhật Bản (Made in Japan), MTG-B2000XD-1ADR không chỉ là một công cụ xem giờ mà còn là biểu tượng của công nghệ tiên tiến và phong cách sống mạnh mẽ, đẳng cấp."

                ],
                "img" => "../image/sp4-casio.png"
            ],
        ]
    ],

 //SP 5
    37 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "GWR-B1000-1A1DR đánh dấu bước tiến mới với cấu trúc Carbon Monocoque – vỏ đúc nguyên khối từ sợi carbon gia cố nhựa. Thiết kế này không chỉ mang lại trọng lượng siêu nhẹ mà còn sở hữu độ bền bỉ vượt trội, bảo vệ bộ máy trước những điều kiện khắc nghiệt nhất trong môi trường hàng không."
                ],
                "img" => "../image/chitiet_casio/casio5-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CARBON TÍCH HỢP",
                "h2" => "Tuyệt tác từ vật liệu tiên tiến",
                "content" => [
                    "Vành bezel được tạo nên từ 52 lớp tấm carbon ép mỏng, tạo nên vân carbon đặc trưng đầy sang trọng và mạnh mẽ. Kết hợp cùng các nút bấm bằng titan chống ăn mòn, chiếc đồng hồ toát lên vẻ tinh tế của một thiết bị kỹ thuật cao cấp, sẵn sàng cùng bạn chinh phục mọi độ cao."
                ],
                "img" => "../image/chitiet_casio/casio5-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Sử dụng cấu trúc Dual Core Guard, Casio kết hợp lớp vỏ nhựa gia cố carbon với các thành phần kim loại sáng bóng. Toàn bộ bề mặt thép được xử lý bằng kỹ thuật đánh bóng Zaratsu thủ công – kỹ thuật thường chỉ thấy trên các dòng đồng hồ cao cấp nhất của Nhật Bản, tạo ra bề mặt gương không tì vết.",
                    "Sự tỉ mỉ này mở rộng đến từng chiếc ốc vít và nút bấm, tất cả đều được kiểm định nghiêm ngặt trong môi trường phòng thí nghiệm để đảm bảo khả năng chống rung động và va chạm mạnh (Triple G Resist)."
                ],
                "img" => "../image/chitiet_casio/casio5-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo của phiên bản này được gia cố bằng sợi carbon chèn bên trong nhựa urethane, mang lại độ bền kéo cực cao nhưng vẫn giữ được sự mềm mại. Cấu trúc này giúp dây đeo ôm sát cổ tay một cách êm ái, nhẹ nhàng, giảm thiểu tối đa áp lực khi sử dụng trong thời gian dài."
                ],
                "img" => "../image/chitiet_casio/casio5-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG ĐỒNG HỒ CASIO MTG-B2000XD-1ADR",
                "h2" => "Trái tim bền bỉ và kết nối thông minh",
                "content" => [
                    "Là một trong những mẫu Gravitymaster tiên tiến nhất, GWR-B1000-1A1DR hội tụ đầy đủ các công nghệ đỉnh cao:",
                    "Tough Solar: Hệ thống sạc năng lượng ánh sáng giúp duy trì nguồn năng lượng vô tận.",
"Triple G Resist: Chống va đập, chống lực ly tâm và chống rung động theo tiêu chuẩn quân đội.",
"Mặt kính Sapphire: Chống trầy xước tuyệt đối với lớp phủ chống phản quang, đảm bảo độ trong suốt tối đa.",
"Cấu trúc chống gỉ: Toàn bộ các bộ phận kim loại tiếp xúc bên ngoài đều làm từ titan để ngăn ngừa sự ăn mòn.",
                    "
Với chứng nhận chế tác tại Nhật Bản (Made in Japan),không chỉ là một công cụ xem giờ mà còn là biểu tượng của công nghệ tiên tiến và phong cách sống mạnh mẽ, đẳng cấp."
                ],
                "img" => "../image/chitiet_sp5-casio.png"
            ],
        ]
],

 //SP 6
    38 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "PRX-8000T-7ADR thể hiện sự đẳng cấp thông qua cấu trúc khung vỏ kết hợp giữa Titanium và các vật liệu bền nhẹ cao cấp. Dù không sử dụng hoa văn carbon rực rỡ như các dòng thời trang, nhưng ẩn sau lớp vỏ mỏng nhẹ này là cấu trúc carbon gia cố tại các điểm trọng yếu, giúp đồng hồ chịu được áp suất thay đổi liên tục và va đập mạnh trong những chuyến leo núi khắc nghiệt."
                    ],
                "img" => "../image/chitiet_casio/casio6-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CARBON TÍCH HỢP",
                "h2" => "Tuyệt tác từ vật liệu tiên tiến",
                "content" => [
                    "Vành bezel của PRX-8000T được chế tác từ Titanium siêu cứng với lớp phủ DLC (Diamond Like Carbon) bóng bẩy, mang lại khả năng chống trầy xước gần như tuyệt đối. Dây đeo Titanium nguyên khối không chỉ tạo nên vẻ ngoài sang trọng, tinh tế mà còn đảm bảo sự bền bỉ vĩnh cửu, không bị ăn mòn bởi mồ hôi hay môi trường oxy hóa cao trên đỉnh núi."
                    ],
                "img" => "../image/chitiet_casio/casio6-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Mẫu Manaslu này là sự kết tinh giữa kim loại Titanium quý hiếm và nhựa kỹ thuật cao cấp tại các khớp nối để giảm trọng lượng tối đa. Mọi chi tiết từ nút bấm đến nắp lưng đều được xử lý bề mặt tỉ mỉ, cam kết mang lại một thiết bị đo đạc chuyên nghiệp có tuổi thọ hàng thập kỷ, bất chấp sự tàn phá của thời gian và thời tiết."
                ],
                "img" => "../image/chitiet_casio/casio6-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo Titanium của PRX-8000T được thiết kế theo cấu trúc mắt xích thông minh, cho phép điều chỉnh độ dài linh hoạt để ôm sát cổ tay một cách êm ái. Với đặc tính sinh học của Titanium, dây đeo không gây kích ứng da và có trọng lượng nhẹ hơn 40% so với thép không gỉ, tạo ra sự thoải mái đỉnh cao ngay cả khi bạn phải đeo đồng hồ liên tục trong những hành trình dài ngày."
                ],
                "img" => "../image/chitiet_casio/casio6-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG VƯỢT TRỘI PRX-8000T-7ADR",
                "h2" => "Đỉnh cao công nghệ Triple Sensor và Smart Access",
                "content" => [
                    "Là đại diện ưu tú nhất của dòng Pro Trek, PRX-8000T-7ADR sở hữu một kho tàng công nghệ hỗ trợ sinh tồn và quản lý thời gian chuyên sâu:",
                    "- Bộ ba cảm biến thế hệ thứ 3 (Triple Sensor Ver.3): Cung cấp các thông số đo độ cao, áp suất khí quyển, la bàn số và nhiệt độ với độ chính xác cực cao, giúp bạn dự báo thời tiết và xác định phương hướng trong mọi địa hình.",
                    "- Tough Solar & Multiband 6: Hệ thống sạc năng lượng mặt trời giúp duy trì hoạt động bền bỉ, kết hợp với khả năng nhận tín hiệu hiệu chỉnh thời gian từ 6 trạm phát sóng trên thế giới, đảm bảo giờ giấc luôn chuẩn xác đến từng mili giây.",
                    "- Mặt kính Sapphire phủ lớp chống lóa: Sử dụng kính Sapphire cao cấp nhất giúp chống trầy xước hoàn hảo, kết hợp lớp phủ chống phản quang cho phép bạn đọc chỉ số rõ ràng ngay cả dưới ánh nắng gắt của đỉnh núi.",
                    "- Đèn LED kép (Neon Illuminator): Chiếu sáng mạnh mẽ toàn bộ mặt số và màn hình kỹ thuật số, đảm bảo bạn luôn làm chủ thời gian ngay cả trong bóng đêm mịt mù của hang động hay rừng sâu.",
                    
                ],
                "img" => "../image/sp6-casio.png"
            ],
        ]
],
    //SP 7
    39 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "GBD-H1000-1A4DR sở hữu cấu trúc vỏ tối ưu với sự kết hợp của nhựa cao cấp và khung viền bảo vệ chắc chắn. Thiết kế này kế thừa triết lý từ công nghệ Carbon Monocoque, giúp phân tán lực va đập tối đa, bảo vệ các cảm biến nhạy bén bên trong mà vẫn giữ được trọng lượng lý tưởng cho các hoạt động thể thao cường độ cao."
                ],
                "img" => "../image/chitiet_casio/casio7-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL CARBON TÍCH HỢP",
                "h2" => "Tuyệt tác từ vật liệu tiên tiến",
                "content" => [
                    "Vành bezel được thiết kế gồ ghề, mạnh mẽ với các điểm nhấn màu cam rực rỡ, không chỉ bảo vệ mặt kính khỏi trầy xước mà còn tạo nên phong cách thể thao năng động. Sự kết hợp giữa bề mặt nhám và các chi tiết kim loại tinh xảo ở nút bấm giúp đồng hồ toát lên vẻ hiện đại, sẵn sàng đồng hành cùng bạn trong cả phòng tập lẫn các hoạt động ngoài trời."
                ],
                "img" => "../image/chitiet_casio/casio7-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Sản phẩm là sự hòa quyện giữa các chi tiết thép không gỉ tại nắp lưng, nút bấm và nhựa kỹ thuật siêu bền. Sự kết hợp này cam kết mang lại khả năng chống chịu va đập tuyệt vời và chống thấm mồ hôi hiệu quả, đảm bảo thiết bị luôn bền bỉ vĩnh cửu bất chấp sự khắc nghiệt của quá trình tập luyện hay điều kiện môi trường thay đổi. Bạn sẽ không bao giờ phải lo lắng về việc thay pin định kỳ hay đồng hồ bị dừng nếu không đeo thường xuyên. Chỉ cần tiếp xúc với ánh sáng mặt trời hoặc thậm chí là ánh sáng đèn điện, viên pin bên trong sẽ tự động sạc đầy và có thể duy trì hoạt động chính xác trong vòng 6 tháng ngay cả khi để trong bóng tối hoàn đầu."
                ],
                "img" => "../image/chitiet_casio/casio7-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                    "Dây đeo bằng nhựa urethane mềm dẻo với cấu trúc nhiều lỗ thoát khí giúp cổ tay luôn khô thoáng khi vận động mạnh. Thiết kế cong đặc thù tại phần nối tiếp với vỏ đồng hồ tạo nên sự thoải mái đỉnh cao, ôm sát một cách êm ái, đảm bảo cảm biến đo nhịp tim tiếp xúc chính xác nhất với da mà không gây cảm giác khó chịu."
                ],
                "img" => "../image/chitiet_casio/casio7-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG VƯỢT TRỘI PRX-8000T-7ADR",
                "h2" => "Đỉnh cao công nghệ Triple Sensor và Smart Access",
                "content" => [
                    "GBD-H1000-1A4DR không chỉ là một chiếc đồng hồ, mà là một huấn luyện viên thực thụ trên cổ tay với những tính năng vượt xa mong đợi:",
                    "- Hệ thống 5 Cảm biến thông minh: Bao gồm cảm biến nhịp tim quang học, cảm biến áp suất (độ cao/khí áp), la bàn số, nhiệt kế và gia tốc kế để theo dõi mọi chuyển động và chỉ số sức khỏe của bạn.",
                    "- Tích hợp GPS: Khả năng đo khoảng cách, tốc độ và lộ trình di chuyển với độ chính xác cao, cực kỳ hữu ích cho việc chạy bộ và các hoạt động ngoài trời.",
                    "- Hệ thống Sạc Kép (Dual Charging): Kết hợp giữa sạc USB cho các tính năng tiêu tốn năng lượng (như GPS, nhịp tim) và sạc năng lượng mặt trời (Tough Solar) ",
                    "- Phân tích tập luyện chuyên sâu: Cung cấp dữ liệu về trạng thái tập luyện, mức độ thể lực (VO2max)",
                    "- Kết nối Smartphone Link: Dễ dàng quản lý lịch sử tập luyện, thiết lập kế hoạch đào tạo và nhận thông báo thông minh (tin nhắn, cuộc gọi) ngay trên mặt đồng hồ thông qua ứng dụng G-SHOCK Move."
                   

                ],
                "img" => "../image/sp7-casio.png"
            ],
        ]
],
//SP 8
    40 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ CARBON ĐA LỚP (MULTILAYER CARBON)",
                "h2" => "Đột phá công nghệ vật liệu từ tương lai",
                "content" => [
                    "GST-B100XA-1ADR gây ấn tượng mạnh mẽ với cấu trúc vỏ kết hợp đa lớp, mang lại sự bảo vệ tối ưu cho bộ máy bên trong. Việc ứng dụng công nghệ carbon không chỉ giúp giảm trọng lượng tổng thể mà còn tăng cường khả năng chịu lực, tạo nên một diện mạo hiện đại và đậm chất kỹ thuật số của tương lai."
                ],
                "img" => "../image/chitiet_casio/casio8-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL VÀ DÂY ĐEO LÕI TỔNG HỢP",
                "h2" => "Sự giao thoa giữa độ bền và sự tinh tế",
                "content" => [
                     "Điểm nhấn đắt giá nhất trên phiên bản này chính là vành bezel được chế tác từ sợi carbon Torayca® và nhựa đặc biệt NANOALLOY®, tạo nên hoa văn carbon đặc trưng cực kỳ sang trọng. Sự kết hợp này mang lại khả năng chống trầy xước vượt trội và vẻ ngoài tinh tế, khẳng định đẳng cấp của dòng G-STEEL chuyên biệt."
                ],
                "img" => "../image/chitiet_casio/casio-2.png"
            ],
             [
                "h3" => "CHẾ TÁC TỪ THÉP KHÔNG GỈ VÀ NHỰA CAO CẤP",
                "h2" => "Cam kết về độ bền vĩnh cửu",
                "content" => [
                    "Sản phẩm là sự hòa quyện hoàn hảo giữa thép không gỉ mài bóng và nhựa kỹ thuật siêu bền. Mọi chi tiết từ nút bấm đến nắp lưng đều được gia công tỉ mỉ, cam kết mang lại khả năng chống chịu va đập tuyệt vời theo tiêu chuẩn G-Shock, đảm bảo sự bền bỉ vĩnh cửu bất chấp các tác động khắc nghiệt từ môi trường bên ngoài."
                ], 
                "img" => "../image/chitiet_casio/casio8-3.png"
            ],
             [
                "h3" => "DÂY ĐEO LÕI TỔNG HỢP (LAYERED COMPOSITE)",
                "h2" => "Sự thoải mái đỉnh cao trên cổ tay",
                "content" => [
                     "Dây đeo của GST-B100XA-1ADR được làm từ nhựa urethane cao cấp với họa tiết dập nổi, mang lại độ bền kéo cực cao nhưng vẫn giữ được sự mềm mại. Thiết kế này chú trọng vào tính công thái học, mang đến sự thoải mái đỉnh cao và ôm sát cổ tay một cách chắc chắn trong mọi hoạt động hàng ngày.",
                ],
                "img" => "../image/chitiet_casio/casio8-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG VƯỢT TRỘI PRX-8000T-7ADR",
                "h2" => "Đỉnh cao công nghệ Triple Sensor và Smart Access",
                "content" => [
                    "GST-B100XA-1ADR không chỉ sở hữu vẻ ngoài lịch lãm mà còn được trang bị những công nghệ tiên tiến nhất của Casio.",
"Tough Solar: Hệ thống sạc năng lượng mặt trời độc quyền, chuyển đổi cả ánh sáng yếu từ đèn huỳnh quang thành năng lượng hoạt động, giúp đồng hồ vận hành bền bỉ mà không cần thay pin.",
"Smartphone Link: Kết nối Bluetooth® năng lượng thấp cho phép đồng hồ tự động điều chỉnh thời gian chính xác 4 lần mỗi ngày, thiết lập giờ thế giới cho hơn 300 thành phố và tìm kiếm điện thoại dễ dàng thông qua ứng dụng G-SHOCK Connected.",
"Mặt kính Sapphire: Sử dụng kính Sapphire cao cấp với lớp phủ chống phản quang, mang lại độ trong suốt tuyệt đối và khả năng chống trầy xước hoàn hảo nhất hiện nay.",
"Cấu trúc bảo vệ lớp (Layer Guard Structure): Khung vỏ được thiết kế nhiều lớp giúp phân tán lực va chạm, bảo vệ an toàn cho các linh kiện điện tử bên trong trước mọi cú sốc vật lý.",
"Đèn LED cực tím (Super Illuminator): Chiếu sáng cường độ cao giúp bạn dễ dàng theo dõi thời gian và các chức năng của đồng hồ ngay cả trong điều kiện tối hoàn toàn.",
                    "Đây là sự hợp tác giữa Seiko và PADI (Hiệp hội hướng dẫn lặn biển chuyên nghiệp thế giới). Nếu mẫu SRPE37J trước đó mang vẻ thanh lịch với mặt trắng, thì SRPG21J1 lại là một quái vật biển sâu thực thụ với tông màu xanh đen đặc trưng của đại dương."
 
                ],
                "img" => "../image/sp8-casio.png"
            ],
        ]
],
];
?>

  <?php
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
                    <img src="<?= $item['img'] ?>" 
                         onerror="this.src='../<?= $row['anh_san_pham'] ?>'">
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</section>
<?php endif; ?>




  
</section>
    <?php
        $publicationImages = [
            33 => "../chitiet_casio/casio1-anpham.png",
            34 => "../chitiet_casio/casio2-anpham.png",
            35 => "../chitiet_casio/casio3-anpham.png",
            36 => "../chitiet_casio/casio4-anpham.png",
            37 => "../chitiet_casio/casio5-anpham.png",
            38 => "../chitiet_casio/casio6-anpham.png",
            39 => "../chitiet_casio/casio3-anpham.png",
            40 => "../chitiet_casio/casio8-anpham.png",
            41 => "../chitiet_casio/casio9-anpham.png",
            42 => "../chitiet_casio/casio10-anpham.png",
            43 => "../chitiet_casio/casio11-anpham.png",
            44 => "../chitiet_casio/casio12-anpham.png",
        ];

        // lấy id sản phẩm hiện tại
        $productId = $row['id'];

    // nếu không có thì dùng ảnh mặc định
    $image = $publicationImages[$productId] ?? "default.jpg";
    ?>
        <section class="bottom-info-section">
        <h3 class="cert-title">Chứng nhận</h3>
        <p class="cert-desc">Superlative Chronometer (chứng nhận COSC + Casio sau khi lắp vỏ)</p>

        <h3 class="pub-title" style="text-align: center;">Ấn phẩm</h3>
        <a href="#" class="download-link">
            <i class="fa-solid fa-download"></i> Tải ấn phẩm
        </a>
        
        <img src="../image/chitiet_rolex/<?php echo $image; ?>" 
            style="max-width:300px;" 
            alt="Ấn phẩm Rolex" 
            class="publication-img">
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

        /* CSS cho các nút Hành động (Sửa/Xóa) */
        .comment-actions { margin-top: 8px; font-size: 12px; }
        .comment-actions button { background: none; border: none; cursor: pointer; color: #888; margin-right: 15px; padding: 0; font-family: inherit; transition: 0.3s; }
        .comment-actions button:hover { color: #b58b5a; }
        .comment-actions .btn-delete:hover { color: #d9534f; }
        
        /* CSS cho khung Sửa bình luận */
        .edit-box { display: none; margin-top: 10px; }
        .edit-box textarea { width: 100%; height: 70px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: none; margin-bottom: 5px; font-family: inherit; outline: none; }
        .edit-box button { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; }
        .btn-save-edit { background: #b58b5a; color: white; margin-right: 5px; }
        .btn-cancel-edit { background: #eee; color: #333; }


        /* CSS CHO MODAL XÁC NHẬN XÓA */
        .glass-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); align-items: center; justify-content: center; }
        .glass-modal-content { background: rgba(255, 255, 255, 0.95); border-radius: 12px; width: 350px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .btn-confirm-del { background: #d9534f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px 5px; }
        .btn-cancel-del { background: #eee; color: #333; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px 5px; }
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

    <script>
        // 1. Hiện khung sửa
        function showEditBox(id) {
            document.getElementById('text-' + id).style.display = 'none';
            document.getElementById('edit-box-' + id).style.display = 'block';
        }

        // 2. Ẩn khung sửa
        function hideEditBox(id) {
            document.getElementById('edit-box-' + id).style.display = 'none';
            document.getElementById('text-' + id).style.display = 'block';
        }

        // 3. Xử lý Gửi Sửa bình luận ngầm
        function saveEdit(id) {
            let newContent = document.getElementById('edit-input-' + id).value.trim();
            if(newContent === '') {
                alert('Nội dung không được để trống!');
                return;
            }

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
                    // Cập nhật nội dung hiển thị bằng nội dung mới
                    document.getElementById('text-' + id).innerHTML = data.replace(/\n/g, '<br>');
                    hideEditBox(id);
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã cập nhật bình luận!', 'fa-check', '#28a745');
                } else {
                    alert('Lỗi: Bạn không có quyền sửa bình luận này!');
                }
            });
        }

        // 4. Xử lý Xóa bình luận ngầm
        function deleteComment(id) {
            if(confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
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
                        // Ẩn luôn cái bình luận đó khỏi màn hình bằng hiệu ứng mờ dần
                        let commentItem = document.getElementById('comment-' + id);
                        commentItem.style.transition = "opacity 0.5s ease";
                        commentItem.style.opacity = "0";
                        setTimeout(() => { commentItem.remove(); }, 500);
                        
                        if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã xóa bình luận!', 'fa-trash', '#888');
                    } else {
                        alert('Lỗi: Bạn không có quyền xóa bình luận này!');
                    }
                });
            }
        }
    </script>
    

    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo"><img src="../image/logo.png" alt="Timeless"></div>
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
                <img src="../<?php echo $row['anh_san_pham']; ?>" alt="Rolex Mini">
                <h4 class="sticky-title"><?php echo $row['ten_san_pham']; ?></h4>
            </div>

            <div class="sticky-price-box">
                <?php if (!empty($row['gia_cu'])): ?>
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

    <script>
        // 1. AJAX: THẢ TIM SẢN PHẨM
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

        // 2. AJAX: HIỆN/ẨN KHUNG SỬA BÌNH LUẬN
        function showEditBox(id) {
            document.getElementById('text-' + id).style.display = 'none';
            document.getElementById('edit-box-' + id).style.display = 'block';
        }

        function hideEditBox(id) {
            document.getElementById('edit-box-' + id).style.display = 'none';
            document.getElementById('text-' + id).style.display = 'block';
        }

        // 3. AJAX: LƯU SỬA BÌNH LUẬN
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

        // 4. AJAX: XÓA BÌNH LUẬN BẰNG MODAL LĂNG KÍNH
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
                    document.getElementById('comment-' + id).style.opacity = '0';
                    setTimeout(() => { document.getElementById('comment-' + id).remove(); }, 400);
                    if (typeof showGlassPrismToast === "function") showGlassPrismToast('Đã xóa bình luận!', 'fa-trash', '#888');
                } else {
                    alert('Lỗi: Không thể xóa!');
                }
            });
        }

        // 5. XỬ LÝ CHƯA ĐĂNG NHẬP THÌ CHẶN MUA HÀNG
        function requireLogin() {
            if (typeof showGlassPrismToast === "function") {
                showGlassPrismToast('Vui lòng đăng nhập để mua hàng!', 'fa-triangle-exclamation', '#d9534f');
            } else {
                alert('Vui lòng đăng nhập để mua hàng!');
            }
            setTimeout(() => { window.location.href = '../login.php'; }, 2000);
        }

        // 6. XỬ LÝ HIỆN THANH THANH TOÁN (STICKY BAR) VÀ ẨN HEADER KHI CUỘN
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