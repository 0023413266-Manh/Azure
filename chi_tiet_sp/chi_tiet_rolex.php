<?php
session_start(); // ĐÃ THÊM LỆNH KHỞI ĐỘNG SESSION ĐỂ NHẬN DIỆN KHÁCH HÀNG
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
    
        /* CSS cho hệ thống đánh giá */
        .review-section { max-width: 1200px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
        .review-title { font-size: 20px; border-bottom: 2px solid #b58b5a; padding-bottom: 10px; margin-bottom: 20px; color: #333; font-family: "Playfair Display", serif; }
        .review-summary { display: flex; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
        .review-rating-overview { text-align: center; padding: 20px; background: #faf7f2; border-radius: 8px; min-width: 200px; }
        .review-big-rating { display: flex; align-items: baseline; justify-content: center; gap: 5px; margin-bottom: 10px; }
        .review-score { font-size: 48px; font-weight: bold; color: #b58b5a; }
        .review-out-of { font-size: 18px; color: #888; }
        .review-stars { display: flex; justify-content: center; gap: 5px; margin-bottom: 10px; }
        .review-total { font-size: 14px; color: #666; }
        .review-form { flex: 1; min-width: 300px; }
        .review-stars-input { margin-bottom: 15px; }
        .review-stars-input label { display: block; margin-bottom: 10px; font-weight: bold; color: #333; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 30px; color: #ddd; cursor: pointer; transition: color 0.2s; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label { color: #f39c12; }
        .review-form textarea { width: 100%; height: 100px; padding: 15px; border: 1px solid #ccc; border-radius: 5px; resize: none; font-family: inherit; outline: none; margin-bottom: 15px; }
        .review-form textarea:focus { border-color: #b58b5a; }
        .review-file-upload { margin-bottom: 15px; }
        .review-file-upload label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .review-file-upload input[type="file"] { padding: 10px; border: 1px dashed #ccc; border-radius: 5px; width: 100%; }
        .image-preview { margin-top: 10px; }
        .image-preview img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; }
        .btn-submit-review { background: #b58b5a; color: #fff; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-submit-review:hover { background: #967045; }
        .review-notice { padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .review-notice i { font-size: 20px; }
        .review-notice.notice-info { background: #f0f7ff; color: #0056b3; border: 1px solid #cce5ff; }
        .review-notice.notice-warning { background: #fff5f5; color: #c92a2a; border: 1px solid #ffc9c9; }
        .review-notice.notice-success { background: #f4fbf7; color: #2b8a3e; border: 1px solid #b2f2bb; }
        .review-notice a { color: inherit; font-weight: bold; text-decoration: underline; }
        .review-success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .review-error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .review-list { display: flex; flex-direction: column; gap: 20px; }
        .review-item { display: flex; gap: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .review-item-header { flex: 1; }
        .review-user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .review-user-avatar { width: 40px; height: 40px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #b58b5a; font-size: 18px; }
        .review-user-details { display: flex; flex-direction: column; }
        .review-author { font-weight: bold; color: #333; }
        .review-date { font-size: 12px; color: #888; }
        .review-item-stars { display: flex; gap: 3px; margin-bottom: 10px; }
        .review-item-content { flex: 1; }
        .review-item-content p { color: #555; line-height: 1.6; margin-bottom: 10px; }
        .review-item-image img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; cursor: pointer; transition: transform 0.2s; }
        .review-item-image img:hover { transform: scale(1.05); }
        .review-no-data { text-align: center; padding: 40px; color: #888; }
        .review-no-data i { font-size: 40px; color: #ddd; margin-bottom: 15px; display: block; }
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
                
                <?php if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false): ?>
                    <img src="../image/chitiet_rolex/rolexxanh_mat.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126610lv-rolexxanh_anpham.jpg" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexxanh_chuyen_dong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexxanh_day_deo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexxanh_vanh.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexxanh_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false): ?>
                    <img src="../image/chitiet_rolex/rolexm116518LN_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm116518LN_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm116518LN_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm116518LN_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m116518LN-0041anpham.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm116518LN_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '116503') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex116503_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116503_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116503_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116503_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116503_anpham.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116503_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '52508') !== false): ?>
                    <img src="../image/chitiet_rolex/rolexm52508_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm52508_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm52508_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm52508_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m52508-0006_anpham.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm52508_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex126711_vongso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126711_matso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126711_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126711_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126711chnr-0002_anpham.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126711_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex126598TBR_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126598TBR_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126598TBR_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126598TBR_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126598tbr-0001_anpham.avif" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126598TBR_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '126518LN') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex126518_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126518_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126518_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126518_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126518ln-0014_anpham.avif" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126518_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '126509') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex126509_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126509_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126509_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126509_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126509-0005_anpham.avif" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex126509_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false): ?>
                    <img src="../image/chitiet_rolex/rolexLN_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m124060-0001lv_anpham.jpg" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexLN_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexLN_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexLN_vongso.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexLN_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex124060_vongso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex124060_matso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex124060_daydeo.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126231-0015_anpham.avif" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex124060_duoicung.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex124060_chuyendong.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '136668LB') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex136668LB_vongso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136668LB_matso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136668LB_huyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136668LB_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m136668lb-0001_anpham.jpg" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136668LB_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '136660') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex136660_vongso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136660_matso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136660_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136660_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m136660-0005_rolex_anpham.avif" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex136660_cuoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '116508') !== false): ?>
                    <img src="../image/chitiet_rolex/rolex116508_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116508_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116508_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116508_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m116508-0002anpham.png" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolex116508_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false): ?>
                    <img src="../image/chitiet_rolex/rolexm126535tbr_matso.png" class="thumb" onclick="changeImage(1)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm126535tbr_vongso.png" class="thumb" onclick="changeImage(2)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm126535tbr_chuyendong.png" class="thumb" onclick="changeImage(3)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm126535tbr_daydeo.png" class="thumb" onclick="changeImage(4)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/m126535tbr-0002anpham.avif" class="thumb" onclick="changeImage(5)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
                    <img src="../image/chitiet_rolex/rolexm126535tbr_duoicung.png" class="thumb" onclick="changeImage(6)" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">

                <?php else: ?>
                    <img src="../image/chitiet_rolex/rolex_mat_so.png" class="thumb" onclick="changeImage(1)">
                    <img src="../image/chitiet_rolex/rolex_vang_kim.png" class="thumb" onclick="changeImage(2)">
                    <img src="../image/chitiet_rolex/rolex_day_deo.png" class="thumb" onclick="changeImage(3)">
                    <img src="../image/chitiet_rolex/rolex_chuyen_dong.png" class="thumb" onclick="changeImage(4)">
                    <img src="../image/chitiet_rolex/m278288rbr-anpham.jpg" class="thumb" onclick="changeImage(5)">
                    <img src="../image/chitiet_rolex/m278288rbr-anpham.jpg" class="thumb" onclick="changeImage(6)" style="display:none;">
                <?php endif; ?>
            </div>
            
            <div class="highlight-box" <?php if (strpos($row['ten_san_pham'], 'Submariner') !== false || strpos($row['ten_san_pham'], 'Deepsea') !== false || strpos($row['ten_san_pham'], 'Daytona') !== false) echo 'style="border-left-color: #e5e5e5;"'; ?>>
                <?php if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false): ?>
                    <h4 style="color: #0c6a3f; font-size: 22px;">NIỀM TIN CHINH PHỤC VÙNG NƯỚC SÂU</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Submariner Date bằng thép Oystersteel với miếng đệm vành đồng hồ Cerachrom bằng gốm màu xanh lá và mặt đồng hồ màu đen có vạch dấu giờ phát quang lớn.</p>
                
                <?php elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false): ?>
                    <h4 style="color: #333; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Cosmograph Daytona chế tác từ vàng kim 18 ct với mặt đồng hồ màu trắng và các vòng bộ đếm tương phản. Được phối với dây đeo Oysterflex, tích hợp vành đồng hồ Cerachrom đen và khắc thang đo tachymetric.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '116503') !== false): ?>
                    <h4 style="color: #333; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Oyster Perpetual Cosmograph Daytona chế tác từ vàng kim 18 ct với mặt đồng hồ màu đen và các vòng bộ đếm tương phản. Được phối với dây đeo Oyster, tích hợp vành đồng hồ vàng kim 18 ct khắc thang đo tachymetric.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '52508') !== false): ?>
                    <h4 style="color: #bba271; font-size: 22px;">CHỦ NGHĨA CỔ ĐIỂN TÂN TIẾN</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Perpetual 1908 có vỏ 39 mm bằng vàng kim 18 ct với dây da cá sấu.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false): ?>
                    <h4 style="color: #9b726b; font-size: 22px;">HÒA NHỊP VỚI THẾ GIỚI</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual GMT-Master II chế tác từ thép Oystersteel và vàng Everose với mặt đồng hồ màu đen và dây đeo Oyster.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false): ?>
                    <h4 style="color: #9c7c44; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Oyster Perpetual Cosmograph Daytona này chế tác từ vàng kim 18 ct với mặt đồng hồ màu vàng kim, nạm kim cương và các vòng bộ đếm tương phản, có dây đeo Oyster, tích hợp vành đồng hồ nạm kim cương và có các vấu nạm kim cương.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '126518LN') !== false): ?>
                    <h4 style="color: #c98c56; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Cosmograph Daytona chế tác từ vàng kim 18 ct với mặt đồng hồ màu xanh ngọc lam và các vòng bộ đếm tương phản. Được phối với dây đeo Oysterflex, tích hợp vành đồng hồ Cerachrom đen và khắc thang đo tachymetric.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '126509') !== false): ?>
                    <h4 style="color: #2a5a8e; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Cosmograph Daytona chế tác từ vàng trắng 18 ct với mặt đồng hồ màu xanh dương sáng và các vòng bộ đếm tương phản. Được phối với dây đeo Oysterflex, tích hợp vành đồng hồ Cerachrom đen và khắc thang đo tachymetric.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false): ?>
                    <h4 style="color: #333; font-size: 22px;">SỰ CHUẨN MỰC CỦA ĐỒNG HỒ THỢ LẶN</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Submariner bằng thép Oystersteel với vành đồng hồ Cerachrom bằng gốm màu đen và mặt đồng hồ màu đen có vạch dấu giờ phát quang lớn.</p>
                
                <?php elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false): ?>
                    <h4 style="color: #333;">NỔI BẬT VỚI MẶT SỐ RHODIUM VÀ CỌC SỐ KIM CƯƠNG</h4>
                    <p>Đồng hồ Oyster Perpetual Datejust 36 bằng Rolesor Everose với mặt số màu Rhodium thẫm đính kim cương và dây đeo Jubilee.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '136668LB') !== false): ?>
                    <h4 style="color: #274d82; font-size: 22px;">THÁCH THỨC SỰ BẤT KHẢ THI</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Rolex Deepsea chế tác từ vàng kim 18 ct với miếng đệm vành đồng hồ Cerachrom bằng gốm màu xanh dương và dây đeo Oyster.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '136660') !== false): ?>
                    <h4 style="color: #333; font-size: 22px;">THÁCH THỨC SỰ BẤT KHẢ THI</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Oyster Perpetual Rolex Deepsea chế tác từ thép Oystersteel với miếng đệm vành đồng hồ Cerachrom bằng gốm màu đen và dây đeo Oyster.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '116508') !== false): ?>
                    <h4 style="color: #0c6a3f; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Oyster Perpetual Cosmograph Daytona này chế tác từ vàng kim 18 ct với mặt đồng hồ màu xanh lá cây tươi và vàng kim và dây đeo Oyster, tích hợp vành đồng hồ vàng kim 18 ct và khắc thang đo tachymetric.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false): ?>
                    <h4 style="color: #9c6c6e; font-size: 22px;">VINH QUANG CỦA SỰ BỀN BỈ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Oyster Perpetual Cosmograph Daytona này chế tác từ vàng Everose 18 ct với mặt đồng hồ màu đỏ bụi sundust, nạm kim cương và các vòng bộ đếm tương phản, có dây đeo Oysterflex, tích hợp vành đồng hồ nạm kim cương và có các vấu nạm kim cương.</p>

                <?php else: ?>
                    <h4>NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY</h4>
                    <p>Đồng hồ Oyster Perpetual Datejust 31 bằng vàng kim 18 ct đi kèm mặt số màu đỏ ombré, đính kim cương và dây đeo President.</p>
                <?php endif; ?>
            </div>

            <div class="ref-number">
                Số tham chiếu: <?php echo $row['so_tham_chieu']; ?>
            </div>
        </div>

    <script>
        const images = <?php if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolexxanh_mat.png",
            "../image/chitiet_rolex/m126610lv-rolexxanh_anpham.jpg",
            "../image/chitiet_rolex/rolexxanh_chuyen_dong.png",
            "../image/chitiet_rolex/rolexxanh_day_deo.png",
            "../image/chitiet_rolex/rolexxanh_vanh.png",
            "../image/chitiet_rolex/rolexxanh_duoicung.png"
        ];
        
        <?php elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolexm116518LN_matso.png",
            "../image/chitiet_rolex/rolexm116518LN_vongso.png",
            "../image/chitiet_rolex/rolexm116518LN_chuyendong.png",
            "../image/chitiet_rolex/rolexm116518LN_daydeo.png",
            "../image/chitiet_rolex/m116518LN-0041anpham.png",
            "../image/chitiet_rolex/rolexm116518LN_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '116503') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex116503_matso.png",
            "../image/chitiet_rolex/rolex116503_vongso.png",
            "../image/chitiet_rolex/rolex116503_chuyendong.png",
            "../image/chitiet_rolex/rolex116503_daydeo.png",
            "../image/chitiet_rolex/rolex116503_anpham.png",
            "../image/chitiet_rolex/rolex116503_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '52508') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolexm52508_matso.png",
            "../image/chitiet_rolex/rolexm52508_vongso.png",
            "../image/chitiet_rolex/rolexm52508_chuyendong.png",
            "../image/chitiet_rolex/rolexm52508_daydeo.png",
            "../image/chitiet_rolex/m52508-0006_anpham.png",
            "../image/chitiet_rolex/rolexm52508_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex126711_vongso.png",
            "../image/chitiet_rolex/rolex126711_matso.png",
            "../image/chitiet_rolex/rolex126711_chuyendong.png",
            "../image/chitiet_rolex/rolex126711_daydeo.png",
            "../image/chitiet_rolex/m126711chnr-0002_anpham.png",
            "../image/chitiet_rolex/rolex126711_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex126598TBR_matso.png",
            "../image/chitiet_rolex/rolex126598TBR_vongso.png",
            "../image/chitiet_rolex/rolex126598TBR_chuyendong.png",
            "../image/chitiet_rolex/rolex126598TBR_daydeo.png",
            "../image/chitiet_rolex/m126598tbr-0001_anpham.avif",
            "../image/chitiet_rolex/rolex126598TBR_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '126518LN') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex126518_matso.png",
            "../image/chitiet_rolex/rolex126518_vongso.png",
            "../image/chitiet_rolex/rolex126518_chuyendong.png",
            "../image/chitiet_rolex/rolex126518_daydeo.png",
            "../image/chitiet_rolex/m126518ln-0014_anpham.avif",
            "../image/chitiet_rolex/rolex126518_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '126509') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex126509_matso.png",
            "../image/chitiet_rolex/rolex126509_vongso.png",
            "../image/chitiet_rolex/rolex126509_chuyendong.png",
            "../image/chitiet_rolex/rolex126509_daydeo.png",
            "../image/chitiet_rolex/m126509-0005_anpham.avif",
            "../image/chitiet_rolex/rolex126509_duoicung.png" 
        ];

        <?php elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolexLN_matso.png",
            "../image/chitiet_rolex/m124060-0001lv_anpham.jpg",
            "../image/chitiet_rolex/rolexLN_chuyendong.png",
            "../image/chitiet_rolex/rolexLN_daydeo.png",
            "../image/chitiet_rolex/rolexLN_vongso.png",
            "../image/chitiet_rolex/rolexLN_duoicung.png" 
        ];
        <?php elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex124060_vongso.png",
            "../image/chitiet_rolex/rolex124060_matso.png",
            "../image/chitiet_rolex/rolex124060_daydeo.png",
            "../image/chitiet_rolex/m126231-0015_anpham.avif",
            "../image/chitiet_rolex/rolex124060_duoicung.png",
            "../image/chitiet_rolex/rolex124060_chuyendong.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '136668LB') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex136668LB_vongso.png",
            "../image/chitiet_rolex/rolex136668LB_matso.png",
            "../image/chitiet_rolex/rolex136668LB_huyendong.png",
            "../image/chitiet_rolex/rolex136668LB_daydeo.png",
            "../image/chitiet_rolex/m136668lb-0001_anpham.jpg",
            "../image/chitiet_rolex/rolex136668LB_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '136660') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex136660_vongso.png",
            "../image/chitiet_rolex/rolex136660_matso.png",
            "../image/chitiet_rolex/rolex136660_chuyendong.png",
            "../image/chitiet_rolex/rolex136660_daydeo.png",
            "../image/chitiet_rolex/m136660-0005_rolex_anpham.avif",
            "../image/chitiet_rolex/rolex136660_cuoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '116508') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex116508_matso.png",
            "../image/chitiet_rolex/rolex116508_vongso.png",
            "../image/chitiet_rolex/rolex116508_chuyendong.png",
            "../image/chitiet_rolex/rolex116508_daydeo.png",
            "../image/chitiet_rolex/m116508-0002anpham.png",
            "../image/chitiet_rolex/rolex116508_duoicung.png" 
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolexm126535tbr_matso.png",
            "../image/chitiet_rolex/rolexm126535tbr_vongso.png",
            "../image/chitiet_rolex/rolexm126535tbr_chuyendong.png",
            "../image/chitiet_rolex/rolexm126535tbr_daydeo.png",
            "../image/chitiet_rolex/m126535tbr-0002anpham.avif",
            "../image/chitiet_rolex/rolexm126535tbr_duoicung.png" 
        ];
        <?php else: ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_rolex/rolex_mat_so.png",
            "../image/chitiet_rolex/rolex_vang_kim.png",
            "../image/chitiet_rolex/rolex_day_deo.png",
            "../image/chitiet_rolex/rolex_chuyen_dong.png",
            "../image/chitiet_rolex/m278288rbr-anpham.jpg",
            "../image/chitiet_rolex/m278288rbr-anpham.jpg" 
        ];
        <?php endif; ?>
        
        let currentIndex = 0; 
        const mainImg = document.getElementById('main-product-img');
        const thumbs = document.querySelectorAll('.thumb');

        function changeImage(index) {
            currentIndex = index;
            mainImg.style.opacity = 0.8;
            setTimeout(() => {
                mainImg.src = images[currentIndex];
                mainImg.style.opacity = 1;
            }, 100); 
            thumbs.forEach(thumb => thumb.classList.remove('active'));
            thumbs[currentIndex].classList.add('active');
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
                <a href="../all_rolex.php" style="text-decoration: none; color: #666;">Rolex</a> 
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
                    <strong>Bảo hành:</strong> 5 năm quốc tế Rolex & Bảo hành trọn đời tại Timeless.
                </p>
                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <strong>Địa điểm:</strong> Bảo hiểm hàng hóa 100% & Miễn phí vận chuyển toàn quốc.
                </p>
            </div>

            <h3 style="margin-top: 30px; font-size: 18px;">Thông số kỹ thuật</h3>
            <table class="specs-table">
                <?php if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3235 (Máy In-house thế hệ mới, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, có kính Cyclops phóng đại lịch ngày)</td></tr>
                    <tr><td>Đường kính</td><td>41 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oysterflex (Thép 904L độc quyền, chống ăn mòn cực cao)</td></tr>
                    <tr><td>Độ chịu nước</td><td>30 ATM (300 mét - Đạt chuẩn đồng hồ lặn chuyên nghiệp)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>
                
                <?php elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false): ?>
                    <tr><td>Bộ máy</td><td>Calibre 4130</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước tuyệt hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Oysterflex</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '116503') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4131 (Máy in-house thế hệ mới, dự trữ 72 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước hoàn hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oysterflex (Lõi thép bọc cao su cao cấp)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '52508') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 7140 (Máy in-house thế hệ mới, trữ cót xấp xỉ 66 giờ, dây tóc Syloxi)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, dạng vòm lồi cổ điển)</td></tr>
                    <tr><td>Đường kính</td><td>39 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Da cá sấu (Thường đi kèm khóa đập cài dập hoặc đai khóa vàng kim)</td></tr>
                    <tr><td>Độ chịu nước</td><td>5 ATM (50 mét - Chịu nước mức độ sinh hoạt hàng ngày)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3285 (Máy in-house thế hệ mới, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, có kính Cyclops phóng đại lịch ngày)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oyster (Thép Oystersteel kết hợp vàng hồng Everose 18 ct)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Tắm, bơi lội nhẹ nhàng)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4131 (Máy in-house thế hệ mới, dự trữ 72 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước hoàn hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Vàng kim 18 ct nguyên khối</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '126518LN') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4131 (Máy in-house thế hệ mới, dự trữ 72 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước hoàn hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oysterflex (Lõi kim loại đàn hồi bọc cao su đúc)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '126509') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4131 (Máy in-house thế hệ mới, dự trữ 72 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước hoàn hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oyster (Vàng trắng 18 ct)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3230 (Máy In-house thế hệ mới, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước tuyệt đối)</td></tr>
                    <tr><td>Đường kính</td><td>41 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oyster (Thép Oystersteel 904L nguyên khối)</td></tr>
                    <tr><td>Độ chịu nước</td><td>30 ATM (300 mét - Đạt chuẩn đồng hồ lặn chuyên nghiệp)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3235 (Dây tóc Syloxi, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, có kính Cyclops phóng đại lịch ngày)</td></tr>
                    <tr><td>Đường kính</td><td>36 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Jubilee 5 mối nối (Rolesor Everose - Thép và Vàng 18 ct)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế Rolex</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '136668LB') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3235 (Máy In-house thế hệ mới, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, dày 5.5 mm. Có van thoát khí Helium)</td></tr>
                    <tr><td>Đường kính</td><td>44 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Vàng kim 18 ct</td></tr>
                    <tr><td>Dây đeo</td><td>Dây Oyster (3 mối nối, làm bằng vàng kim 18 ct)</td></tr>
                    <tr><td>Độ chịu nước</td><td>390 ATM (3.900 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '136660') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 3235 (Máy In-house thế hệ mới, trữ cót xấp xỉ 70 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước, dày 5.5 mm. Có van thoát khí Helium)</td></tr>
                    <tr><td>Đường kính</td><td>44 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Thép Oystersteel</td></tr>
                    <tr><td>Dây đeo</td><td>Dây Oyster (3 mối nối, làm bằng thép Oystersteel)</td></tr>
                    <tr><td>Độ chịu nước</td><td>390 ATM (3.900 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '116508') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4130</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước tuyệt hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Vàng kim 18 ct</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false): ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 4131 (Máy in-house thế hệ mới, dự trữ 72 giờ)</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối (Chống trầy xước tuyệt hảo)</td></tr>
                    <tr><td>Đường kính</td><td>40 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây Oysterflex (Thép đàn hồi bọc cao su cao cấp)</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét - Thoải mái đi bơi, lặn nông)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php else: ?>
                    <tr><td>Bộ máy</td><td>Automatic - Calibre 2236</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối</td></tr>
                    <tr><td>Đường kính</td><td>31 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Vàng kim 18 ct</td></tr>
                    <tr><td>Dây đeo</td><td>President, bán nguyệt 3 mảnh</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét)</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>
                <?php endif; ?>
                
            </table>
        </div>
    </div>

    <?php if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false): ?>
    
    <section class="product-story-section" style="background-color: #a3b19b;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ XOAY MỘT CHIỀU</h3>
                    <h2>Công cụ dưới nước</h2>
                    <p>Vành đồng hồ xoay của Submariner là tính năng chủ đạo của đồng hồ. Thiết kế vạch chia 60 phút cho phép thợ lặn theo dõi thời gian lặn và điểm dừng giảm áp một cách chuẩn xác và an toàn.</p>
                    <p>Được chế tác bởi Rolex từ một loại gốm đặc biệt, vành số Cerachrom hầu như không bị trầy xước. Một hộp phát quang tại điểm 0 đảm bảo độ rõ nét bất kể môi trường tối như thế nào. Mép khía của vành đồng hồ được thiết kế cẩn thận để tạo độ bám tuyệt vời dưới nước, ngay cả khi đeo găng tay.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexxanh_vanh.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU ĐEN</h3>
                    <h2>Đảm bảo mức độ dễ đọc dưới nước</h2>
                    <p>Màn hình hiển thị Chromalight là một sự cách tân đã giúp cải thiện khả năng hiển thị trong môi trường tối, đây là một tính năng thiết yếu cho thợ lặn.</p>
                    <p>Các vạch dấu giờ có các hình dạng đơn giản - hình tam giác, hình tròn, hình chữ nhật - kim giờ và kim phút mở rộng cho phép đọc thông số nhanh và đáng tin cậy, ngăn chặn bất kỳ nguy cơ nhầm lẫn nào dưới nước.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexxanh_mat.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THÉP OYSTERSTEEL</h3>
                    <h2>Khả năng chống ăn mòn mạnh mẽ</h2>
                    <p>Rolex sử dụng thép Oystersteel cho vỏ đồng hồ bằng thép. Thép Oystersteel do thương hiệu Rolex phát triển và thuộc dòng thép 904L - loại hợp kim được sử dụng phổ biến nhất trong ngành công nghệ cao, hàng không và hóa chất, đây là những ngành mà độ kháng ăn mòn cực đại là điều thiết yếu.</p>
                    <p>Thép Oystersteel có sức bền ưu việt và có độ sáng bóng tuyệt vời khi được đánh bóng, đồng thời duy trì được vẻ đẹp ngay cả trong những môi trường khắc nghiệt nhất.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexxanh_chuyen_dong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng, tính thẩm mỹ và công nghệ, được thiết kế thỏa mãn tiêu chí bền chắc và thoải mái.</p>
                    <p>Thiết kế được trang bị khóa Oysterlock giúp ngăn chặn việc bung khóa bất ngờ, với thiết kế Glidelock khéo léo, hỗ trợ tùy chỉnh dây đeo không cần dụng cụ - qua đó cho phép dây đeo được sử dụng thoải mái cùng đồ lặn.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexxanh_day_deo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Submariner 41 126610LV 'Starbucks'</h2>
                        <p>Rolex Submariner 126610LV được trang bị bộ máy Caliber 3235 – một trong những bộ máy tự động tiên tiến nhất của Rolex. Bộ máy này mang lại độ chính xác cao với độ lệch chỉ -2/+2 giây mỗi ngày, đồng thời cung cấp trữ lực lên đến 70 giờ.</p>
                        <p>Khả năng chống nước lên đến 300 mét (1,000 feet) làm cho Rolex 126610LV Starbucks trở thành người bạn đồng hành lý tưởng cho các hoạt động lặn và thể thao dưới nước. Hệ thống van thoát khí Helium cho phép đồng hồ hoạt động ổn định trong các điều kiện lặn chuyên nghiệp.</p>
                        <p>Vòng bezel một chiều của Rolex 126610LV-0002 có thể xoay được và được thiết kế để tính toán thời gian lặn một cách chính xác và an toàn. Cơ chế khóa vòng bezel chắc chắn ngăn ngừa việc xoay nhầm trong quá trình sử dụng.</p>
                        <p>Kính sapphire chống xước với lớp phủ chống phản chiếu đảm bảo khả năng đọc giờ tối ưu trong mọi điều kiện ánh sáng. Thiết kế Cyclops lens phóng đại ngày tháng gấp 2.5 lần, giúp việc đọc thông tin ngày tháng trở nên dễ dàng hơn.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex_xanh_duoi_cung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false): ?>
    <section class="product-story-section" style="background-color: #dfceaa;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTERFLEX</h3>
                    <h2>Khả năng chống chịu mạnh mẽ và bền bỉ</h2>
                    <p>Các phiên bản Cosmograph Daytona bằng vàng 18 ct với vành đồng hồ Cerachrom có trên dây đeo Oysterflex. Được phát triển bởi Rolex và đã được cấp bằng sáng chế, dây đeo này kết hợp độc đáo giữa độ bền của một chiếc dây đeo kim loại với sự thoải mái của dây đeo đàn hồi.</p>
                    <p>Nó được làm từ hai lưỡi kim loại cong, linh hoạt - mỗi lưỡi cho một phần dây đeo - được đúc khuôn với vật liệu đàn hồi hiệu suất cao màu đen. Để mang lại sự thoải mái tối ưu, dây đeo Oysterflex được trang bị miếng đệm ở các mặt bên trong và có khóa an toàn Oysterlock để ngăn việc bung khóa bất ngờ. Chiều dài của dây có thể được điều chỉnh thông qua hệ thống nới dây kiểu khóa trượt Rolex khéo léo.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm116518LN_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm116518LN_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU TRẮNG</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp một đồng hồ màu trắng với bộ đếm phun phủ, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm116518LN_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THANG ĐO TACHYMETRIC</h3>
                    <h2>Bộ bấm giờ hiệu năng cao</h2>
                    <p>Một phần quan trọng tạo nên phong cách của dòng sản phẩm là vành đồng hồ đúc thang đo tachymetric để đo tốc độ trung bình lên tới 400 dặm hoặc ki-lô-mét mỗi giờ. Pha trộn giữa công nghệ cao với yếu tố thẩm mỹ tinh tế, vành đồng hồ màu đen là sự gợi nhớ đến mẫu năm 1965 được trang bị một miếng đệm vành đồng hồ Plexiglas màu đen.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm116518LN_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 40 116518LN-0041 Mặt Số Trắng</h2>
                        <p>Đồng Hồ Rolex Cosmograph Daytona 40 116518ln-0041 Mặt Số Trắng Dây Đeo Oysterflex là một mẫu đồng hồ lịch lãm đặc biệt dành cho các tín đồ đam mê phong cách thể thao của Rolex, sở hữu bộ vỏ bền bỉ hàng đầu khi gia công từ Vàng 18k độc quyền với dáng mặt số tròn kích thước 40mm lấy vẻ đẹp tone màu trắng làm chủ đạo, đặc biệt ở vành bezel được thiết kế dải kính xoay 2 chiều mang lại nét đẹp độc đáo cho tổng thể đồng hồ. Ba mặt số phụ ở góc 3 giờ, 6 giờ và 9 giờ được tô điểm bằng đường viền màu vàng độc đáo có nhiệm vụ thực hiện các chức năng Chronograph. Mặt kính chất liệu Sapphire với khả năng chống sốc và chống sấy xước tuyệt vời. Dây đeo cao su Oysterflex tạo sự thanh lịch và mềm mại.</p>
                        <p>Bên cạnh đó, Rolex Cosmograph Daytona 40 116518ln-0041 Mặt Số Trắng Dây Đeo Oysterflex còn được trang bị bộ máy Automatic Calibre 4130 được sản xuất in-house trong nhà máy Rolex có trang bị dây tóc Syloxi hoạt động ổn định với độ chính xác gấp 10 lần dây tóc truyền thống cùng mức dự trữ năng lượng lên đến 72 giờ. Cỗ máy này đã xuất sắc vượt qua bài kiểm tra của tổ chức COSC khi chỉ sai lệch -2/+2 giây một ngày.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm116518LN_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '116503') !== false): ?>
    <section class="product-story-section" style="background-color: #ead088;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao. Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia.</p>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng. Được giới thiệu lần đầu tiên vào cuối những năm 1930, loại dây đeo kim loại bản rộng, đặc biệt bền chắc và thoải mái với ba mảnh dạng phẳng, bản rộng này vẫn giữ vai trò là chiếc dây đeo đồng hồ phổ biến nhất trong bộ sưu tập Oyster.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116503_daydeo.png" style="background-color: #a68a56 !important; box-shadow: none !important; border-radius: 15px !important; border: none !important; outline: none !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>ROLESOR VÀNG</h3>
                    <h2>Cuộc hội ngộ của hai kim loại</h2>
                    <p>Vàng được ưa chuộng bởi sự lấp lánh và sang quý. Thép củng cố độ bền và độ tin cậy. Chúng kết hợp hài hòa các đặc tính tốt nhất của mình với nhau.</p>
                    <p>Đại diện cho một dấu ấn bản sắc của Rolex, Rolesor đã có trong các mẫu đồng hồ Rolex kể từ đầu thập niên 1930, và được đăng ký nhãn hiệu từ năm 1933. Đây là một trong số những điểm đặc trưng nổi bật của bộ sưu tập Oyster Perpetual.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116503_chuyendong.png" style="background-color: #a68a56 !important; box-shadow: none !important; border-radius: 15px !important; border: none !important; outline: none !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU ĐEN</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu đen với bộ đếm phun phủ, có vạch dấu giờ đánh đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116503_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THANG ĐO TACHYMETRIC</h3>
                    <h2>Bộ bấm giờ hiệu năng cao</h2>
                    <p>Với thang đo tachymetric, ba bộ đếm và nút bấm, đồng hồ Cosmograph Daytona được thiết kế để trở thành công cụ thời gian chính xác cho các tay đua bền. Vành đồng hồ đúc thang đo tachymetric để đọc tốc độ trung bình trên một khoảng cách và thời gian nhất định.</p>
                    <p>Thang đo này đảm bảo mức độ dễ đọc tối ưu, giúp đồng hồ Cosmograph Daytona trở thành công cụ lý tưởng để đo tốc độ lên đến 400 đơn vị mỗi giờ, đơn vị tính bằng km hoặc dặm.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116503_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 40 116503-0004 Dây Oyster Thép Vàng Vàng</h2>
                        <p>Đồng Hồ Rolex Cosmograph Daytona 40 116503-0004 Mặt Số Đen Dây Oyster Thép Vàng Vàng là một mẫu đồng hồ lịch lãm đặc biệt dành cho các tín đồ đam mê phong cách thể thao của Rolex, sở hữu bộ vỏ bền bỉ hàng đầu khi gia công từ chất liệu Thép bền bỉ và Vàng 18k độc quyền với dáng mặt số tròn kích thước 40mm lấy vẻ đẹp tone màu đen làm chủ đạo, đặc biệt ở vành bezel được thiết kế dải kính xoay 2 chiều mang lại nét đẹp độc đáo cho tổng thể đồng hồ. Ba mặt số phụ ở góc 3 giờ, 6 giờ và 9 giờ được tô điểm bằng đường viền màu vàng độc đáo có nhiệm vụ thực hiện các chức năng Chronograph. Mặt kính chất liệu Sapphire với khả năng chống sốc và chống sấy xước tuyệt vời. Dây đeo Oyster 3 mối nối đi cùng với khóa gập Oysterlock tạo cảm giác thoải mái và tiện dụng.</p>
                        <p>Bên cạnh đó, Rolex Cosmograph Daytona 40 116503-0004 Mặt Số Đen Dây Oyster Thép Vàng Vàng còn được trang bị bộ máy Automatic Calibre 4130 được sản xuất in-house trong nhà máy Rolex có trang bị dây tóc Syloxi hoạt động ổn định với độ chính xác gấp 10 lần dây tóc truyền thống cùng mức dự trữ năng lượng lên đến 72 giờ. Cỗ máy này đã xuất sắc vượt qua bài kiểm tra của tổ chức COSC khi chỉ sai lệch -2/+2 giây một ngày.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116503_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '52508') !== false): ?>
    <section class="product-story-section" style="background-color: #e4dac4;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU TRẮNG TINH KHIẾT</h3>
                    <h2>Tác phẩm nghệ thuật thu nhỏ</h2>
                    <p>Mặt đồng hồ là một tác phẩm nghệ thuật thu nhỏ. Màu sắc, độ phản chiếu và kết cấu bề mặt, cùng các yếu tố trang trí và thiết kế tổng thể, tất cả mang đến cho mỗi chiếc đồng hồ đặc trưng riêng biệt.</p>
                    <p>Rolex tự hào sở hữu toàn bộ quy trình sáng tạo và sản xuất mặt đồng hồ hoàn mỹ, từ khâu thiết kế đến sản xuất. Từ những bản phác thảo ban đầu cho đến khâu kiểm nghiệm cuối cùng, bao gồm pha màu hoặc định phụ kiện trang trí, quy trình bao gồm các bước và thao tác đòi hỏi chuyên môn cụ thể. Một trong số đó – ví dụ như bước tráng men – đã có lịch sử từ thời kỳ đầu của ngành chế tạo đồng hồ. Một số bước khác còn áp dụng các công nghệ tiên tiến, chẳng hạn như phun xạ Magnetron - một kỹ thuật phức tạp để phủ màu bề mặt cho mặt đồng hồ bằng cách đặt lên một màng phim mỏng trong môi trường chân không.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm52508_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ DÁNG VÒM VÀ CÓ KHÍA</h3>
                    <h2>Vành đồng hồ đôi</h2>
                    <p>Mẫu đồng hồ sang trọng và tinh tế này có vỏ mỏng được vành đồng hồ dáng vòm kết hợp với khía tinh xảo bao xung quanh – phần dưới có khía trang nhã và phần trên có dáng vòm.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm52508_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm52508_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO ĐẲNG CẤP</h3>
                    <h2>Được thiết kế riêng</h2>
                    <p>Phiên bản 1908 này được trang bị dây đeo chất liệu da cá sấu. Dây đeo trang nhã này được thiết kế riêng cho mẫu đồng hồ có lớp lót láng da bê màu xanh lá và mũi khâu đồng màu.</p>
                    <p>Mẫu này được trang bị khóa cài Dualclasp, một kiểu khóa gập kép, chế tác từ vàng kim 18 ct. Nhờ kiểu dáng được thiết kế một cách tỉ mỉ, khóa cài Dualclasp luôn nằm ngay giữa cổ tay.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm52508_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Rolex 1908 52508-0006 Mặt Số Trắng</h2>
                        <p>Rolex 1908 M52508-0006 Mặt Số Trắng không chỉ nổi bật bởi thiết kế đẹp mắt mà còn bởi những tính năng ưu việt. Được trang bị bộ máy cơ tự động Calibre 7140 do chính Rolex phát triển, chiếc đồng hồ này đảm bảo độ chính xác cao và khả năng hoạt động bền bỉ. Bộ máy Calibre 7140 được chứng nhận Chronometer, một chứng chỉ khẳng định chất lượng và độ chính xác của Rolex.</p>
                        <p>Chức năng hiển thị giờ, phút và giây của Rolex 1908 M52508-0006 được thực hiện bởi các kim đồng hồ bằng vàng trắng 18k, mang đến sự hoàn thiện kỹ thuật cao cấp. Kim giây được đặt ở vị trí trung tâm, mang đến sự thuận tiện và dễ dàng theo dõi thời gian.</p>
                        <p>Khả năng chống nước của Rolex 1908 M52508-0006 lên đến 50 mét, giúp người dùng yên tâm sử dụng trong các hoạt động hàng ngày mà không lo lắng về việc tiếp xúc với nước.</p>
                        <p>Rolex 1908 M52508-0006 Mặt Số Trắng là sự kết hợp hoàn hảo giữa phong cách cổ điển và công nghệ chế tác hiện đại. Với thiết kế tinh tế, vật liệu cao cấp và tính năng vượt trội, chiếc đồng hồ này không chỉ là một phụ kiện thời trang mà còn là biểu tượng của sự sang trọng và đẳng cấp.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm52508_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false): ?>
    <section class="product-story-section" style="background-color: #dbcbcc;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ XOAY VẠCH CHIA 24 GIỜ</h3>
                    <h2>Công nghệ cao không ngừng cải tiến</h2>
                    <p>Mẫu đồng hồ này đi kèm mặt số màu đen và miếng đệm vành đồng hồ Cerachrom bằng gốm màu nâu và đen. Ngoài các kim giờ, phút và giây thông thường, phiên bản GMT-Master II được trang bị một kim đầu mũi tên, di chuyển hết một vòng mặt đồng hồ mỗi 24 giờ, cũng như vành đồng hồ chia vạch 24 giờ xoay hai chiều.</p>
                    <p>Kim 24 giờ có màu đặc trưng hiển thị giờ tham chiếu "nhà" ở múi giờ đầu tiên, có thể đọc được trên các vạch chia của vành đồng hồ. Giờ địa phương của khách du lịch dễ dàng được thiết lập bằng cách "nhảy" theo từng giờ, nhờ cơ chế khéo léo được vận hành thông qua núm vặn: kim giờ có thể được điều chỉnh về phía trước hoặc ngược lại một cách độc lập với kim phút và kim giây. Điều này cho phép khách du lịch thích ứng với múi giờ mới của họ mà không ảnh hưởng đến độ chính xác của đồng hồ.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126711_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU ĐEN</h3>
                    <h2>Mức độ dễ đọc cao trong mọi hoàn cảnh</h2>
                    <p>Giống với tất cả các phiên bản đồng hồ Chuyên dụng của Rolex, nhờ màn hình hiển thị Chromalight, GMT-Master II có sự rõ nét đặc biệt trong bất kỳ tình huống nào, đặc biệt là trong môi trường tối.</p>
                    <p>Các kim bản rộng và vạch dấu giờ có dáng đơn giản – hình tam giác, hình tròn, hình chữ nhật – được phủ một loại vật liệu phát quang phát ra ánh sáng bền bỉ.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126711_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>ROLESOR EVEROSE</h3>
                    <h2>Cuộc hội ngộ của hai kim loại</h2>
                    <p>Vàng được ưa chuộng bởi sự lấp lánh và sang quý. Thép củng cố độ bền và độ tin cậy. Chúng kết hợp hài hòa các đặc tính tốt nhất của mình với nhau.</p>
                    <p>Đại diện cho một dấu ấn bản sắc của Rolex, Rolesor đã có trong các mẫu đồng hồ Rolex kể từ đầu thập niên 1930, và được đăng ký nhãn hiệu từ năm 1933. Đây là một trong số những điểm đặc trưng nổi bật của bộ sưu tập Oyster Perpetual.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126711_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO JUBILEE / DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng, tính thẩm mỹ và công nghệ, được thiết kế thỏa mãn tiêu chí bền chắc và thoải mái. Thiết kế được trang bị khóa gập Oysterlock giúp ngăn chặn việc bung khóa bất ngờ và mối dây mở rộng tiện lợi Easylink độc quyền của Rolex.</p>
                    <p>Hệ thống khéo léo này cho phép người đeo tăng chiều dài dây đeo thêm khoảng 5 mm, tạo sự thoải mái trong bất kỳ tình huống nào.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126711_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex GMT-Master II 40 126711CHNR "Rootbeer"</h2>
                        <p>Đồng hồ Rolex GMT Master II 40 126711CHNR "Rootbeer" Mặt Số Đen Dây Oyster sở hữu bộ vỏ được gia công bằng chất liệu Thép 904L không gỉ độc quyền của Rolex kết hợp cùng vàng Hồng 18K, với dáng mặt số tròn kích thước 40mm, đặc biệt ở vành bezel Cerachrom là sự kết hợp hoàn hảo giữa hai tone màu đen và nâu. Ở cửa sổ hiển thị ngày ở góc 3 giờ được trang bị ống kính Cyclops phóng đại. Mặt kính chất liệu Sapphire với khả năng chống sốc và chống xây xước tuyệt vời. Dây đeo Oyster 3 mối nối được mạ vàng Everose ở trung tâm tạo sự thanh lịch và sang trọng.</p>
                        <p>Bên cạnh đó, Rolex GMT Master II 40 126711CHNR "Rootbeer" Mặt Số Đen Dây Oyster còn được trang bị bộ máy Automatic Calibre 3285 có trang bị dây tóc Syloxi hoạt động ổn định với độ chính xác gấp 10 lần dây tóc truyền thống cùng mức dự trữ năng lượng lên đến 70 giờ. Cỗ máy này đã xuất sắc vượt qua bài kiểm tra của tổ chức COSC khi chỉ sai lệch -2/+2 giây một ngày.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126711_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false): ?>
    <section class="product-story-section" style="background-color: #e6e6e6;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ XOAY MỘT CHIỀU</h3>
                    <h2>Công cụ dưới nước</h2>
                    <p>Vành đồng hồ xoay của Submariner là tính năng chủ đạo của đồng hồ. Thiết kế vạch chia 60 phút cho phép thợ lặn theo dõi thời gian lặn và điểm dừng giảm áp một cách chuẩn xác và an toàn.</p>
                    <p>Được chế tác bởi Rolex từ một loại gốm bền chống ăn mòn, vòng số vành Cerachrom hầu như không bị trầy xước. Một hộp phát quang tại điểm 0 đảm bảo độ rõ nét bất kể môi trường tối như thế nào. Mép khía của vành đồng hồ được thiết kế cẩn thận để tạo độ bám tuyệt vời dưới nước, ngay cả khi đeo găng tay.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexLN_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU ĐEN</h3>
                    <h2>Đảm bảo mức độ dễ đọc dưới nước</h2>
                    <p>Màn hình hiển thị Chromalight là một sự cách tân đã giúp cải thiện khả năng hiển thị trong môi trường tối, đây là một tính năng thiết yếu cho thợ lặn.</p>
                    <p>Các vạch dấu giờ có các hình dạng đơn giản – hình tam giác, hình tròn, hình chữ nhật – kim giờ và kim phút mở rộng cho phép đọc thông số nhanh và đáng tin cậy, ngăn chặn bất kỳ nguy cơ nhầm lẫn nào dưới nước.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexLN_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THÉP OYSTERSTEEL</h3>
                    <h2>Khả năng chống ăn mòn mạnh mẽ</h2>
                    <p>Rolex sử dụng thép Oystersteel cho vỏ đồng hồ bằng thép. Thép Oystersteel do thương hiệu Rolex phát triển và thuộc dòng thép 904L - loại hợp kim được sử dụng phổ biến nhất trong ngành công nghệ cao, hàng không và hóa chất, đây là những ngành mà độ kháng ăn mòn cực đại là điều thiết yếu.</p>
                    <p>Thép Oystersteel có sức bền ưu việt và có độ sáng bóng tuyệt vời khi được đánh bóng, đồng thời duy trì được vẻ đẹp ngay cả trong những môi trường khắc nghiệt nhất.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexLN_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng, tính thẩm mỹ và công nghệ, được thiết kế thỏa mãn tiêu chí bền chắc và thoải mái.</p>
                    <p>Thiết kế được trang bị khóa Oysterlock giúp ngăn chặn việc bung khóa bất ngờ, với thiết kế Glidelock khéo léo, hỗ trợ tùy chỉnh dây đeo không cần dụng cụ - qua đó cho phép dây đeo được sử dụng thoải mái cùng đồ lặn.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexLN_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Submariner 41 124060LN Black 124060LN-0001</h2>
                        <p>Rolex 124060LN sở hữu bộ máy Calibre 3230 – một trong những bộ máy tự động tiên tiến nhất của Rolex hiện nay. Bộ máy này được sản xuất hoàn toàn trong nhà máy Rolex với độ chính xác được chứng nhận COSC.</p>
                        <p>Khả năng chống nước đến 300 mét (1000 feet) làm cho Rolex 124060LN Black trở thành người bạn đồng hành lý tưởng cho các hoạt động lặn và thể thao dưới nước. Vỏ Oyster monobloc đặc biệt của Rolex Submariner 41 đảm bảo khả năng chống nước tuyệt đối thông qua hệ thống niêm phong ba lớp.</p>
                        <p>Vành bezel xoay một chiều 60 phút của Rolex Submariner cho phép theo dõi thời gian lặn một cách an toàn và chính xác. Cơ chế click chắc chắn đảm bảo vành bezel không bị xoay nhầm trong quá trình sử dụng. Hệ thống Chromalight độc quyền của Rolex trên Rolex 124060LN phát ra ánh sáng xanh lam sáng gấp đôi so với các vật liệu phát quang thông thường, đảm bảo khả năng đọc thời gian tuyệt vời trong bóng tối. Xứng đáng là lựa chọn hàng đầu cho những ai tìm kiếm sự hoàn hảo trong từng chi tiết.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexLN_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false): ?>
    <section class="product-story-section" style="background-color: #eae1d7;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH KHÍA</h3>
                    <h2>Dấu hiệu đặc trưng của Rolex</h2>
                    <p>Vành khía Rolex là dấu ấn của sự khác biệt. Ban đầu, các khía trên vành đồng hồ Oyster được tạo ra để siết chặt vành đồng hồ với vỏ đồng hồ một cách dễ dàng, nhằm đảm bảo chống thấm nước tuyệt đối cho đồng hồ. Thiết kế tương đồng với khía trên nắp lưng đồng hồ được vặn chặt với vỏ đồng hồ giúp chống thấm nước nhờ công cụ đặc biệt của Rolex. Theo thời gian, thiết kế khía đã trở thành một chi tiết có tính thẩm mỹ cao, xứng đáng là bộ phận nổi bật mang phong cách Rolex. Ngày nay, vành khía được làm bằng vàng và trở thành điểm nhấn riêng trên mẫu Datejust 36 này.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex124060_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU TRẮNG</h3>
                    <h2>Sáng tạo vô hạn</h2>
                    <p>Sơn mài dường như có khả năng sáng tạo vô hạn về màu sắc với cường độ hoàn hảo và tạo hiệu ứng trơn nhẵn. Kỹ thuật sơn mài bao gồm việc phủ liên tiếp sáu lớp sơn mài mỏng lên một phiến đế bằng đồng. Sau đó, một lớp sơn bóng không màu được phủ lên để tạo chiều sâu và độ bóng cho màu sắc và sắc thái của mặt đồng hồ. Sau khi lớp sơn bóng khô, bề mặt của mặt đồng hồ được đánh bóng để làm nổi bật màu sắc; sau đó mặt đồng hồ sẵn sàng để được in chuyển chi tiết dập và đính các phụ kiện.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex124060_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>ROLESOR EVEROSE</h3>
                    <h2>Cuộc hội ngộ của hai kim loại</h2>
                    <p>Vàng được ưa chuộng bởi sự lấp lánh và sang quý. Thép củng cố độ bền và độ tin cậy. Chúng kết hợp hài hòa các đặc tính tốt nhất của mình với nhau.</p>
                    <p>Đại diện cho một dấu ấn bản sắc của Rolex, Rolesor đã có trong các mẫu đồng hồ Rolex kể từ đầu thập niên 1930, và được đăng ký nhãn hiệu từ năm 1933. Đây là một trong số những điểm đặc trưng nổi bật của bộ sưu tập Oyster Perpetual.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex124060_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO JUBILEE</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao.</p>
                    <p>Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia. Dây đeo đồng hồ kim loại Jubilee có thiết kế mềm mại và thoải mái với mối nối năm mảnh và được đặc biệt chế tác cho sự ra mắt của Oyster Perpetual Datejust vào năm 1945.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex124060_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Datejust 36 126231-0015 Roman Rhodium</h2>
                        <p>Không những vậy, Rolex Datejust 36 Mặt Số Roman Rhodium 126231-0015 sở hữu bộ dây đeo Oyster Jubilee 5 mối nối cùng với khóa gập ẩn Crownclasp đã vượt qua các bài kiểm tra nghiêm ngặt mang tính ứng dụng cao, chắc chắn sẽ mang lại sự thoải mái tối ưu cho cổ tay của người đeo.</p>
                        <p>Cuối cùng, hoàn thiện cho kiệt tác Rolex Datejust 36 Mặt Số Roman Rhodium này là bộ chuyển động tự lên dây Calibre 3235, một bộ máy có trang bị dây tóc Syloxi bằng silicon đảm bảo độ chính xác cao và khả năng chống va đập tốt hơn. Được chứng nhận bởi COSC khi chỉ sai lệch -2/+2 giây một ngày và có mức dự trữ năng lượng lên đến 70 giờ, đồng thời là khả năng chống nước ở độ sâu 100m.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex124060_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '136668LB') !== false): ?>
    <section class="product-story-section" style="background-color: #6c91bc;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ CHẤT LIỆU GỐM VÀ MÀN HÌNH HIỂN THỊ PHÁT QUANG</h3>
                    <h2>Tính liên tục của một thiết kế</h2>
                    <p>Vành đồng hồ xoay một chiều có vạch chia 60 phút của Rolex Deepsea cho phép các thợ lặn giám sát số lần lặn và giải nén của họ một cách chính xác và an toàn. Dấu gờ hình tam giác trên vạch chia độ nổi bật trong bóng tối nhờ một dấu hiển thị mang tích hợp cùng loại vật liệu phát quang phát ra ánh sáng xanh dương bền lâu. Vạch chia trên vành đồng hồ được phủ bằng công nghệ PVD (Công nghệ mạ chân không) với một lớp vàng kim mỏng.</p>
                    <p>Màu xanh dương trên vòng nén, mặt đồng hồ và miếng đệm vành Cerachrom kết hợp với nhau để tạo ra bảng màu đại dương tinh tế, nổi bật nhờ phần vỏ chế tác từ vàng kim 18 ct và dây đeo Oyster.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136668LB_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU XANH DƯƠNG</h3>
                    <h2>Đảm bảo mức độ dễ đọc dưới nước</h2>
                    <p>Trên mặt đồng hồ sơn mài màu xanh dương, cái tên ‘DEEPSEA’ được phủ màu vàng. Mặt đồng hồ được trang bị vạch giờ dạ quang Chromalight và kim lớn, phủ vật liệu phát quang phát ra ánh sáng màu xanh dương lâu dài, mang lại khả năng đọc rõ ràng trong điều kiện tối.</p>
                    <p>Các kiểu dáng đơn giản của vạch dấu giờ – hình tam giác, hình tròn và hình chữ nhật – cùng các kim, được phân biệt rõ ràng về kích thước và hình dáng, được làm đầy hoặc phủ vật liệu phát quang, phát ra ánh sáng màu xanh dương lâu dài – lâu hơn gấp hai lần so với vật liệu phát quang truyền thống.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136668LB_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136668LB_huyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng, tính thẩm mỹ và công nghệ, được thiết kế thỏa mãn tiêu chí bền chắc và thoải mái.</p>
                    <p>Thiết kế được trang bị khóa Oysterlock giúp ngăn chặn việc bung khóa bất ngờ, với thiết kế Glidelock khéo léo, hỗ trợ tùy chỉnh dây đeo không cần dụng cụ – qua đó cho phép dây đeo được sử dụng thoải mái cùng đồ lặn.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136668LB_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Deep Sea 44 136668LB</h2>
                        <p>Rolex Deep Sea 44 136668LB-0001 Mặt Số Xanh Dương không chỉ nổi bật với thiết kế vỏ vật liệu cao cấp mà còn được trang bị những tính năng vượt trội, mang lại trải nghiệm đẳng cấp cho người sử dụng.</p>
                        <p>Đồng hồ được trang bị chức năng chống nước tuyệt đối, với khả năng chịu đựng độ sâu lên đến 3.900 mét (12.800 feet), là một trong những chiếc đồng hồ lặn có khả năng chống nước tốt nhất trên thế giới. Điều này giúp người đeo yên tâm sử dụng trong các hoạt động lặn sâu và thám hiểm dưới biển mà không lo lắng về độ bền và tính năng của đồng hồ.</p>
                        <p>Bộ máy cơ tự động 3235 được Rolex phát triển và sản xuất, mang lại độ chính xác cao và khả năng dự trữ năng lượng lên đến 70 giờ. Bộ máy này không chỉ đảm bảo hoạt động mượt mà mà còn được chứng nhận độ chính xác cao nhất từ COSC (Viện Kiểm tra Đồng hồ Chronometer Thụy Sĩ).</p>
                        <p>Với chứng nhận Superlative Chronometer, 278288rbr-0041 đảm bảo hiệu suất vượt trội trong mọi điều kiện. Rolex Datejust 31 Red Ombré không chỉ là một chiếc đồng hồ mà còn là biểu tượng của sự thành công và phong cách sống đẳng cấp.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136668LB_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '136660') !== false): ?>
    <section class="product-story-section" style="background-color: #b2c9df;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ D-BLUE</h3>
                    <h2>Tôn vinh sự kiện lặn sâu lịch sử</h2>
                    <p>Kỷ niệm lần lặn một mình lịch sử của đạo diễn James Cameron.</p>
                    <p>Từ màu xanh dương rực rỡ đến màu đen sâu thẳm, mặt đồng hồ chuyển sắc hai màu kỷ niệm cuộc hành trình của người đàn ông đã đi đến nơi sâu nhất trên Trái đất: rãnh Mariana.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136660_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ CHẤT LIỆU GỐM VÀ MÀN HÌNH HIỂN THỊ PHÁT QUANG</h3>
                    <h2>Chiếc đồng hồ thợ lặn huyền thoại</h2>
                    <p>Vành đồng hồ xoay một chiều có vạch chia 60 phút của Rolex Deepsea cho phép các thợ lặn giám sát số lần lặn và giải nén của họ một cách chính xác và an toàn.</p>
                    <p>Nó được trang bị miếng đệm vành Cerachrom màu đen đã được cấp bằng sáng chế do Rolex sản xuất, bởi chất liệu gốm sứ chống trầy xước mà màu sắc không bị ảnh hưởng bởi tia cực tím. Vạch chia được phủ qua công nghệ PVD (Công nghệ mạ chân không) bằng một lớp bạch kim mỏng. Mặt đồng hồ được trang bị vạch giờ dạ quang Chromalight và kim lớn, phủ vật liệu phát quang phát ra ánh sáng màu xanh dương lâu dài, mang lại khả năng đọc rõ ràng trong điều kiện tối.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136660_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THÉP OYSTERSTEEL</h3>
                    <h2>Khả năng chống ăn mòn mạnh mẽ</h2>
                    <p>Rolex sử dụng thép Oystersteel cho vỏ đồng hồ bằng thép. Thép Oystersteel do thương hiệu Rolex phát triển và thuộc dòng thép 904L - loại hợp kim được sử dụng phổ biến nhất trong ngành công nghệ cao, hàng không và hóa chất, đây là những ngành mà độ kháng ăn mòn cực đại là điều thiết yếu.</p>
                    <p>Thép Oystersteel có sức bền ưu việt và có độ sáng bóng tuyệt vời khi được đánh bóng, đồng thời duy trì được vẻ đẹp ngay cả trong những môi trường khắc nghiệt nhất.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136660_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng, tính thẩm mỹ và công nghệ, được thiết kế thỏa mãn tiêu chí bền chắc và thoải mái.</p>
                    <p>Thiết kế được trang bị khóa Oysterlock giúp ngăn chặn việc bung khóa bất ngờ, với thiết kế Glidelock khéo léo, hỗ trợ tùy chỉnh dây đeo không cần dụng cụ – qua đó cho phép dây đeo được sử dụng thoải mái cùng đồ lặn.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136660_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Deepsea 126660</h2>
                        <p>Khả năng kháng nước phi thường Được mệnh danh là “quái vật đại dương”, phiên bản 126660 sở hữu cấu trúc vỏ Ringlock System độc quyền, cho phép đồng hồ chịu được áp suất nước khổng lồ ở độ sâu 3.900 mét (12.800 feet). Đặc biệt, van thoát khí Helium (Helium Escape Valve) được trang bị bên hông vỏ giúp bảo vệ đồng hồ an toàn tuyệt đối trong quá trình giảm áp khi lặn bão hòa chuyên nghiệp.</p>
                        <p>Mặt số D-Blue huyền bí Điểm nhấn đắt giá nhất chính là mặt số D-Blue với hiệu ứng gradient chuyển dần từ màu xanh thẫm của đại dương sang màu đen tuyền của vực sâu không đáy. Các cọc số và kim được phủ chất dạ quang Chromalight xanh dương, đảm bảo khả năng quan sát vượt trội ngay cả trong môi trường tối tăm nhất dưới đáy biển.</p>
                        <p>Trái tim cơ khí mạnh mẽ - Calibre 3235 Bên trong bộ vỏ thép kiên cố là bộ máy tự động Calibre 3235 thế hệ mới do Rolex phát triển toàn toàn. Bộ máy này mang lại khả năng dự trữ năng lượng ấn tượng lên đến 70 giờ và độ chính xác tối ưu nhờ bộ thoát Chronergy. Kế thừa chứng nhận Superlative Chronometer, Deepsea 126660 cam kết sai số chỉ ở mức -2/+2 giây mỗi ngày sau khi lắp vỏ, vượt xa tiêu chuẩn COSC thông thường.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex136660_cuoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '116508') !== false): ?>
    <section class="product-story-section" style="background-color: #dae8da;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao. Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia.</p>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng. Được giới thiệu lần đầu tiên vào cuối những năm 1930, loại dây đeo kim loại bản rộng, đặc biệt bền chắc và thoải mái với ba mảnh dạng phẳng, đây vẫn giữ vai trò là chiếc dây đeo đồng hồ phổ biến nhất trong bộ sưu tập Oyster.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116508_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116508_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU XANH LÁ CÂY TƯƠI</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu xanh lá cây tươi và vàng kim với bộ đếm phai phủ, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116508_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THANG ĐO TACHYMETRIC</h3>
                    <h2>Bộ bấm giờ hiệu năng cao</h2>
                    <p>Với thang đo tachymetric, ba bộ đếm và nút bấm, đồng hồ Cosmograph Daytona được thiết kế để trở thành công cụ thời gian chính xác cho các tay đua bền. Vành đồng hồ đặc trưng có thang đo tachymetric để đọc tốc độ trung bình trên một khoảng cách và thời gian nhất định.</p>
                    <p>Thang đo này đảm bảo mức độ dễ đọc tối ưu, giúp đồng hồ Cosmograph Daytona trở thành công cụ lý tưởng để đo tốc độ lên đến 400 đơn vị mỗi giờ, đơn vị tính bằng km hoặc dặm.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116508_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính năng của Đồng Hồ Rolex Cosmograph Daytona 40 116508-0013 Mặt Số Xanh Lá Dây Oyster</h2>
                        <p>Chiếc Rolex Cosmograph Daytona 116508-0013 là biểu tượng của sự sang trọng bậc nhất với bộ vỏ và dây đeo Oyster được chế tác hoàn toàn từ vàng vàng 18k nguyên khối.</p>
                        <p>Điểm nhấn đắt giá nhất chính là mặt số màu xanh lá cây rực rỡ - hay còn gọi là phiên bản "John Mayer" - kết hợp cùng các chi tiết vạch số và kim bằng vàng tạo nên một tổng thể đầy quyền lực.</p>
                        <p>Về mặt kỹ thuật, đồng hồ sở hữu vành bezel khắc thang đo tachymeter cho phép đo tốc độ trung bình, vận hành chính xác nhờ bộ máy Calibre 4130 bền bỉ với khả năng dự trữ năng lượng lên đến 72 giờ.</p>
                        <p>Bên cạnh khả năng chống nước ở độ sâu 100m, mẫu đồng hồ này còn đạt chứng nhận Superlative Chronometer, đảm bảo sai số cực thấp và sự tin cậy tuyệt đối cho những người sưu tầm tinh hoa.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex116508_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false): ?>
    <section class="product-story-section" style="background-color: #d8c3c5;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTERFLEX</h3>
                    <h2>Khả năng chống chịu mạnh mẽ và bền bỉ</h2>
                    <p>Các phiên bản Cosmograph Daytona bằng vàng 18 ct với vành đồng hồ Cerachrom có trên dây đeo Oysterflex. Được phát triển bởi Rolex và đã được cấp bằng sáng chế, dây đeo này kết hợp độc đáo giữa độ bền của một chiếc dây đeo kim loại với sự thoải mái của dây đeo đàn hồi.</p>
                    <p>Nó được làm từ hai lưỡi kim loại cong, linh hoạt - mỗi lưỡi cho một phần dây đeo - được đúc khuôn với vật liệu đàn hồi hiệu suất cao màu đen. Để mang lại sự thoải mái tối ưu, dây đeo Oysterflex được trang bị miếng đệm ở các mặt bên trong và có khóa an toàn Oysterlock để ngăn việc bung khóa bất ngờ. Chiều dài của dây có thể được điều chỉnh thông qua hệ thống nới dây kiểu khóa trượt Rolex khéo léo.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm126535tbr_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG EVEROSE 18 CT</h3>
                    <h2>Bằng sáng chế độc quyền</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu đỏ bụi sundust, nạm kim cương với bộ đếm xoắn ốc, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm126535tbr_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU ĐỎ SUNDUST</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu đỏ bụi sundust, nạm kim cương với bộ đếm xoắn ốc, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm126535tbr_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ NẠM KIM CƯƠNG</h3>
                    <h2>Tô điểm mẫu đồng hồ chronograph huyền thoại</h2>
                    <p>Mẫu đồng hồ có vành đồng hồ được trang trí với 36 viên kim cương cắt hình thang.</p>
                    <p>Chiếc chronograph huyền thoại này hiện là công cụ được lựa chọn để bấm giờ và xác định vận tốc trung bình. Trên phiên bản đính đá quý này, thang đo tachymetric biểu tượng được thay thế bằng trang trí kim cương.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm126535tbr_vongso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 40 126535TBR Mặt Số Hồng Phấn</h2>
                        <p>Rolex Cosmograph Daytona 40 126535TBR-0002 không chỉ thu hút bởi vẻ ngoài đẹp mắt mà còn sở hữu những tính năng vượt trội. Được trang bị bộ máy tự động calibre 4131 do Rolex phát triển, chiếc đồng hồ này mang lại độ chính xác cao và khả năng dự trữ năng lượng lên đến 72 giờ. Bộ máy này được thiết kế với hệ thống chống sốc Paraflex và dây tóc Parachrom xanh, giúp đồng hồ hoạt động ổn định và bền bỉ trong mọi điều kiện khắc nghiệt.</p>
                        <p>Chức năng bấm giờ chính xác của Rolex Cosmograph Daytona 40 là công cụ lý tưởng cho các cuộc đua và hoạt động thể thao. Đồng hồ có khả năng đo thời gian với độ chính xác đến 1/8 giây, cùng với khả năng đo lường lên đến 12 giờ, giúp người đeo theo dõi thời gian một cách hiệu quả và chính xác nhất.</p>
                        <p>Khả năng chống nước của Rolex Cosmograph Daytona 40 126535TBR-0002 lên đến 100 mét, cho phép bạn yên tâm sử dụng trong mọi hoạt động dưới nước. Mặt kính sapphire chống trầy xước bảo vệ mặt số, giữ cho đồng hồ luôn sáng bóng và mới mẻ. Núm vặn Triplock đảm bảo độ kín nước tuyệt đối, mang đến sự an tâm và tin cậy cho người sử dụng.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolexm126535tbr_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false): ?>
    <section class="product-story-section" style="background-color: #c0a671;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU VÀNG KIM</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu vàng kim, nạm kim cương với bộ đếm phụ, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126598TBR_matso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH ĐỒNG HỒ NẠM KIM CƯƠNG</h3>
                    <h2>Tô điểm mẫu đồng hồ chronograph huyền thoại</h2>
                    <p>Mẫu đồng hồ có vành đồng hồ được trang trí với 36 viên kim cương cắt hình thang.</p>
                    <p>Chiếc chronograph huyền thoại này hiện là công cụ được lựa chọn để bấm giờ và xác định vận tốc trung bình. Trên phiên bản đính đá quý này, thang đo tachymetric biểu tượng được thay thế bằng trang trí kim cương.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126598TBR_vongso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126598TBR_chuyendong.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao. Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia.</p>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng. Được giới thiệu lần đầu tiên vào cuối những năm 1930, loại dây đeo kim loại bản rộng, đặc biệt bền chắc và thoải mái với ba mảnh dạng phẳng, bản rộng này vẫn giữ vai trò là chiếc dây đeo đồng hồ phổ biến nhất trong bộ sưu tập Oyster.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126598TBR_daydeo.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 126598TBR</h2>
                        <p>Điểm đặc biệt nhất và nổi bật nhất của chiếc đồng hồ này chính là cỗ máy bấm giờ thể thao (chronograph) vô cùng chính xác, được vận hành bởi bộ máy Calibre 4131 thế hệ mới. Với cấu trúc tích hợp bánh xe cột (column wheel) và ly hợp dọc (vertical clutch), người sử dụng có thể kích hoạt chức năng bấm giờ ngay lập tức một cách nhẹ nhàng, mượt mà mà không lo bị giật lag, độ trễ thời gian gần như bằng 0 so với một chiếc máy cơ thông thường.</p>
                        <p>Về mặt kỹ thuật, đồng hồ sở hữu vành bezel nạm kim cương với chức năng tachymeter cho phép đo tốc độ trung bình, vận hành chính xác nhờ bộ máy Calibre 4131 bền bỉ với khả năng dự trữ năng lượng lên đến 72 giờ.</p>
                        <p>Bên cạnh khả năng chống nước ở độ sâu 100m, mẫu đồng hồ này còn đạt chứng nhận Superlative Chronometer, đảm bảo sai số cực thấp và sự tin cậy tuyệt đối cho những người sưu tầm tinh hoa.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126598TBR_duoicung.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '126518LN') !== false): ?>
    <section class="product-story-section" style="background-color: #dfceaa;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTERFLEX</h3>
                    <h2>Khả năng chống chịu mạnh mẽ và bền bỉ</h2>
                    <p>Các phiên bản Cosmograph Daytona bằng vàng 18 ct với vành đồng hồ Cerachrom có trên dây đeo Oysterflex. Được phát triển bởi Rolex và đã được cấp bằng sáng chế, dây đeo này kết hợp độc đáo giữa độ bền của một chiếc dây đeo kim loại với sự thoải mái của dây đeo đàn hồi.</p>
                    <p>Nó được làm từ hai lưỡi kim loại cong, linh hoạt - mỗi lưỡi cho một phần dây đeo - được đúc khuôn với vật liệu đàn hồi hiệu suất cao màu đen. Để mang lại sự thoải mái tối ưu, dây đeo Oysterflex được trang bị miếng đệm ở các mặt bên trong và có khóa an toàn Oysterlock để ngăn việc bung khóa bất ngờ. Chiều dài của dây có thể được điều chỉnh thông qua hệ thống nới dây kiểu khóa trượt Rolex khéo léo.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126518_daydeo.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG KIM 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126518_chuyendong.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU XANH NGỌC LAM</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu xanh ngọc lam với bộ đếm xoắn ốc, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126518_matso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THANG ĐO TACHYMETRIC</h3>
                    <h2>Bộ bấm giờ hiệu năng cao</h2>
                    <p>Một phần quan trọng tạo nên phong cách của dòng sản phẩm là vành đồng hồ đúc thang đo tachymetric để đo tốc độ trung bình lên tới 400 dặm hoặc ki-lô-mét mỗi giờ. Pha trộn giữa công nghệ cao với yếu tố thẩm mỹ tinh tế, vành đồng hồ màu đen là sự gợi nhớ đến mẫu năm 1965 được trang bị một miếng đệm vành đồng hồ Plexiglas màu đen.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126518_vongso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 126518LN</h2>
                        <p>Chiếc Rolex Cosmograph Daytona 126518LN là một kiệt tác thiết kế, kết hợp hoàn hảo giữa sự sang trọng của chất liệu Vàng vàng 18K nguyên khối và nét thể thao mạnh mẽ của đồng hồ thể thao chuyên dụng. Điểm nhấn ấn tượng nhất chính là mặt số màu xanh ngọc lam (Turquoise) rực rỡ, mang đến vẻ đẹp hiện đại, trẻ trung nhưng vẫn giữ được đẳng cấp thượng lưu đặc trưng của nhà Rolex.</p>
                        <p>Vành bezel gốm Cerachrom màu đen tích hợp thang đo vận tốc Tachymeter vành đai Oysterflex bền bỉ, chiếc đồng hồ không chỉ hỗ trợ tối đa cho các tay đua tính toán tốc độ trung bình mà còn mang lại cảm giác thoải mái nhất khi đeo trên tay. Bên trong bộ vỏ là bộ máy chuyển động tự động Calibre 4131 tiên tiến, đảm bảo sự chính xác tuyệt đối và khả năng dự trữ năng lượng lên tới 72 giờ.</p>
                        <p>Đem cảm giác lôi cuốn, mẫu Daytona này còn mang giá trị sưu tầm cao nhờ sự kết hợp màu sắc độc đáo, thường được giới mộ điệu gọi là phiên bản "Tiffany" đầy sức hút. Việc sở hữu một chiếc đồng hồ hội tụ cả chất liệu quý hiếm lẫn màu sắc mặt số đang dẫn đầu xu hướng giúp chủ nhân khẳng định phong cách và độ chịu chơi đích thực.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126518_duoicung.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '126509') !== false): ?>
    <section class="product-story-section" style="background-color: #8fa8ca;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>THANG ĐO TACHYMETRIC</h3>
                    <h2>Bộ bấm giờ hiệu năng cao</h2>
                    <p>Với thang đo tachymetric, ba bộ đếm và nút bấm, đồng hồ Cosmograph Daytona được thiết kế để trở thành công cụ thời gian chính xác cho các tay đua bền. Vành đồng hồ đặc trưng có thang đo tachymetric để đọc tốc độ trung bình trên một khoảng cách và thời gian nhất định.</p>
                    <p>Thang đo này đảm bảo mức độ dễ đọc tối ưu, giúp đồng hồ Cosmograph Daytona trở thành công cụ lý tưởng để đo tốc độ lên đến 400 đơn vị mỗi giờ, đơn vị tính bằng km hoặc dặm.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126509_vongso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ MÀU XANH DƯƠNG SÁNG</h3>
                    <h2>Kết hợp bộ đếm với chức năng bấm giờ</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ màu xanh dương sáng với bộ đếm xoắn ốc, có vạch dấu giờ đính đá và kim đồng hồ bằng vàng 18 ct với màn hình hiển thị Chromalight, làm từ vật liệu phát quang dễ đọc.</p>
                    <p>Mặt đồng hồ này cho phép các tay đua có thể theo dõi thời gian đua và đề ra chiến lược.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126509_matso.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG TRẮNG 18 CT</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126509_chuyendong.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DÂY ĐEO OYSTER</h3>
                    <h2>Thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao. Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia.</p>
                    <p>Dây đeo Oyster là một thiết kế giả kim hoàn hảo về cả kiểu dáng lẫn chức năng. Được giới thiệu lần đầu tiên vào cuối những năm 1930, loại dây đeo kim loại bản rộng, đặc biệt bền chắc và thoải mái với ba mảnh dạng phẳng, đây vẫn giữ vai trò là chiếc dây đeo đồng hồ phổ biến nhất trong bộ sưu tập Oyster.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126509_daydeo.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Rolex Cosmograph Daytona 126509</h2>
                        <p>Khả năng kháng nước phi thường được mệnh danh là "vua tốc độ" phiên bản 126509 sở hữu cấu trúc vỏ cứng cáp, cho phép đồng hồ chịu được áp suất nước khổng lồ ở độ sâu 100 mét. Đặc biệt, van xả tự động và thiết kế núm vặn khóa Triplock được trang bị bên hông vỏ giúp bảo vệ đồng hồ an toàn tuyệt đối trong quá trình sử dụng và bơi lội.</p>
                        <p>Mặt số màu xanh dương sáng Điểm nhấn đắt giá nhất chính là mặt số xanh dương với hiệu ứng chải tia đồng tâm (sunray), chuyển sắc nhẹ nhàng dưới ánh sáng. Các cọc số và kim được phủ chất dạ quang Chromalight xanh dương, đảm bảo khả năng quan sát vượt trội ngay cả trong môi trường tối tăm nhất.</p>
                        <p>Trái tim cơ khí mạnh mẽ - Calibre 4131. Bên trong bộ vỏ vàng trắng 18k kiên cố là bộ máy tự động Calibre 4131 thế hệ mới do Rolex phát triển hoàn toàn. Bộ máy này mang lại khả năng dự trữ năng lượng ấn tượng lên đến 72 giờ và độ chính xác tối ưu nhờ bộ thoát Chronergy. Kế thừa chứng nhận Superlative Chronometer, Daytona 126509 cam kết sai số chỉ ở mức -2/+2 giây mỗi ngày sau khi lắp vỏ, vượt xa tiêu chuẩn COSC thông thường.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_rolex/rolex126509_duoicung.png" style="border: none !important; outline: none !important; box-shadow: none !important; border-radius: 15px !important;" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php else: ?>
    <section class="product-story-section" style="background-color: #f0c4c2;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>Mặt đồng hồ màu đỏ Ombré</h3>
                    <h2>Tính liên tục của một thiết kế</h2>
                    <p>Mặt đồng hồ màu xám đen ombré với bề mặt màu ở trung tâm chuyển dần sang màu đen đậm ở phần mép. Kiểu mặt số này thể hiện tính liên tục của thiết kế mà Rolex đã giới thiệu từ thập niên 1980 và quay trở lại vào năm 2019.</p>
                    <p>Việc sản xuất những mặt đồng hồ này với độ chuyển sắc đồng tâm bao gồm việc áp dụng kỹ thuật sơn mài đen - một kỹ thuật tinh vi được giám sát bởi một chuyên gia có nhiệm vụ đảm bảo sự chuyển đổi hài hòa màu sắc sang tông tối dần.</p>
                </div>
                <div class="story-img">
                    <img src="../image/chitiet_rolex/rolex_mat_so.png" alt="Mặt số Ombré">
                </div>
            </div>

            <div class="story-block">
                <div class="story-text">
                    <h3>Vành đồng hồ nạm kim cương</h3>
                    <h2>Bản giao hưởng của sự lấp lánh</h2>
                    <p>Những nghệ nhân đính đá, như những nhà điêu khắc, chạm khắc các kim loại quý để tạo thành hình dáng nơi mỗi viên đá quý sẽ được đặt một cách hoàn hảo. Với kỹ nghệ và kỹ xảo của thợ kim hoàn, mỗi viên đá đều được đặt tỉ mỉ và căn chỉnh phù hợp với những viên khác, sau đó được đính lên vành đồng hồ vàng hoặc bạch kim.</p>
                    <p>Bên cạnh chất lượng của đá, một số tiêu chí khác góp phần tạo vẻ đẹp cho đồng hồ Rolex đính đá quý gồm: sự sắp xếp chuẩn xác về độ cao của các viên đá, hướng và vị trí của chúng, độ đều đặn, chắc chắn và tỷ lệ bố trí, cũng như tính phức tạp ở công đoạn thao tác cuối với kim loại.</p>
                </div>
                <div class="story-img">
                    <img src="../image/chitiet_rolex/rolex_vang_kim.png" alt="Vành kim cương">
                </div>
            </div>

            <div class="story-block">
                <div class="story-text">
                    <h3>Vàng kim 18 ct</h3>
                    <h2>Cam kết chất lượng xuất sắc</h2>
                    <p>Nhờ có xưởng đúc riêng của mình, Rolex có khả năng đúc hợp kim vàng 18 ct chất lượng cao nhất. Theo tỷ lệ bạc, đồng, bạch kim hoặc palladium, Rolex tạo ra được các loại vàng 18 ct: vàng kim, vàng hồng hoặc vàng trắng.</p>
                    <p>Chúng được làm bằng kim loại tinh khiết nhất và được kiểm tra tỉ mỉ trong một phòng thí nghiệm nội bộ với thiết bị hiện đại, trước khi vàng được tạo hình và chế tác với sự đầu tư chăm sóc chất lượng chu đáo nhất. Rolex cam kết độ xuất sắc bắt đầu từ bước nguyên liệu.</p>
                </div>
                <div class="story-img">
                    <img src="../image/chitiet_rolex/rolex_chuyen_dong.png" alt="Vàng kim">
                </div>
            </div>
            
            <div class="story-block">
                <div class="story-text">
                    <h3>Dây đeo President</h3>
                    <h2>Vẻ tinh tế tối thượng</h2>
                    <p>Việc thiết kế, phát triển và sản xuất dây đeo Rolex và khóa cài, cũng như các bài kiểm tra nghiêm ngặt chúng phải đối mặt, đòi hỏi phải ứng dụng công nghệ cao.</p>
                    <p>Và với mọi bộ phận của đồng hồ, tính thẩm mỹ được đảm bảo dưới con mắt chuyên gia. Dây đeo President với mối nối 3 mảnh bán nguyệt được chế tác vào năm 1956 cho sự ra mắt của dòng sản phẩm Oyster Perpetual Day-Date. Dây đeo này đại diện sự tinh tế và thoải mái, luôn được làm bằng kim loại quý sau khi tuyển chọn cẩn thận.</p>
                </div>
                <div class="story-img">
                    <img src="../image/chitiet_rolex/rolex_day_deo.png" alt="Dây đeo President">
                </div>
            </div>

            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text"><h2>Tính Năng Đồng Hồ Rolex Datejust 31 278288RBR Mặt Số Đỏ Ombré</h2>
                        <p>Trái tim của Datejust 31 278288RBR là bộ máy Caliber 2236 tự động, được sản xuất hoàn toàn tại Rolex. Bộ máy này mang lại độ chính xác cao với sai số chỉ ±2 giây mỗi ngày, vượt xa tiêu chuẩn chronometer thông thường. Dự trữ năng lượng lên đến 55 giờ đảm bảo đồng hồ hoạt động liên tục ngay cả khi không đeo trong cuối tuần.</p>
                        <p>Tính năng Datejust kinh điển cho phép hiển thị ngày tháng tại vị trí 3 giờ với kính lúp Cyclops phóng đại 2.5 lần, giúp đọc ngày tháng dễ dàng và chính xác. Cơ chế thay đổi ngày tức thời vào lúc nửa đêm đảm bảo tính chính xác tuyệt đối.</p>
                        <p>Rolex 278288RBR Ombré có khả năng chống nước đến độ sâu 100 mét (330 feet), phù hợp cho các hoạt động hàng ngày và thể thao nhẹ. Vỏ Oyster chắc chắn bảo vệ bộ máy khỏi va đập và tác động môi trường.</p>
                        <p>Với chứng nhận Superlative Chronometer, 278288rbr-0041 đảm bảo hiệu suất vượt trội trong mọi điều kiện. Rolex Datejust 31 Red Ombré không chỉ là một chiếc đồng hồ mà còn là biểu tượng của sự thành công và phong cách sống đẳng cấp.</p></div>
                </div>
                <div class="story-img">
                    <img src="../image/chitiet_rolex/rolex_anh_duoi_cung.png" alt="Rolex Footer">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="bottom-info-section">
        <h3 class="cert-title">Chứng nhận</h3>
        <p class="cert-desc">Superlative Chronometer (chứng nhận COSC + Rolex sau khi lắp vỏ)</p>

        <h3 class="pub-title" style="text-align: center;">Ấn phẩm</h3>
        <a href="#" class="download-link"><i class="fa-solid fa-download"></i> Tải ấn phẩm</a>
        
    <?php
        $anpham_img = 'm278288rbr-anpham.jpg'; // Mặc định là hộp đỏ
        if (strpos($row['ten_san_pham'], 'LV') !== false || strpos($row['ten_san_pham'], 'Starbucks') !== false) {
            $anpham_img = 'm126610lv-rolexxanh_anpham.jpg'; 
        } elseif (strpos($row['so_tham_chieu'], '124060') !== false || strpos($row['so_tham_chieu'], '126610LN') !== false) {
            $anpham_img = 'm124060-0001lv_anpham.jpg'; 
        } elseif (strpos($row['ten_san_pham'], '126231') !== false || strpos($row['ten_san_pham'], 'Rhodium') !== false) {
            $anpham_img = 'm126231-0015_anpham.avif'; 
        } elseif (strpos($row['so_tham_chieu'], '136668LB') !== false) {
            $anpham_img = 'm136668lb-0001_anpham.jpg'; 
        } elseif (strpos($row['so_tham_chieu'], '136660') !== false) {
            $anpham_img = 'm136660-0005_rolex_anpham.avif'; 
        } elseif (strpos($row['so_tham_chieu'], '116508') !== false) {
            $anpham_img = 'm116508-0002anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '126535TBR') !== false) {
            $anpham_img = 'm126535tbr-0002anpham.avif'; 
        
        } elseif (strpos($row['so_tham_chieu'], '116518LN') !== false && strpos($row['ten_san_pham'], '126518LN') === false) {
            $anpham_img = 'm116518LN-0041anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '116503') !== false) {
            $anpham_img = 'rolex116503_anpham.png';
            
        } elseif (strpos($row['so_tham_chieu'], '52508') !== false) {
            $anpham_img = 'm52508-0006_anpham.png';
            
        } elseif (strpos($row['so_tham_chieu'], '126711CHNR') !== false) {
            $anpham_img = 'm126711chnr-0002_anpham.png';
            
        } elseif (strpos($row['so_tham_chieu'], '126598TBR') !== false) {
            $anpham_img = 'm126598tbr-0001_anpham.avif'; 
            
        } elseif (strpos($row['so_tham_chieu'], '126518LN') !== false) {
            $anpham_img = 'm126518ln-0014_anpham.avif'; 
            
        } elseif (strpos($row['so_tham_chieu'], '126509') !== false) {
            $anpham_img = 'm126509-0005_anpham.avif'; 
        }
        ?>
        <img src="../image/chitiet_rolex/<?php echo $anpham_img; ?>" 
             style="max-width:300px; box-shadow: none !important; border-radius: 15px !important; border: none !important; outline: none !important;" alt="Ấn phẩm Rolex" class="publication-img" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'">
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
    
        /* CSS cho hệ thống đánh giá */
        .review-section { max-width: 1200px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
        .review-title { font-size: 20px; border-bottom: 2px solid #b58b5a; padding-bottom: 10px; margin-bottom: 20px; color: #333; font-family: "Playfair Display", serif; }
        .review-summary { display: flex; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
        .review-rating-overview { text-align: center; padding: 20px; background: #faf7f2; border-radius: 8px; min-width: 200px; }
        .review-big-rating { display: flex; align-items: baseline; justify-content: center; gap: 5px; margin-bottom: 10px; }
        .review-score { font-size: 48px; font-weight: bold; color: #b58b5a; }
        .review-out-of { font-size: 18px; color: #888; }
        .review-stars { display: flex; justify-content: center; gap: 5px; margin-bottom: 10px; }
        .review-total { font-size: 14px; color: #666; }
        .review-form { flex: 1; min-width: 300px; }
        .review-stars-input { margin-bottom: 15px; }
        .review-stars-input label { display: block; margin-bottom: 10px; font-weight: bold; color: #333; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 30px; color: #ddd; cursor: pointer; transition: color 0.2s; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label { color: #f39c12; }
        .review-form textarea { width: 100%; height: 100px; padding: 15px; border: 1px solid #ccc; border-radius: 5px; resize: none; font-family: inherit; outline: none; margin-bottom: 15px; }
        .review-form textarea:focus { border-color: #b58b5a; }
        .review-file-upload { margin-bottom: 15px; }
        .review-file-upload label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .review-file-upload input[type="file"] { padding: 10px; border: 1px dashed #ccc; border-radius: 5px; width: 100%; }
        .image-preview { margin-top: 10px; }
        .image-preview img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; }
        .btn-submit-review { background: #b58b5a; color: #fff; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-submit-review:hover { background: #967045; }
        .review-notice { padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .review-notice i { font-size: 20px; }
        .review-notice.notice-info { background: #f0f7ff; color: #0056b3; border: 1px solid #cce5ff; }
        .review-notice.notice-warning { background: #fff5f5; color: #c92a2a; border: 1px solid #ffc9c9; }
        .review-notice.notice-success { background: #f4fbf7; color: #2b8a3e; border: 1px solid #b2f2bb; }
        .review-notice a { color: inherit; font-weight: bold; text-decoration: underline; }
        .review-success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .review-error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .review-list { display: flex; flex-direction: column; gap: 20px; }
        .review-item { display: flex; gap: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .review-item-header { flex: 1; }
        .review-user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .review-user-avatar { width: 40px; height: 40px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #b58b5a; font-size: 18px; }
        .review-user-details { display: flex; flex-direction: column; }
        .review-author { font-weight: bold; color: #333; }
        .review-date { font-size: 12px; color: #888; }
        .review-item-stars { display: flex; gap: 3px; margin-bottom: 10px; }
        .review-item-content { flex: 1; }
        .review-item-content p { color: #555; line-height: 1.6; margin-bottom: 10px; }
        .review-item-image img { max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #eee; cursor: pointer; transition: transform 0.2s; }
        .review-item-image img:hover { transform: scale(1.05); }
        .review-no-data { text-align: center; padding: 40px; color: #888; }
        .review-no-data i { font-size: 40px; color: #ddd; margin-bottom: 15px; display: block; }
    </style>

    
    
    
<?php 
$sp_id = $row['id'];
include 'module_danh_gia.php'; 
?>

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

    <script>
        // 1. AJAX: THẢ TIM SẢN PHẨM
        function toggleFav(productId) {
            fetch('../action_yeuthich.php?action=toggle&id=' + productId)
            .then(res => res.text())
            .then(data => {
                if(data.trim() === 'not_logged_in') {
                    showGlassPrismToast('Vui lòng đăng nhập để lưu sản phẩm!', 'fa-triangle-exclamation', '#d9534f');
                    setTimeout(() => { window.location.href = '../login.php'; }, 2000);
                } else if(data.trim() === 'added') {
                    document.getElementById('fav-btn').innerHTML = '<i class="fa-solid fa-heart" style="color: #d9534f;"></i> <span style="color: #d9534f; font-weight: bold;">Đã thích</span>';
                    showGlassPrismToast('Đã lưu vào danh sách yêu thích!', 'fa-heart', '#d9534f');
                } else if(data.trim() === 'removed') {
                    document.getElementById('fav-btn').innerHTML = '<i class="fa-regular fa-heart"></i> Yêu thích';
                    showGlassPrismToast('Đã bỏ thích sản phẩm!', 'fa-heart-crack', '#888');
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
    
    <?php if (file_exists(__DIR__ . '/../thongbao.php')) { include __DIR__ . '/../thongbao.php'; } ?>


    <script>
        // Preview ảnh khi chọn file
        function previewImage(input) {
            const preview = document.getElementById("image-preview");
            preview.innerHTML = "";
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Mở ảnh modal
        function openImageModal(src) {
            const modal = document.createElement("div");
            modal.style.cssText = "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;cursor:pointer;";
            modal.innerHTML = '<img src="' + src + '" style="max-width:90%;max-height:90%;border-radius:10px;">';
            modal.onclick = function() { document.body.removeChild(modal); };
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>