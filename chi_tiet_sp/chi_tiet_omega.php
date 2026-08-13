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

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Định dạng tiền tệ
        $gia_ban = number_format($row['gia_ban'], 0, ',', '.') . ' VNĐ';
        $gia_cu = (!empty($row['gia_cu']) && $row['gia_cu'] > 0) ? number_format($row['gia_cu'], 0, ',', '.') . ' VNĐ' : '';
    } else {
        die("<h2 style='text-align:center; margin-top:50px;'>Sản phẩm không tồn tại trong hệ thống!</h2>");
    }
} else {
    die("<h2 style='text-align:center; margin-top:50px;'>Không tìm thấy sản phẩm! Bạn hãy quay lại trang danh sách.</h2>");
}

// 4. KIỂM TRA YÊU THÍCH
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $check_fav = $conn->query("SELECT id FROM yeu_thich WHERE id_nguoi_dung = $uid AND id_san_pham = $id");
    if ($check_fav && $check_fav->num_rows > 0) {
        $is_favorited = true;
    }
}

// 5. KHAI BÁO BIẾN ĐƯỜNG DẪN LÙI VỀ THƯ MỤC GỐC & CSS RIÊNG
$path_prefix = '../'; 
$custom_css = 'chi_tiet.css';

/**
 * format_img_url(): Hàm xử lý đường dẫn ảnh chung
 * - Nếu rỗng → trả về ảnh mặc định chung (no-image.png)
 * - Nếu là http:// hoặc https:// → giữ nguyên (Azure/CDN)
 * - Nếu là đường dẫn local → chuẩn hóa thành ../path
 */
if (!function_exists('format_img_url')) {
    function format_img_url($url) {
        $url = trim($url ?? '');
        
        // 🟢 ĐÃ SỬA: Thay seiko1-1.png thành no-image.png để không bị dính ảnh Seiko sang các trang khác
        if (empty($url)) return '../image/no-image.png';
        
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url; // Azure / CDN URL – giữ nguyên
        }
        
        // Local path: bỏ ./ hay ../ dư thừa rồi nối ../
        return '../' . ltrim(preg_replace('#^\.{1,2}/#', '', $url), '/');
    }
}

// 6. NHÚNG HEADER CHUNG
include $path_prefix . 'header.php';

// 7. XỬ LÝ ĐƯỜNG DẪN ẢNH CHÍNH (dùng format_img_url chuẩn)
$anh_chinh = format_img_url($row['anh_san_pham'] ?? '');
$so_ref = $row['so_tham_chieu'] ?? '';
?>

<div style="background-color: #f9f9f9; padding: 30px 0;">
    <div class="product-detail-container" style="max-width: 1200px; margin: 0 auto; display: flex; gap: 30px; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <!-- 🖼️ KHU VỰC ALBUM ẢNH (BÊN TRÁI) -->
        <div class="product-gallery" style="flex: 1; border:none; padding: 0;">
            
            <div class="main-image-container" style="position: relative; text-align: center; margin-bottom: 15px;">
                <div class="gallery-nav">
                    <i class="fa-solid fa-chevron-left" id="prev-btn"></i>
                    <i class="fa-solid fa-chevron-right" id="next-btn"></i>
                </div>
                <!-- 🟢 1. Ảnh chính: Bọc show_img_url(), nếu hỏng ảnh thì ẩn/trỏ ảnh mặc định chung -->
                <img id="main-product-img" 
                     src="<?php echo show_img_url($anh_chinh); ?>" 
                     alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>" 
                     style="max-width: 380px; width: 100%; border-radius: 8px;" 
                     onerror="this.onerror=null; this.src='../image/no-image.png';">
            </div>

            <div class="thumbnail-slider" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <!-- 🟢 2. Thumbnail Ảnh chính: Bọc show_img_url() -->
                <img src="<?php echo show_img_url($anh_chinh); ?>" 
                     class="thumb active" 
                     onclick="changeImage(this)" 
                     alt="Ảnh chính" 
                     onerror="this.onerror=null; this.style.display='none';">
                <?php
                // 🟢 3. Định nghĩa fallback chung cho Thumbnail: ẢNH LỖI -> TỰ ẨN, KHÔNG HIỆN SEIKO NỮA
                $thumb_err = "onerror=\"this.onerror=null; this.style.display='none';\"";
                ?>

                <?php if (strpos($so_ref, '210.32.42.20.04.001') !== false): ?>
                    <img src="../image/chitiet_omega/omega1_daydeo.png"    class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega1_chuyendong.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega1_matso.png"      class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega1_matsso.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega1_anpham.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '424.25.24.60.55.001') !== false): ?>
                    <img src="../image/chitiet_omega/omega2_daydeo.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega2_chuyendong.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega2_matso.png"      class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega2_full.png"       class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega2_anpham.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '210.30.44.51.03.002') !== false): ?>
                    <img src="../image/chitiet_omega/omega3_daydeo.png"    class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega3_chuyendong.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega3_matso.png"      class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega3_full.png"       class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega3_anpham.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '331.20.42.51.02.001') !== false): ?>
                    <img src="../image/chitiet_omega/omega4_daydeo.png"    class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega4_duoicung.png"   class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega4_chuyendong.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega4_full.png"       class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega4_anpham.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '424.10.37.20.01.001') !== false): ?>
                    <img src="../image/chitiet_omega/omega5_daydeo.png"    class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega5_chuyendong.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega5_matso.png"      class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega5_full.png"       class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega5_anpham.png"     class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '131.20.29.20.06.001') !== false): ?>
                    <img src="../image/chitiet_omega/image57a.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image57b.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image57c.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image57d.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php elseif (strpos($so_ref, '434.20.34.20.02.001') !== false): ?>
                    <img src="../image/chitiet_omega/image58b.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image58c.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image58a.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/image58d.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">

                <?php else: ?>
                    <img src="../image/chitiet_omega/omega_default_1.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega_default_2.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                    <img src="../image/chitiet_omega/omega_default_3.png" class="thumb" onclick="changeImage(this)" onerror="<?php echo $thumb_err; ?>">
                <?php endif; ?>
            </div>
        
            <div class="highlight-box" style="border-left-color: #2b6cb0;">
                <?php if (strpos($row['so_tham_chieu'], '210.32.42.20.04.001') !== false): ?>
                    <h4 style="color: #2b6cb0; font-size: 22px;">BIỂU TƯỢNG CỦA ĐẠI DƯƠNG</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Seamaster Diver 300M này được chế tác từ thép không gỉ 316L bền bỉ, mang phong cách thể thao sang trọng. Chiếc đồng hồ này nằm trong phân khúc "Luxury Diver" (Đồng hồ lặn xa xỉ), cạnh tranh trực tiếp với các dòng thể thao nhưng có mức giá dễ tiếp cận hơn nhiều so với chất lượng kỹ thuật mang lại.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '424.25.24.60.55.001') !== false): ?>
                    <h4 style="color: #bba271; font-size: 22px;">BIỂU TƯỢNG CỦA SỰ THANH LỊCH</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu De Ville Prestige này được chế tác tinh xảo từ sự kết hợp giữa thép không gỉ và vàng vàng 18K nguyên khối, mang phong cách cổ điển đầy quý phái. Chiếc đồng hồ này nằm trong phân khúc "Luxury Dress Watch" (Đồng hồ trang sức xa xỉ), là hiện thân của vẻ đẹp nữ tính vượt thời gian.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '210.30.44.51.03.002') !== false): ?>
                    <h4 style="color: #2b6cb0; font-size: 22px;">BIỂU TƯỢNG CỦA SỰ MẠNH MẼ VÀ CHÍNH XÁC</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Seamaster Diver 300M Chronograph này được chế tác từ thép không gỉ 316L nguyên khối, mang phong cách thể thao đầy nam tính và mạnh mẽ. Chiếc đồng hồ này nằm trong phân khúc "Luxury Dive Chronograph" (Đồng hồ lặn bấm giờ xa xỉ), là hiện thân của sự bền bỉ và kỹ thuật đỉnh cao.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '331.20.42.51.02.001') !== false): ?>
                    <h4 style="color: #c98c56; font-size: 22px;">BIỂU TƯỢNG CỦA SỰ ĐỘT PHÁ VÀ LỊCH SỬ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu Speedmaster Moonwatch này được chế tác tinh xảo từ thép không gỉ kết hợp vành bezel vàng Sedna™ 18K, mang phong cách thể thao thanh lịch và đẳng cấp. Đây là chiếc đồng hồ chronograph huyền thoại từng được NASA chọn làm đồng hồ chính thức cho các sứ mệnh Apollo, là biểu tượng của sự chính xác và tinh thần chinh phục không gian.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '424.10.37.20.01.001') !== false): ?>
                    <h4 style="color: #555; font-size: 22px;">BIỂU TƯỢNG CỦA SỰ THANH LỊCH VÀ TINH TẾ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Mẫu De Ville Prestige này được chế tác tinh xảo từ thép không gỉ cao cấp, mang phong cách cổ điển thanh lịch đầy quý phái. Chiếc đồng hồ nằm trong phân khúc "Luxury Dress Watch" (Đồng hồ trang sức xa xỉ), là hiện thân của vẻ đẹp vượt thời gian, nhờ thiết kế tối giản, mặt số đen huyền bí và sự hoàn hảo trong từng chi tiết.</p>
                <?php elseif (strpos($row['so_tham_chieu'], '131.20.29.20.06.001') !== false): ?>
                    <h4 style="color: #bba271; font-size: 22px;">BIỂU TƯỢNG CỦA SỰ CHÍNH XÁC & THỜI TRANG</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Dòng Constellation đặc trưng bởi mặt số xám thanh lịch và chi tiết thiết kế "Griffes" (móng vuốt) huyền thoại. Phiên bản 29mm này là sự kết hợp hoàn hảo giữa Thép không gỉ cao cấp và Vàng Sedna™ 18K độc quyền của OMEGA.</p>

                <?php elseif (strpos($row['so_tham_chieu'], '434.20.34.20.02.001') !== false): ?>
                    <h4 style="color: #bba271; font-size: 22px;">VẺ ĐẸP CỔ ĐIỂN VƯỢT THỜI GIAN</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">De Ville Prestige là bộ sưu tập mang tính biểu tượng toàn cầu với thiết kế thanh lịch. Phiên bản 34mm sở hữu mặt số bạc rạng rỡ, kết hợp cùng bộ vỏ mỏng nhẹ chế tác từ Thép và Vàng Sedna™ 18K.</p>
                <?php else: ?>
                    <h4 style="color: #2b6cb0; font-size: 22px;">ĐỈNH CAO CHẾ TÁC THỤY SỸ</h4>
                    <p style="font-size: 18px; line-height: 1.5; color: #000;">Omega luôn tự hào mang đến những chiếc đồng hồ với độ chính xác chuẩn Master Chronometer cùng nghệ thuật chế tác vượt thời gian.</p>
                
                <?php endif; ?>
                
            </div>

            <div class="ref-number">
                Số tham chiếu: <?php echo $row['so_tham_chieu']; ?>
            </div>
        </div>

  <script>
        const images = <?php if (strpos($row['so_tham_chieu'], '210.32.42.20.04.001') !== false): ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>",
            "../image/chitiet_omega/omega1_daydeo.png",
            "../image/chitiet_omega/omega1_chuyendong.png",
            "../image/chitiet_omega/omega1_matso.png",
            "../image/chitiet_omega/omega1_matsso.png",
            "../image/chitiet_omega/omega1_anpham.png"
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '424.25.24.60.55.001') !== false): ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>",
            "../image/chitiet_omega/omega5_daydeo.png",
            "../image/chitiet_omega/omega2_chuyendong.png",
            "../image/chitiet_omega/omega2_matso.png",
            "../image/chitiet_omega/omega2_full.png",
            "../image/chitiet_omega/omega2_anpham.png"
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '210.30.44.51.03.002') !== false): ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>",
            "../image/chitiet_omega/omega3_daydeo.png",
            "../image/chitiet_omega/omega3_chuyendong.png",
            "../image/chitiet_omega/omega3_matso.png",
            "../image/chitiet_omega/omega3_full.png",
            "../image/chitiet_omega/omega3_anpham.png"
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '331.20.42.51.02.001') !== false): ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>",
            "../image/chitiet_omega/omega4_daydeo.png",
            "../image/chitiet_omega/omega4_duoicung.png",
            "../image/chitiet_omega/omega4_chuyendong.png",
            "../image/chitiet_omega/omega4_full.png",
            "../image/chitiet_omega/omega4_anpham.png"
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '424.10.37.20.01.001') !== false): ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>",
            "../image/chitiet_omega/omega5_daydeo.png",
            "../image/chitiet_omega/omega5_chuyendong.png",
            "../image/chitiet_omega/omega5_matso.png",
            "../image/chitiet_omega/omega5_full.png",
            "../image/chitiet_omega/omega5_anpham.png"
        ];

        <?php elseif (strpos($row['so_tham_chieu'], '131.20.29.20.06.001') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_omega/image57a.png",
            "../image/chitiet_omega/image57b.png",
            "../image/chitiet_omega/image57c.png",
            "../image/chitiet_omega/image57d.png"
        ];
        <?php elseif (strpos($row['so_tham_chieu'], '434.20.34.20.02.001') !== false): ?>
        [
            "../<?php echo $row['anh_san_pham']; ?>",
            "../image/chitiet_omega/image58a.png",
            "../image/chitiet_omega/image58b.png",
            "../image/chitiet_omega/image58c.png",
            "../image/chitiet_omega/image58d.png"
        ];

        <?php else: ?>
        [
            "../<?php echo trim($row['anh_san_pham']); ?>"
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
            if(thumbs[currentIndex]) {
                thumbs[currentIndex].classList.add('active');
            }
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
                <a href="../all_omega.php" style="text-decoration: none; color: #666;">Omega</a> 
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
                <p>
                    <i class="fa-solid fa-box-open"></i>
                    <strong>Tình trạng:</strong> Mới 100%, Fullbox, đầy đủ hộp, sổ, thẻ bảo hành.
                </p>
                <p>
                    <i class="fa-solid fa-shield-halved"></i>
                    <strong>Bảo hành:</strong> 5 năm quốc tế Omega & Bảo hành trọn đời tại Timeless.
                </p>
                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <strong>Địa điểm:</strong> Bảo hiểm hàng hóa 100% & Miễn phí vận chuyển toàn quốc.
                </p>
            </div>

            <h3 style="margin-top: 30px; font-size: 18px;">Thông số kỹ thuật</h3>
            <table class="specs-table">
                <?php if (strpos($row['so_tham_chieu'], '210.32.42.20.04.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>Cơ tự động Omega Co-Axial Master Chronometer Cal. 8800</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối, phủ chống phản chiếu</td></tr>
                    <tr><td>Đường kính</td><td>42 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Dây cao su cao cấp</td></tr>
                    <tr><td>Độ chịu nước</td><td>30 ATM (300 mét - Chuẩn lặn chuyên nghiệp)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '424.25.24.60.55.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>Quartz (Pin) Calibre 1376</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối, chống trầy xước</td></tr>
                    <tr><td>Đường kính</td><td>24.4 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Thép không gỉ & Vàng 18K (Demi)</td></tr>
                    <tr><td>Độ chịu nước</td><td>3 ATM (30 mét - Đi mưa, rửa tay)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '210.30.44.51.03.002') !== false): ?>
                    <tr><td>Bộ máy</td><td>Cơ tự động Omega Co-Axial Master Chronometer Cal. 9900</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối, phủ chống phản chiếu</td></tr>
                    <tr><td>Đường kính</td><td>44 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Thép không gỉ 316L</td></tr>
                    <tr><td>Độ chịu nước</td><td>30 ATM (300 mét - Chuẩn lặn chuyên nghiệp)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '331.20.42.51.02.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>Cơ tự động Omega Co-Axial Chronograph Cal. 9300</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối, chống trầy xước</td></tr>
                    <tr><td>Đường kính</td><td>41.5 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Thép không gỉ & Vàng Sedna™ 18K</td></tr>
                    <tr><td>Độ chịu nước</td><td>10 ATM (100 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '424.10.37.20.01.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>Cơ tự động Omega Co-Axial Chronometer Cal. 2500</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối, chống trầy xước hoàn hảo</td></tr>
                    <tr><td>Đường kính</td><td>36.8 mm</td></tr>
                    <tr><td>Chất liệu dây</td><td>Thép không gỉ 316L</td></tr>
                    <tr><td>Độ chịu nước</td><td>30m (3 ATM)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>
                
                <?php elseif (strpos($row['so_tham_chieu'], '131.20.29.20.06.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>OMEGA Co-Axial Master Chronometer Calibre 8700 (Kháng từ tính > 15,000 gauss)</td></tr>
                    <tr><td>Kính</td><td>Sapphire chống trầy xước, phủ chống lóa cả hai mặt</td></tr>
                    <tr><td>Đường kính</td><td>29 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Thép không gỉ & Vàng Sedna™ 18K</td></tr>
                    <tr><td>Độ chịu nước</td><td>5 ATM (50 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế OMEGA</td></tr>

                <?php elseif (strpos($row['so_tham_chieu'], '434.20.34.20.02.001') !== false): ?>
                    <tr><td>Bộ máy</td><td>OMEGA Master Chronometer Calibre 8800 (Kháng từ tính > 15,000 gauss)</td></tr>
                    <tr><td>Kính</td><td>Sapphire hình vòm chống trầy xước, phủ chống lóa bên trong</td></tr>
                    <tr><td>Đường kính</td><td>34 mm</td></tr>
                    <tr><td>Chất liệu vỏ</td><td>Thép không gỉ & Vàng Sedna™ 18K</td></tr>
                    <tr><td>Độ chịu nước</td><td>3 ATM (30 mét)</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế OMEGA</td></tr>
                    
                <?php else: ?>
                    <tr><td>Bộ máy</td><td>Cơ tự động Omega Co-Axial</td></tr>
                    <tr><td>Kính</td><td>Sapphire nguyên khối</td></tr>
                    <tr><td>Bảo hành</td><td>5 năm quốc tế</td></tr>
                    <tr><td>Xuất xứ</td><td>Thụy Sỹ (Swiss Made)</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (strpos($row['so_tham_chieu'], '210.32.42.20.04.001') !== false): ?>
    <section class="product-story-section" style="background-color: #bac5d3;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>TRẢI NGHIỆM ĐEO & DÂY CAO SU CAO CẤP</h3>
                    <h2>Khả năng chống chịu mạnh mẽ và bền bỉ</h2>
                    <p>Phiên bản Seamaster Diver 300M với vành bezel gốm Ceramic đen bóng được trang bị trên dây đeo cao su màu đen tích hợp. Được phát triển bởi Omega để chinh phục đại dương, dây đeo này kết hợp độc đáo giữa độ bền bỉ trước môi trường nước mặn với sự thoải mái, nhẹ nhàng của vật liệu hiện đại.</p>
                    <p>Nó được chế tác từ cao su tổng hợp cao cấp, thiết kế liền mạch ôm sát vỏ đồng hồ, với bề mặt được hoàn thiện chải xước mô phỏng thớ vải cực kỳ tinh tế. Để mang lại sự thoải mái tối ưu, mặt trong dây đeo được thiết kế các rãnh sóng giúp thoát khí và sử dụng khóa cài (Tang Buckle) chắc chắn để đảm bảo đồng hồ luôn cố định trên cổ tay. Chiều dài của dây có thể được điều chỉnh linh hoạt thông qua các nấc gài truyền thống, dễ dàng nới rộng ngay cả khi đeo bên ngoài bộ đồ lặn.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega1_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THÉP KHÔNG GỈ & GỐM CERAMIC</h3>
                    <h2>Vật liệu kỹ thuật cao</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ bằng gốm ceramic trắng, khắc laser họa tiết sóng, có vạch dấu giờ nổi và kim đồng hồ dạng khung xương được phủ đen (blackened) tương phản với dạ quang Super-LumiNova, làm từ vật liệu phát quang dễ đọc. Mặt đồng hồ này cho phép các thợ lặn có thể theo dõi thời gian chính xác ngay cả trong điều kiện thiếu sáng dưới đáy đại dương.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega1_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ GỐM CERAMIC TRẮNG</h3>
                    <h2>Họa tiết sóng với độ tương phản cao</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ bằng gốm ceramic (ZrO2) màu trắng tinh khiết, được khắc laser họa tiết sóng đặc trưng, có vạch dấu giờ nổi và kim đồng hồ khung xương được phủ đen (blackened) tạo sự tương phản mạnh mẽ với dạ quang Super-LumiNova, làm từ vật liệu phát quang dễ đọc. Mặt đồng hồ này cho phép các thợ lặn có thể theo dõi thời gian một cách tức thì và đảm bảo an toàn tuyệt đối dưới đáy biển.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega1_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNH BEZEL CERAMIC ĐEN</h3>
                    <h2>Công cụ đếm thời gian lặn chính xác</h2>
                    <p>Vành bezel lặn xoay một chiều được chế tác từ gốm ceramic đen chống trầy xước, với thang đo 60 phút tráng men trắng (white enamel) nổi bật. Cấu trúc viền khía rãnh mang lại độ bám tuyệt vời ngay cả khi thao tác dưới nước với găng tay lặn chuyên dụng.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega1_matsso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Omega Seamaster Diver 300M Mặt Số Gốm Trắng</h2>
                        <p>Omega Seamaster Diver 300M 210.32.42.20.04.001 không chỉ thu hút bởi vẻ ngoài hiện đại mà còn sở hữu những tính năng lặn chuyên nghiệp vượt trội. Được trang bị bộ máy tự động Co-Axial Master Chronometer Calibre 8800 do Omega phát triển, chiếc đồng hồ này mang lại độ chính xác đạt chuẩn METAS và khả năng dự trữ năng lượng lên đến 55 giờ. Bộ máy này sử dụng công nghệ Thoát Co-Axial và lò xo cân bằng Silicon Si14, giúp đồng hồ hoạt động ổn định, kháng từ trường lên đến 15.000 gauss và bền bỉ trong mọi điều kiện khắc nghiệt.</p>
                        <p>Khả năng vận hành chuyên nghiệp của Omega Seamaster Diver 300M là công cụ lý tưởng cho các hoạt động dưới đáy đại dương. Đồng hồ được trang bị van thoát khí Helium hình nón tại vị trí 10 giờ, giúp bảo vệ đồng hồ khỏi áp suất khi lặn sâu, đảm bảo an toàn tuyệt đối khi nổi lên mặt nước.</p>
                        <p>Khả năng chống nước của Omega Seamaster Diver 300M 210.32.42.20.04.001 lên đến 300 mét, cho phép bạn yên tâm sử dụng trong mọi hoạt động bơi lặn chuyên nghiệp. Mặt kính sapphire cong vòm chống trầy xước, được phủ lớp chống lóa ở cả hai mặt, bảo vệ mặt số và giúp quan sát rõ ràng dưới ánh nắng gắt. Núm vặn dạng xoáy (screw-in crown) đảm bảo độ kín nước tuyệt đối, mang đến sự an tâm và tin cậy cho người sử dụng.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega-135.webp" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '424.25.24.60.55.001') !== false): ?>
    <section class="product-story-section" style="background-color: #f3e8c1;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>TRẢI NGHIỆM ĐEO & DÂY ĐEO PHỐI VÀNG 18K</h3>
                    <h2>Sự sang trọng và mềm mại như trang sức</h2>
                    <p>Phiên bản De Ville Prestige này đi kèm với dây đeo kim loại phối màu (Two-Tone) đặc trưng. Được thiết kế bởi Omega để tôn vinh vẻ đẹp nữ tính, dây đeo này kết hợp độc đáo giữa độ sáng bóng hiện đại của thép không gỉ với sự quý phái, ấm áp của vàng vàng 18K chạy dọc theo thân dây.</p>
                    <p>Nó được chế tác từ các mắt xích nhỏ liên kết linh hoạt, ôm sát cổ tay một cách êm ái và nhẹ nhàng tựa như một chiếc lắc tay cao cấp. Để mang lại vẻ đẹp liền mạch tối ưu, dây đeo được hoàn thiện bóng bẩy và sử dụng khóa gập an toàn (Folding Clasp) kín đáo, giúp người đeo dễ dàng thao tác mà vẫn giữ được sự thanh lịch và tinh tế cho tổng thể chiếc đồng hồ.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega2_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>VÀNG 18K & XÀ CỪ (MOTHER OF PEARL)</h3>
                    <h2>Vẻ đẹp tự nhiên đầy mê hoặc</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ làm từ xà cừ trắng tự nhiên, mang lại những sắc thái màu sắc độc bản và biến đổi kỳ ảo dưới ánh sáng. Nổi bật trên nền mặt số là 8 viên kim cương lấp lánh được sử dụng làm vạch dấu giờ, xen kẽ với các chữ số La Mã và bộ kim được chế tác từ vàng 18K sang trọng. Mặt đồng hồ này không chỉ hiển thị thời gian một cách thanh lịch mà còn tôn lên khí chất quý phái, kiêu sa của người phụ nữ trong mọi khoảnh khắc.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega2_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT ĐỒNG HỒ XÀ CỪ TRẮNG NẠM KIM CƯƠNG</h3>
                    <h2>Sự hòa quyện giữa vẻ đẹp tự nhiên và kim cương</h2>
                    <p>Mẫu này sở hữu mặt đồng hồ làm từ xà cừ trắng (Mother of Pearl) tinh khiết, mang lại hiệu ứng ánh sáng thay đổi kỳ ảo và độc bản trên từng chiếc đồng hồ. Nổi bật trên nền mặt số là 8 viên kim cương lấp lánh đóng vai trò là vạch chỉ giờ, kết hợp hài hòa cùng các chữ số La Mã cổ điển và bộ kim được chế tác từ vàng 18 ct sang trọng. Mặt đồng hồ này không còn là công cụ đo đếm thời gian đơn thuần, mà trở thành một tuyệt tác trang sức giúp các quý cô theo dõi thời gian một cách thanh lịch và toát lên khí chất kiêu sa trong mọi hoàn cảnh.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega2_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THIẾT KẾ VỎ SIÊU MỎNG & TINH TẾ</h3>
                    <h2>Vẻ đẹp trang nhã vượt thời gian</h2>
                    <p>Với đường kính chỉ 24.4mm siêu nhỏ gọn, lớp vỏ thép không gỉ được đánh bóng hoàn hảo ôm sát bộ viền bezel vàng 18K rực rỡ nạm kim cương xung quanh. Thiết kế siêu mỏng này giúp chiếc đồng hồ dễ dàng nằm gọn dưới mép áo lụa mỏng manh nhất.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega2_full.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Omega De Ville Prestige Quartz 24.4mm</h2>
                        <p>Omega De Ville Prestige 424.25.24.60.55.001 không chỉ thu hút bởi vẻ ngoài kiêu sa mà còn sở hữu sự chính xác vô cùng tuyệt đối của một món trang sức thời gian. Được trang bị bộ máy Quartz Precision Calibre 1376 do Omega chế tác, chiếc đồng hồ này mang lại độ chính xác hoàn hảo mà không cần người dùng phải bận tâm về việc lên dây cót hay chỉnh giờ thường xuyên. Bộ máy này được thiết kế tối ưu cho sự nhỏ gọn và hoạt động bền bỉ với tuổi thọ pin ấn tượng (khoảng 48 tháng), giúp đồng hồ luôn sẵn sàng đồng hành cùng các quý cô trong mọi sự kiện.</p>
                        <p>Khả năng vận hành êm ái và thiết kế mỏng nhẹ của Omega De Ville Prestige biến nó thành phụ kiện lý tưởng cho các buổi tiệc sang trọng hay môi trường công sở. Không mang nặng các chi tiết kỹ thuật hầm hố như dòng thể thao, chiếc đồng hồ này tập trung vào trải nghiệm đeo nhẹ nhàng, ôm sát cổ tay và tôn vinh nét duyên dáng, nữ tính của người sở hữu.</p>
                        <p>Khả năng chống nước của Omega De Ville Prestige dừng ở mức 30 mét (3 ATM), cho phép bạn yên tâm sử dụng khi rửa tay hoặc đi dưới những cơn mưa nhẹ. Mặt kính sapphire chống trầy xước với độ cứng cao giúp bảo vệ tối đa cho mặt số xà cừ và các viên kim cương giá trị bên trong. Lớp kính này giữ cho đồng hồ luôn sáng bóng và mới mẻ, đảm bảo vẻ đẹp hoàn mỹ theo thời gian.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega_165.webp" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '210.30.44.51.03.002') !== false): ?>
    <section class="product-story-section" style="background-color: #7fa3c5;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>TRẢI NGHIỆM ĐEO & DÂY ĐEO THÉP KHÔNG GỈ</h3>
                    <h2>Sự chắc chắn và linh hoạt tối đa</h2>
                    <p>Phiên bản này đi kèm với dây đeo kim loại bằng thép không gỉ được đánh bóng và chải xước xen kẽ đặc trưng của dòng Seamaster. Được thiết kế để chịu được áp lực dưới biển sâu, dây đeo này mang lại cảm giác đầm tay, chắc chắn nhưng vẫn linh hoạt nhờ cấu trúc mắt dây 5 mảnh.</p>
                    <p>Dây đeo được trang bị khóa gập an toàn với cơ chế giãn nở (diver extension) thông minh, cho phép người đeo dễ dàng nới rộng dây để đeo bên ngoài bộ đồ lặn hoặc điều chỉnh nhanh chóng khi cổ tay thay đổi kích thước do nhiệt độ, đảm bảo sự thoải mái trong mọi hoạt động.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega3_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>GỐM CERAMIC & HỌA TIẾT SÓNG KHẮC LASER</h3>
                    <h2>Vẻ đẹp chiều sâu của đại dương</h2>
                    <p>Mẫu này kết hợp mặt đồng hồ làm từ gốm Ceramic (ZrO2) màu xanh dương, mang lại độ bóng sâu và khả năng chống phai màu tuyệt đối. Nổi bật trên nền mặt số là họa tiết sóng khắc Laser đặc trưng, tạo nên chiều sâu thị giác đầy mê hoặc. Các cọc số nổi được phủ dạ quang Super-LumiNova siêu sáng, đảm bảo khả năng quan sát rõ ràng ngay cả trong vùng nước tối tăm nhất.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega3_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>CHỨC NĂNG CHRONOGRAPH (BẤM GIỜ)</h3>
                    <h2>Công cụ đo đếm thời gian chuyên nghiệp</h2>
                    <p>Mẫu này tích hợp tính năng Chronograph cao cấp với hai mặt số phụ (sub-dials). Tại vị trí 9 giờ là kim giây nhỏ của đồng hồ. Đặc biệt, tại vị trí 3 giờ là bộ đếm kết hợp cả kim giờ và kim phút của chức năng bấm giờ trên cùng một mặt số phụ, giúp việc đọc thời gian trôi qua trực quan và dễ dàng như xem giờ bình thường. Các kim chỉ chức năng bấm giờ được sơn màu đỏ nổi bật, hỗ trợ thao tác nhanh chóng và chính xác.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega3_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DI SẢN ĐẠI DƯƠNG & CÔNG NGHỆ TIÊN TIẾN</h3>
                    <h2>Biểu tượng vượt thời gian của Omega dưới lòng biển</h2>
                    <p>Kể từ khi ra mắt năm 1993, dòng Seamaster Diver 300M đã trở thành biểu tượng của sự bền bỉ và khám phá đại dương, được tin dùng bởi các thợ lặn chuyên nghiệp lẫn những người đam mê phiêu lưu. Phiên bản Chronograph này kế thừa trọn vẹn di sản ấy, kết hợp với các công nghệ hiện đại nhất của Omega: từ bezel ceramic chống xước với scale lặn tráng men, van thoát helium tại vị trí 10 giờ, đến mặt số laser khắc họa tiết sóng nước độc quyền – tạo hiệu ứng chiều sâu sống động như đang nhìn xuống đại dương.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega3_full.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>TÍNH NĂNG ĐỒNG HỒ OMEGA SEAMASTER DIVER 300M CHRONOGRAPH</h2>
                        <p>Omega Seamaster Diver 300M Chronograph không chỉ thu hút bởi vẻ ngoài hầm hố mà còn sở hữu sức mạnh nỗ lực đáng nể. Được trang bị bộ máy Co-Axial Master Chronometer Calibre 9900 do Omega tự phát triển, chiếc đồng hồ này mang lại độ chính xác đỉnh cao và khả năng vận hành ổn định. Bộ máy này sử dụng cơ chế bánh xe cột (column wheel) cho thao tác bấm giờ mượt mà và hai ổ cót nối tiếp giúp dự trữ năng lượng lên đến 60 giờ.</p>
                        <p>Khả năng vận hành chuyên nghiệp biến nó thành công cụ sinh tồn lý tưởng. Đồng hồ được trang bị van thoát khí Heli hình nón tại vị trí 10 giờ, bảo vệ đồng hồ khi lặn bão hòa. Khả năng chống nước lên đến 300 mét (30 Bar), cùng với các nút bấm giờ làm bằng gốm ceramic chống nước, cho phép bạn yên tâm sử dụng trong mọi hoạt động dưới nước.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega-245webp.webp" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '331.20.42.51.02.001') !== false): ?>
    <section class="product-story-section" style="background-color: #ebd4b3;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>TRẢI NGHIỆM ĐEO & DÂY ĐEO TWO-TONE</h3>
                    <h2>Sự cân bằng hoàn hảo giữa sang trọng và thể thao</h2>
                    <p>Phiên bản này đi kèm dây đeo kim loại two-tone (thép không gỉ kết hợp vàng Sedna™ 18K) với các mắt xích chữ H đặc trưng. Dây đeo mang lại cảm giác chắc chắn, sáng bóng, ôm sát cổ tay và tạo điểm nhấn sang trọng. Khóa gập ẩn an toàn giúp dễ dàng đeo và đảm bảo độ bền cao trong sử dụng hàng ngày.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega4_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT SỐ TRẮNG SẠCH & BEZEL VÀNG</h3>
                    <h2>Vẻ đẹp cổ điển vượt thời gian</h2>
                    <p>Mặt số trắng bạc (silver-white) tinh tế, kết hợp các vạch giờ và kim phủ vàng Sedna™ 18K. Bezel tachymeter vàng Sedna™ khắc scale 1000, cho phép đo tốc độ trung bình. Các sub-dial chronograph được bố trí hài hòa: 30 phút tại 3 giờ, 12 giờ tại 6 giờ, small seconds tại 9 giờ. Cửa sổ ngày tại vị trí 6 giờ và toàn bộ được bảo vệ bởi kính sapphire chống xước.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega4_duoicung.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>CHỨC NĂNG CHRONOGRAPH</h3>
                    <h2>Công cụ đo thời gian chuyên nghiệp</h2>
                    <p>Tích hợp chức năng chronograph chính xác, cho phép đo thời gian vòng đua, thời gian phản ứng hoặc các hoạt động cần đo lường chính xác. Kim chronograph trung tâm màu vàng nổi bật, cùng các sub-dial giúp theo dõi thời gian elapsed một cách trực quan và dễ đọc.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega4_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DI SẢN LỊCH SỬ & CÔNG NGHỆ HIỆN ĐẠI</h3>
                    <h2>Chiếc đồng hồ đầu tiên lên Mặt Trăng</h2>
                    <p>Ra mắt từ năm 1957 và là đồng hồ duy nhất được đeo trên Mặt Trăng năm 1969, Speedmaster Moonwatch là biểu tượng bất diệt của Omega. Phiên bản này kết hợp di sản huyền thoại với công nghệ tiên tiến: bộ máy Co-Axial, mặt số và kim phát sáng Super-LumiNova, cùng kích thước vỏ 41.5mm mang lại sự hiện diện mạnh mẽ nhưng vẫn thoải mái khi đeo.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega4_full.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>TÍNH NĂNG ĐỒNG HỒ OMEGA SPEEDMASTER MOONWATCH TWO-TONE</h2>
                        <p>Được trang bị bộ máy Co-Axial Chronograph Calibre 9300 (hoặc tương đương), dự trữ năng lượng khoảng 60 giờ, kháng từ tính chuẩn của Omega. Khả năng chống nước 100 mét (10 bar), mặt kính sapphire chống xước, bảo vệ tối đa mặt số và các chi tiết phát sáng. Chiếc Speedmaster Two-Tone này là sự kết hợp hoàn hảo giữa di sản lịch sử, công nghệ hiện đại và phong cách sang trọng thể thao – dành cho những ai trân trọng giá trị vượt thời gian và đẳng cấp thực thụ.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega-183.webp" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '424.10.37.20.01.001') !== false): ?>
    <section class="product-story-section" style="background-color: #a6a8a7;">
        <div class="story-container">
            <div class="story-block">
                <div class="story-text">
                    <h3>TRẢI NGHIỆM ĐEO & DÂY ĐEO KIM LOẠI THÉP KHÔNG GỈ</h3>
                    <h2>Sự thoải mái và sang trọng như một món trang sức</h2>
                    <p>Phiên bản này đi kèm dây đeo kim loại thép không gỉ với mắt xích Jubilee (hoặc kiểu lưới vuông đặc trưng), mang lại cảm giác mịn màng, sáng bóng và ôm sát cổ tay một cách nhẹ nhàng. Dây đeo được thiết kế bởi Omega để tôn vinh vẻ đẹp tinh tế, kết hợp độ bền cao của thép không gỉ với sự linh hoạt, dễ điều chỉnh. Khóa gập ẩn an toàn (Folding Clasp) giúp đeo tháo dễ dàng mà vẫn giữ được sự thanh lịch liền mạch. Tổng thể mang lại trải nghiệm đeo nhẹ nhàng, thoải mái suốt cả ngày, lý tưởng cho môi trường công sở hay các dịp trang trọng.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega5_daydeo.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>MẶT SỐ ĐEN HUYỀN BÍ & CHỮ LA MÃ</h3>
                    <h2>Vẻ đẹp tối giản, cổ điển và dễ đọc</h2>
                    <p>Mẫu này sở hữu mặt số đen (black dial) sang trọng, được hoàn thiện tinh xảo với các chỉ số giờ La Mã (Roman numerals) tại vị trí 12, 3, 6, 9 giờ, kết hợp các vạch giờ mảnh mạ rhodium. Kim giờ và phút hình lá (leaf hands) bằng thép đánh bóng, cùng kim giây trung tâm mảnh, tạo nên sự cân đối hoàn hảo. Cửa sổ ngày tại vị trí 3 giờ với kính phóng đại (date magnifier), giúp dễ dàng đọc giờ mà không làm mất đi vẻ thanh lịch tổng thể. Toàn bộ được bảo vệ bởi mặt kính sapphire chống xước với lớp phủ chống lóa.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega5_chuyendong.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>DI SẢN THANH LỊCH & CÔNG NGHỆ HIỆN ĐẠI</h3>
                    <h2>Biểu tượng vượt thời gian của dòng De Ville</h2>
                    <p>Dòng De Ville từ lâu đã đại diện cho sự tinh tế và đẳng cấp của Omega, với thiết kế lấy cảm hứng từ những năm 1960-1970 nhưng được nâng cấp bằng công nghệ đương đại. Với kích thước vỏ khoảng 36.8mm, chiếc đồng hồ mang lại sự hiện diện vừa vặn, thanh thoát trên cổ tay nam giới, phù hợp cho cả người yêu thích sự tối giản lẫn những ai tìm kiếm một chiếc đồng hồ dress watch đa năng. Đây không chỉ là công cụ đo thời gian, mà còn là biểu tượng của phong cách tinh tế trong mọi hoàn cảnh.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega5_full.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                <div class="story-text">
                    <h3>THIẾT KẾ VỎ SIÊU MỎNG & TINH TẾ</h3>
                    <h2>Vẻ đẹp trang nhã vượt thời gian</h2>
                    <p>Bộ vỏ được làm bằng thép không gỉ sáng bóng, với đường nét bo cong mềm mại và viền bezel bậc thang đặc trưng của bộ sưu tập De Ville Prestige. Mặt sau đồng hồ khắc dập nổi biểu tượng Chronos - vị thần thời gian, khẳng định chất lượng và di sản của một chiếc đồng hồ Thụy Sỹ đích thực.</p>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega5_matso.png" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
            <div class="story-block">
                 <div class="story-text">
                    <h3>Tính Năng Đặc Biệt</h3>
                    <div class="story-text">
                        <h2>Tính Năng Đồng Hồ Omega De Ville Prestige</h2>
                        <p>Không chỉ là một chiếc đồng hồ trang sức, De Ville Prestige còn sở hữu độ chính xác và bền bỉ vượt trội. Được trang bị bộ máy Co-Axial Chronometer Calibre 2500 (thương hiệu Omega) hoạt động bền bỉ, mang lại độ chính xác cao, dự trữ năng lượng 48 giờ. Khả năng chống nước 30 mét (3 bar), đủ dùng hàng ngày (rửa tay, mưa nhẹ). Mặt kính sapphire chống trầy xước bảo vệ tối đa mặt số và các chi tiết. Chiếc De Ville Prestige này là sự kết hợp hoàn hảo giữa di sản thanh lịch cổ điển, công nghệ hiện đại và phong cách tinh tế – dành cho những ai trân trọng sự sang trọng giản dị và đẳng cấp thực thụ.</p>
                    </div>
                </div>
                <div class="story-img"><img src="../image/chitiet_omega/omega-60webp.webp" onerror="this.src='../<?php echo $row['anh_san_pham']; ?>'"></div>
            </div>
        </div>
    </section>


    <?php elseif (strpos($row['so_tham_chieu'], '131.20.29.20.06.001') !== false): ?>
    <section class="product-story-section" style="background-color: #f3ebd6; padding: 60px 0; color: #333; font-family: 'Segoe UI', Roboto, Arial, sans-serif;">
        <div class="story-container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
            
            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #555;">Sự bền bỉ trong diện mạo lịch lãm</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">KHUNG VỎ THÉP KHÔNG GỈ VỚI THIẾT KẾ AQUA TERRA ĐẶC TRƯNG</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #444;">Với kích thước 38.5mm lý tưởng, bộ vỏ được đúc từ thép không gỉ cao cấp, kết hợp giữa các bề mặt đánh bóng và phay xước tinh xảo. Thiết kế này không chỉ mang lại sự cứng cáp, bảo vệ tuyệt đối cho bộ máy bên trong mà còn giữ được nét thanh thoát, dễ dàng phối hợp cùng trang phục từ thể thao năng động đến Suit công sở trang trọng.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image57a.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px; flex-direction: row-reverse;">
                <div class="story-text" style="flex: 1;">
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; line-height: 1.4;">MẶT SỐ XÀ CỪ ĐEN NẠM KIM CƯƠNG ĐẲNG CẤP</h2>
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; color: #555;">Hiệu ứng thị giác đầy lôi cuốn</h3>
                    <p style="font-size: 15px; line-height: 1.6; color: #444;">Chiếc Aqua Terra này sở hữu mặt số được chế tác từ xà cừ đen (Tahiti Mother-of-pearl) quý hiếm, tạo nên những hiệu ứng vân sắc ảo diệu thay đổi theo từng góc nhìn. Sự sang trọng được đẩy lên tối đa với 11 viên kim cương tinh khiết đính tại các vị trí chỉ giờ, mang lại vẻ rực rỡ và quyền quý cho các quý ông hiện đại.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image57d.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #555;">Sự chính xác tuyệt đối từ Thụy Sĩ</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">BỘ MÁY CO-AXIAL MASTER CHRONOMETER ĐỈNH CAO</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #444;">Đồng hồ vận hành bởi bộ máy Omega Calibre 8508 (hoặc 8500 tùy đời sản xuất), tích hợp công nghệ thoát đồng trục Co-Axial danh tiếng. Bộ máy này không chỉ đảm bảo độ chính xác vượt trội đã qua kiểm định khắt khe mà còn có khả năng kháng từ trường cực mạnh, giúp đồng hồ hoạt động ổn định trong mọi môi trường hiện đại.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image57b.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px; flex-direction: row-reverse;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #555;">Cảm giác ôm sát và tin cậy</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">DÂY ĐEO THÉP BA DẢI MẮT XÍCH CHẮC CHẮN</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #444;">Đi kèm là dây đeo bằng thép không gỉ với thiết kế ba dải mắt xích truyền thống nhưng được hoàn thiện cực kỳ mượt mà. Hệ thống khóa gập tiện lợi không chỉ đảm bảo an toàn tuyệt đối khi vận động mà còn mang lại cảm giác đeo êm ái, thoải mái suốt ngày dài.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image57c.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px;">
                <div class="story-text" style="flex: 1;">
                    <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 10px; line-height: 1.4;">Tính Năng Đồng Hồ Omega Seamaster Aqua Terra</h2>
                    <p style="font-size: 14px; margin-bottom: 15px; color: #444;">Minh chứng cho đẳng cấp dòng Seamaster</p>
                    <p style="font-size: 14px; line-height: 1.6; color: #444;">
                        <strong>Chống nước 150 mét (15 bar):</strong> Thoải mái tham gia các hoạt động bơi lội và lặn nông mà không lo ảnh hưởng đến bộ máy. 
                        <strong>Kính Sapphire nguyên khối:</strong> Khả năng chống trầy xước gần như tuyệt đối với lớp phủ chống phản quang hai mặt. 
                        <strong>Nắp lưng lộ máy:</strong> Cho phép chiêm ngưỡng vẻ đẹp của bộ máy cơ khí với các đường vân sóng Geneva đặc trưng. 
                        <strong>Kim chỉ giờ phủ dạ quang Super-LumiNova:</strong> Hỗ trợ quan sát giờ rõ nét ngay cả trong điều kiện thiếu sáng.
                    </p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/sp9-omeg.png" style="width: 100%; max-width: 300px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

        </div>
    </section>

    <?php elseif (strpos($row['so_tham_chieu'], '434.20.34.20.02.001') !== false): ?>
    <section class="product-story-section" style="background-color: #513629; padding: 60px 0; color: #f5eedc; font-family: 'Segoe UI', Roboto, Arial, sans-serif;">
        <div class="story-container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
            
            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #d8c9b3;">Vẻ Đẹp Cổ Điển Và Nghệ Thuật Quản Lý Thời Gian</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">THIẾT KẾ VỎ TWO-TONE SANG TRỌNG</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #e0d5c1;">Sự kết hợp hoàn hảo giữa Thép và Vàng vàng 18K. Chiếc đồng hồ sở hữu bộ vỏ 39.5mm lịch lãm với vành bezel được chế tác từ Vàng vàng 18K rực rỡ, kết hợp hài hòa cùng thân vỏ bằng thép không gỉ sáng bóng. Đây là sự lựa chọn tinh tế cho những quý ông yêu thích vẻ đẹp truyền thống nhưng vẫn muốn khẳng định đẳng cấp khác biệt qua chất liệu kim loại quý.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image58d.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px; flex-direction: row-reverse;">
                <div class="story-text" style="flex: 1;">
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; line-height: 1.4;">MẶT SỐ VÀNG VÂN "SILK-LIKE" ĐỘC ĐÁO</h2>
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; color: #d8c9b3;">Hiệu ứng thị giác đầy lôi cuốn</h3>
                    <p style="font-size: 15px; line-height: 1.6; color: #e0d5c1;">Điểm nhấn nổi bật nhất chính là mặt số màu vàng được trang trí bằng các họa tiết vân lụa (silk-like pattern) lấy cảm hứng từ các thiết kế De Ville thập niên 60. Các cọc số La Mã xen kẽ các chấm tròn bằng vàng được đính nổi thủ công, tạo nên một bề mặt có chiều sâu và cực kỳ sang trọng dưới mọi góc nhìn.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image58a.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #d8c9b3;">Sự chính xác tuyệt đối từ Thụy Sĩ</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">BỘ MÁY CO-AXIAL CHRONOMETER DANH TIẾNG</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #e0d5c1;">Trái tim của đồng hồ là bộ máy Omega Calibre 2627 tự động, tích hợp bộ thoát đồng trục Co-Axial giúp giảm ma sát và tăng độ bền bỉ. Bộ máy đã vượt qua các bài kiểm tra khắt khe để đạt chứng nhận Chronometer, cam kết sai số cực thấp và hiệu suất vận hành ổn định qua hàng thập kỷ.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image58b.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px; margin-bottom: 60px; flex-direction: row-reverse;">
                <div class="story-text" style="flex: 1;">
                    <h3 style="font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #d8c9b3;">Nét cổ điển cho quý ông thành đạt</h3>
                    <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; line-height: 1.4;">DÂY ĐEO DA CÁ SẤU NÂU LỊCH LÃM</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #e0d5c1;">Đồng hồ đi kèm với dây đeo da cá sấu màu nâu cao cấp, mang lại vẻ ngoài ấm áp và vô cùng sang trọng. Chất liệu da mềm mại không chỉ tôn lên vẻ đẹp của vỏ vàng 18K mà còn đảm bảo sự thoải mái tuyệt đối khi phối cùng các bộ Suit hay trang phục công sở lịch thiệp.</p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
                    <img src="../image/chitiet_omega/image58c.png" style="width: 100%; max-width: 400px; border-radius: 8px;" onerror="this.src='../<?php echo trim($row['anh_san_pham']); ?>'">
                </div>
            </div>

            <div class="story-block" style="display: flex; align-items: center; gap: 40px;">
                <div class="story-text" style="flex: 1;">
                    <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 10px; line-height: 1.4;">Tính Năng Đồng Hồ Omega De Ville Prestige</h2>
                    <p style="font-size: 14px; margin-bottom: 15px; color: #e0d5c1;">Minh chứng cho đẳng cấp nghệ nhân Omega</p>
                    <p style="font-size: 14px; line-height: 1.6; color: #e0d5c1;">
                        <strong>Kính Sapphire chống trầy xước:</strong> Được xử lý chống phản quang bên trong để quan sát mặt số rõ nét nhất. 
                        <strong>Hiển thị ngày tiện lợi:</strong> Ô lịch ngày tại vị trí 3 giờ được thiết kế sắc nét, hỗ trợ tối đa cho công việc hàng ngày. 
                        <strong>Kim giây nhỏ (Small Seconds):</strong> Mặt số phụ hiển thị kim giây tại vị trí 9 giờ tạo nên sự cân bằng hoàn hảo cho tổng thể mặt số. 
                        <strong>Chống nước 30 mét (3 bar):</strong> Đảm bảo an toàn khi rửa tay hoặc đi mưa nhẹ trong sinh hoạt hàng ngày.
                    </p>
                </div>
                <div class="story-img" style="flex: 1; text-align: center;">
    <img src="<?php echo show_img_url('../image/chitiet_omega/image58d.png'); ?>" 
         style="width: 100%; max-width: 300px;" 
         alt="Câu chuyện Omega" 
         onerror="this.onerror=null; this.style.display='none';">
</div>
            </div>

        </div>
    </section>

   




    <?php endif; ?>

    <section class="bottom-info-section">
        <h3 class="cert-title">Chứng nhận</h3>
        <p class="cert-desc">Master Chronometer (chứng nhận bởi METAS về độ chính xác, hiệu suất và kháng từ)</p>

        <h3 class="pub-title" style="text-align: center;">Ấn phẩm</h3>
        <a href="#" class="download-link"><i class="fa-solid fa-download"></i> Tải ấn phẩm</a>
        
    <?php
        $anpham_img = 'omega_default_anpham.jpg'; 
        
        if (strpos($row['so_tham_chieu'], '210.32.42.20.04.001') !== false) {
            $anpham_img = 'omega1_anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '424.25.24.60.55.001') !== false) {
            $anpham_img = 'omega2_anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '210.30.44.51.03.002') !== false) {
            $anpham_img = 'omega3_anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '331.20.42.51.02.001') !== false) {
            $anpham_img = 'omega4_anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '424.10.37.20.01.001') !== false) {
            $anpham_img = 'omega5_anpham.png'; 
        } elseif (strpos($row['so_tham_chieu'], '131.20.29.20.06.001') !== false) {
            $anpham_img = 'omega-anpham6.jpg'; 
        } elseif (strpos($row['so_tham_chieu'], '434.20.34.20.02.001') !== false) {
            $anpham_img = 'omega-anpham7.jpg'; 
        }
        
        ?>
        <img src="<?php echo show_img_url('../image/chitiet_omega/' . $anpham_img); ?>" 
     alt="Ấn phẩm Omega" 
     class="publication-img" 
     onerror="this.onerror=null; this.style.display='none';">
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
            <div class="footer-logo"><img src="../image/logo.png" alt="Timeless" onerror="this.onerror=null; this.src='../image/no-image.png';"></div>
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
                <img src="<?php echo $anh_chinh; ?>" alt="Omega Mini" onerror="this.onerror=null; this.src='../image/no-image.png';">
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

<!-- deleteModal đã có ở trên (L873), không cần khai báo lại -->

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
    <script>
function changeImage(param) {
    let mainImg = document.getElementById('main-product-img');
    let thumbs = document.querySelectorAll('.thumb');
    if (!mainImg || thumbs.length === 0) return;

    // 1. Gỡ bỏ viền nâu (active) ở TẤT CẢ các ảnh nhỏ
    thumbs.forEach(img => img.classList.remove('active'));

    // 2. Cập nhật ảnh lớn VÀ bật viền nâu cho ảnh vừa được click
    if (typeof param === 'object' && param.src) {
        // Nếu truyền `this`
        mainImg.src = param.src;
        param.classList.add('active');
    } 
    else if (typeof param === 'number') {
        // Nếu truyền số thứ tự (0, 1, 2...)
        if (thumbs[param]) {
            mainImg.src = thumbs[param].src;
            thumbs[param].classList.add('active');
        }
    } 
    else if (typeof param === 'string') {
        // Nếu truyền chuỗi link (this.src)
        mainImg.src = param;
        thumbs.forEach(img => {
            if (img.src === param) img.classList.add('active');
        });
    }
}
</script>
    <?php include '../thongbao.php'; ?>

<?php
include '../ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>