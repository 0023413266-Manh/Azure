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
    $uid = intval($_SESSION['user_id']);
    $check_fav = $conn->query("SELECT id FROM yeu_thich WHERE id_nguoi_dung = $uid AND id_san_pham = $id");
    if ($check_fav && $check_fav->num_rows > 0) {
        $is_favorited = true;
    }
}

// 5. KHAI BÁO BIẾN ĐƯỜNG DẪN LÙI VỀ THƯ MỤC GỐC & CSS RIÊNG
$path_prefix = '../'; 
$custom_css = 'chi_tiet.css';

include $path_prefix . 'header.php';

// 6. XỬ LÝ CHUẨN ĐƯỜNG DẪN ẢNH CHÍNH
$raw_img = trim($row['anh_san_pham'] ?? '');
if (!empty($raw_img) && strpos($raw_img, 'http') === 0) {
    $anh_chinh = $raw_img;
} else {
    $anh_chinh = (strpos($raw_img, '../') === 0) ? $raw_img : '../' . $raw_img;
}
?>

<div style="background-color: #f9f9f9; padding: 30px 0;">
    <div class="product-detail-container" style="max-width: 1200px; margin: 0 auto; display: flex; gap: 30px; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <!-- 🖼️ KHU VỰC BÊN TRÁI: ALBUM ẢNH -->
        <div class="product-gallery" style="flex: 1; border:none; padding: 0;">
            
            <div class="main-image-container" style="position: relative; text-align: center; margin-bottom: 15px;">
                <div class="gallery-nav">
                    <i class="fa-solid fa-chevron-left" id="prev-btn"></i>
                    <i class="fa-solid fa-chevron-right" id="next-btn"></i>
                </div>
                <img id="main-product-img" src="<?php echo $anh_chinh; ?>" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>" style="max-width: 380px; width: 100%; border-radius: 8px;" onerror="this.onerror=null; this.src='../image/no-image.png';">
            </div>

            <div class="thumbnail-slider" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <!-- Thumbnail 0: Ảnh chính sản phẩm -->
                <img src="<?php echo $anh_chinh; ?>" class="thumb active" onclick="changeImage(this)" onerror="this.onerror=null; this.src='../image/no-image.png';" alt="Ảnh chính">

                <?php
                $gallery = [
                    45 => ['seiko1-1.png', 'seiko1-2.png', 'seiko1-3.png', 'seiko1-4.png', 'seiko-anpham.png'],
                    46 => ['seiko2-1.png', 'seiko2-2.png', 'seiko2-3.png', 'seiko2-4.png', 'seiko-anpham.png'],
                    47 => ['seiko3-1.png', 'seiko3-2.png', 'seiko3-3.png', 'seiko3-4.png', 'seiko-anpham.png'],
                    48 => ['seiko4-1.png', 'seiko4-2.png', 'seiko4-3.png', 'seiko4-4.png', 'seiko-anpham.png'],
                    49 => ['seiko5-1.png', 'seiko5-2.png', 'seiko5-3.png', 'seiko5-4.png', 'seiko-anpham.png'],
                    50 => ['seiko6-1.png', 'seiko6-2.png', 'seiko6-3.png', 'seiko6-4.png', 'seiko-anpham.png'],
                    51 => ['seiko7-1.png', 'seiko7-2.png', 'seiko7-3.png', 'seiko7-4.png', 'seiko-anpham.png'],
                    52 => ['seiko8-1.png', 'seiko8-2.png', 'seiko8-3.png', 'seiko8-4.png', 'seiko-anpham.png'],
                ];

                $current_id = $row['id'];

                if (isset($gallery[$current_id])) {
                    foreach ($gallery[$current_id] as $file_name) {
                        echo '<img src="../image/chitiet_seiko/' . htmlspecialchars($file_name) . '" class="thumb" onclick="changeImage(this)" onerror="this.onerror=null; this.src=\'../image/no-image.png\';" alt="Ảnh chi tiết">';
                    }
                }
                ?>
            </div>

            <?php
            $content_map = [
                'Seiko 42.4mm Nam SRPD25K1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SRPD25K1 được trang bị cửa sổ lịch ngày – thứ tại vị trí 3 giờ, giúp người dùng dễ dàng theo dõi thông tin thời gian trong sinh hoạt hằng ngày. Chức năng này được vận hành bởi bộ máy cơ tự động Seiko 4R36, nổi tiếng với độ bền bỉ, ổn định và khả năng lên cót tay tiện lợi.',
                    'color' => '#0c6a3f'
                ],
                'Seiko 39.5mm Nam SRPK17K1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko 5 Sports SRPK17K1 – 39.5mm Thiết kế thể thao cổ điển đặc trưng Seiko 5 Sports, SRPK17K1 sở hữu ô hiển thị Day–Date tại vị trí 3 giờ, giúp người dùng dễ dàng theo dõi ngày và thứ trong sinh hoạt hằng ngày.',
                    'color' => '#000'
                ],
                'Seiko 41.7mm Nam SRPD41J1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SRPD41J1 được trang bị ô lịch ngày hiển thị rõ ràng tại vị trí 3 giờ, mang lại sự tiện dụng cao trong sinh hoạt hằng ngày.',
                    'color' => '#a88d34'
                ],
                'Seiko Prospex Samurai 43.8mm Nam SRPE33K1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko Prospex Samurai SRPE33K1 được trang bị ô lịch ngày đặt tại vị trí 3 giờ, hiển thị rõ ràng, dễ quan sát trong mọi điều kiện.',
                    'color' => '#6b1823'
                ],
                'Seiko Prospex Samurai 40.2mm Nam SARX123' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SARX123 được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị rõ ràng và cân đối trên mặt số mang họa tiết tinh xảo đặc trưng Presage.',
                    'color' => '#602a2e'
                ],
                'Seiko 43.8mm Nam SRPE37J' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SRPE37J được trang bị cửa sổ lịch ngày tại vị trí 3 giờ, hiển thị rõ ràng, dễ quan sát trong mọi điều kiện sử dụng.',
                    'color' => '#5f4a05'
                ],
                'Seiko 39mm Nam SSC819P1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SSC819P1 thuộc bộ sưu tập Prospex Speedtimer, lấy cảm hứng từ những mẫu chronograph thể thao huyền thoại của Seiko.',
                    'color' => '#15844b'
                ],
                'Seiko 43.8mm Nam SRPG21J1' => [
                    'title' => 'NỔI BẬT VỚI KHẢ NĂNG HIỂN THỊ NGÀY',
                    'desc'  => 'Seiko SRPG21J1 được trang bị ô lịch ngày và thứ tại vị trí 3 giờ, bố cục gọn gàng, dễ quan sát.',
                    'color' => '#245f8c'
                ],
            ];

            $current_content = [
                'title' => 'CHI TIẾT SẢN PHẨM',
                'desc'  => 'Thông tin sản phẩm đang được cập nhật.',
                'color' => '#333'
            ];

            foreach ($content_map as $key => $value) {
                if (strpos($row['ten_san_pham'], trim($key)) !== false) {
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
            45 => [ 'seiko1-1.png', 'seiko1-2.png', 'seiko1-3.png', 'seiko1-4.png', 'seiko-anpham.png'],
            46 => [ 'seiko2-1.png', 'seiko2-2.png', 'seiko2-3.png', 'seiko2-4.png', 'seiko-anpham.png'],
            47 => [ 'seiko3-1.png', 'seiko3-2.png', 'seiko3-3.png', 'seiko3-4.png', 'seiko-anpham.png'],
             48 => [ 'seiko4-1.png', 'seiko4-2.png', 'seiko4-3.png', 'seiko4-4.png', 'seiko-anpham.png'],
              49 => [ 'seiko5-1.png', 'seiko5-2.png', 'seiko5-3.png', 'seiko5-4.png', 'seiko-anpham.png'],
               50 => ['seiko6-1.png', 'seiko6-2.png', 'seiko6-3.png', 'seiko6-4.png', 'seiko-anpham.png'],
                51 => [ 'seiko7-1.png', 'seiko7-2.png', 'seiko7-3.png', 'seiko7-4.png', 'seiko-anpham.png'],
                 52 => [ 'seiko8-1.png', 'seiko8-2.png', 'seiko8-3.png', 'seiko8-4.png', 'seiko-anpham.png'],
            // Bạn cứ thêm 4 => [...], 5 => [...] cho đến 12 ở đây
        ];

        // 2. Lấy ID hiện tại
        $current_id = $row['id'];
        $folder = ($row['id_thuong_hieu'] == 5) ? "chitiet_seiko" : "chitiet_rolex"  ;

        // 3. Tạo mảng ảnh hoàn chỉnh
        $js_images = [$anh_chinh]; // Ảnh chính luôn ở đầu (index 0)

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

            function changeImage(param) {
                let index;

                // Trường hợp 1: click trực tiếp vào ảnh thumbnail (truyền `this`)
                if (typeof param === 'object' && param !== null && param.src) {
                    index = Array.from(thumbs).indexOf(param);
                }
                // Trường hợp 2: bấm nút prev/next (truyền số thứ tự)
                else if (typeof param === 'number') {
                    index = param;
                } else {
                    return;
                }

                if (index < 0 || index >= images.length) return;

                currentIndex = index;
                mainImg.style.opacity = 0.8;

                setTimeout(() => {
                    mainImg.src = images[currentIndex];
                    mainImg.onerror = function() {
                        this.onerror = null;
                        this.src = '../image/no-image.png';
                    };
                    mainImg.style.opacity = 1;
                }, 100);

                // Cập nhật trạng thái active cho ảnh nhỏ
                thumbs.forEach((thumb, i) => {
                    if (i === currentIndex) thumb.classList.add('active');
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
                <a href="../all_rolex.php" style="text-decoration: none; color: #666;">Seiko</a> 
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
                    <strong>Bảo hành:</strong> 3 năm quốc tế Seiko & Bảo hành trọn đời tại Timeless.
                </p>
                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <strong>Địa điểm:</strong> Bảo hiểm hàng hóa 100% & Miễn phí vận chuyển toàn quốc.
                </p>
            </div>



                <?php
                    // Định nghĩa thông số cho từng sản phẩm dựa trên ID trong Database
                    $all_specs = [
                        45 => [ // ID = 1 (Rolex Datejust 31)
                            'Bộ máy' => ' Automatic Caliber 4R36 (lên cót tay, hacking second)',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '42.4 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        46 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => '  Automatic Caliber 4R36',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '39.5 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '100 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        47 => [ // ID = 2 (Rolex Submariner 41)
                            'Bộ máy' => ' Automatic Caliber 4R36',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '39.5 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        48 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Automatic Caliber 4R35',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '43.8 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        49 => [ // ID = 2 (Rolex Submariner 41)
                            'Bộ máy' => ' Automatic Caliber 6R55 (trữ cót 72 giờ)',
                            'Kính' => 'Sapphire Crystal',
                            'Đường kính' => '40.2 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '100 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        50 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => 'Automatic Caliber 4R36',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '43.8 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '200 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        51 => [ // ID = 2 (Rolex Submariner 41)
                          'Bộ máy' => ' Solar Quartz Caliber V192 (Chronograph)',
                            'Kính' => 'Sapphire Crystal',
                            'Đường kính' => '39 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
                            'Độ chịu nước' => '100 m (ISO Diver’s)',
                            'Xuất xứ' => 'Nhật Bản '
                        ],
                        52 => [ // ID = 2 (Rolex Submariner 41)
                           'Bộ máy' => ' Automatic Caliber 4R36',
                            'Kính' => 'Hardlex Crystal',
                            'Đường kính' => '43.2 mm',
                            'Chất liệu vỏ' => 'Thép',
                            'Dây đeo' => 'Thép Không Gỉ',
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
    45 => [
        "bg" => "#e9f2ae",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ BỀN BỈ",
                "h2" => "Thiết kế thể thao mang DNA Seiko 5 Sports",
                "content" => [
                    "Sản phẩm  khẳng định đẳng cấp với bộ vỏ ceramic xanh công nghệ cao, được sản xuất thông qua quy trình nung ở nhiệt độ cực cao, tạo nên kết cấu siêu cứng, chống trầy xước vượt trội và giữ màu bền bỉ theo thời gian.",
                    "Đồng hồ sở hữu bộ vỏ thép không gỉ cao cấp với đường kính 42.4mm, mang lại vẻ ngoài mạnh mẽ, nam tính và chắc chắn. Kiểu dáng đậm chất thể thao phù hợp với nhiều phong cách, từ thường ngày đến năng động."
                ],
                "img" => "../image/no-image.png"
            ],
            [
                "h3" => "VÀNH BEZEL XOAY MỘT CHIỀU",
                "h2" => "Cảm hứng đồng hồ lặn thể thao",
                "content" => [
                    "Vành bezel xoay một chiều được thiết kế rõ nét, hỗ trợ theo dõi thời gian linh hoạt và tăng tính thể thao cho tổng thể. Các chi tiết hoàn thiện tỉ mỉ giúp đồng hồ vừa bền bỉ vừa dễ sử dụng.",
                    "Kết hợp với đó là dây đeo mang đến cảm giác đeo thoải mái ngay cả khi sử dụng trong thời gian dài. Sự hòa quyện giữa vẻ đẹp thể thao mạnh mẽ và tính sang trọng hiện đại giúp chiếc đồng hồ phù hợp cả trong môi trường công sở lẫn các hoạt động thường ngày năng động."
                ],
                "img" => "../image/chitiet_seiko/seiko1-2.png"
            ],
             [
                "h3" => "MẶT SỐ DỄ QUAN SÁT",
                "h2" => "Rõ ràng trong mọi điều kiện ánh sáng",
                "content" => [
                    "Mặt số được trang bị kim và cọc số phủ dạ quang LumiBrite, giúp quan sát thời gian rõ ràng ngay cả trong điều kiện thiếu sáng. Thiết kế mặt số cân đối, trực quan và dễ đọc.",
                    "Các chi tiết như nút bấm chronograph, khóa dây và vít bezel được làm từ titanium nhẹ và siêu bền, gia công chính xác đến từng micromet, đảm bảo khả năng bảo vệ tối ưu cho bộ máy bên trong trước các tác động từ môi trường. Toàn bộ cấu trúc thể hiện sự bền bỉ, ổn định và đẳng cấp – xứng tầm một cỗ máy thời gian xa xỉ dành cho những người yêu công nghệ chế tác Thụy Sĩ."
                ],
                "img" => "../image/chitiet_seiko/seiko1-3.png"
            ],
             [
                "h3" => "DÂY ĐEO THOẢI MÁI",
                "h2" => "Sự thoải mái phù hợp đeo hằng ngày",
                "content" => [
                    "Seiko SRPD25K1 sử dụng dây cao su thể thao, nhẹ và linh hoạt, mang lại cảm giác đeo êm ái, ôm cổ tay và phù hợp cho các hoạt động thường ngày."
                ],
                "img" => "../image/chitiet_seiko/seiko1-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – SEIKO 5 SPORTS SRPD25K1",
                "h2" => "Hiệu năng bền bỉ – Tinh thần thể thao đặc trưng Seiko",
                "content" => [
                    "Không chỉ gây ấn tượng bởi thiết kế thể thao mạnh mẽ, Seiko 5 Sports SRPD25K1 còn sở hữu những tính năng nổi bật, đáp ứng tốt nhu cầu sử dụng hằng ngày:",
                    "Khả năng chống nước 100m (10 ATM): Phù hợp cho sinh hoạt thường ngày, đi mưa và bơi nhẹ.",
                    "Bộ máy cơ tự động Seiko Caliber 4R36: Vận hành ổn định, bền bỉ, tích hợp lên cót tay và dừng kim giây.",
                    "Lịch ngày – thứ tại vị trí 3 giờ: Hiển thị rõ ràng, tiện dụng cho sử dụng hàng ngày.",
                    "Kính Hardlex Crystal: Chống trầy xước tốt, đảm bảo độ trong suốt khi quan sát mặt số.",
                    "Kim và cọc số phủ LumiBrite: Hỗ trợ xem giờ rõ ràng trong điều kiện thiếu sáng."
                ],
                "img" => "../image/sp1-seiko.png"
            ]
        ]
    ],

    // SP 2
    46 => [
        "bg" => "#cfb1b1",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ – THIẾT KẾ GỌN GÀNG, BỀN BỈ",
                "h2" => "Sự kết hợp hài hòa giữa sang trọng và thể thao",
                "content" => [
                    "Vỏ đồng hồ được chế tác từ thép không gỉ cao cấp, hoàn thiện tinh tế, mang lại độ bền cao và khả năng chống ăn mòn tốt.  Kích thước 39.5mm nhỏ gọn, cân đối, phù hợp với nhiều cổ tay nam châu Á, đồng thời giữ được nét thể thao năng động đặc trưng của dòng Seiko 5 Sports."
                ],
                "img" => "../image/chitiet_seiko/seiko2-1.png"
            ],
            [
                "h3" => "VÀNH BEZEL XOAY ĐƠN HƯỚNG – PHONG CÁCH THỂ THAO ĐẶC TRƯNGT",
                "h2" => "Thiết kế biểu tượng – cảm giác đeo tối ưu",
                "content" => [
                    "SRPK17K1 được trang bị vành bezel xoay đơn hướng, thiết kế lấy cảm hứng từ đồng hồ lặn, giúp tăng tính thể thao và khả năng sử dụng thực tế. Các vạch chia rõ ràng, dễ quan sát, tạo điểm nhấn mạnh mẽ cho tổng thể mặt số., các chi tiết như ốc vít hình chữ H đặc trưng và núm vặn đều được gia công tỉ mỉ từ Titanium và nhựa composite đen. Đây là minh chứng cho triết lý 'The Art of Fusion' (Nghệ thuật của sự kết hợp), nơi những vật liệu truyền thống và hiện đại hòa quyện hoàn hảo."
                ],
                "img" => "../image/chitiet_seiko/seiko2-2.png"
            ],
            [
                "h3" => "MẶT KÍNH HARDLEX – ĐỘ BỀN CAO TRONG SỬ DỤNG HẰNG NGÀY",
                "h2" => "Trải nghiệm kỹ thuật số đỉnh cao",
                "content" => [
                    "Seiko SRPK17K1 sử dụng kính Hardlex độc quyền của Seiko, có khả năng chống va đập tốt hơn kính khoáng thông thường, phù hợp cho nhu cầu đeo hằng ngày và các hoạt động ngoài trời.Trải nghiệm kỹ thuật số đỉnh cao Ẩn sau mặt kính Sapphire chống trầy xước là màn hình AMOLED độ nét cao. Người dùng có thể tùy biến các mặt đồng hồ độc quyền của Hublot, từ các thiết kế skeleton biểu tượng đến các tính năng theo dõi sức khỏe hiện đại, mang lại sự linh hoạt tối đa cho mọi sự kiện."
                ],
                "img" => "../image/chitiet_seiko/seiko2-3.png"
            ],
            [
                "h3" => "DÂY ĐEO THÉP KHÔNG GỈ – CHẮC CHẮN & THOẢI MÁI",
                "h2" => "Sự thoải mái tối ưu trên cổ tay",
                "content" => [
                    "Dây đeo thép không gỉ thiết kế thể thao, chắc chắn, ôm cổ tay tốt. Khóa gập an toàn, dễ thao tác, mang lại cảm giác yên tâm và thoải mái khi sử dụng lâu dài.",
                    "Biến hóa phong cách trong tích tắc Sở hữu hệ thống thay dây One Click độc quyền của Hublot, bạn có thể dễ dàng thay đổi diện mạo đồng hồ chỉ bằng một nút bấm. Kết hợp cùng dây cao su trắng cao cấp có cấu trúc sọc dọc, đồng hồ mang lại cảm giác năng động, trẻ trung nhưng vẫn giữ trọn nét quý phái."
                ],
                "img" => "../image/chitiet_seiko/seiko2-4.png"
            ],
            [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO 5 SPORTS SRPK17K1",
                "h2" => "Phong cách thể thao xa xỉ",
                "content" => [
                    "Sự kết hợp hài hòa giữa độ bền, tính thực dụng và cơ khí Nhật Bản:",
                    "Chống nước 100 mét (10 ATM): Phù hợp sinh hoạt hằng ngày, đi mưa, rửa tay và bơi nhẹ",
                    "Bộ máy cơ Automatic 4R36 In-house: Chính xác, bền bỉ, đáng tin cậy",
                    "Dạ quang LumiBrite trên kim và cọc số: Dễ quan sát trong điều kiện thiếu sáng",
                    "Vành bezel xoay đơn hướng: Tăng tính thể thao và khả năng ứng dụng",
                    "Hiển thị Day–Date: Tiện lợi, rõ ràng"
                ],
                "img" => "../image/sp2-seiko.png"
            ]
        ]
    ],
    //SP 3
    47 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ – HOÀN THIỆN CHẮC CHẮN",
                "h2" => "Bền bỉ – chuẩn mực phong cách Nhật Bản",
                "content" => [
                    "Vỏ đồng hồ được chế tác từ thép không gỉ cao cấp, hoàn thiện tỉ mỉ với độ bền cao và khả năng chống ăn mòn tốt. Đường kính 41.7mm mang lại cảm giác cân đối trên cổ tay nam giới, phù hợp cả khi đeo thường ngày lẫn trong các hoạt động năng động. Thiết kế tổng thể gọn gàng, chắc chắn, đúng tinh thần “Everyday Watch” của Seiko. Cấu trúc này mang lại độ bền vượt trội, khả năng chống va đập cao và vẻ đẹp sang trọng mang đậm DNA Ferrari."
                ],
                "img" => "../image/chitiet_seiko/seiko3-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL XOAY MỘT CHIỀU – PHONG CÁCH THỂ THAO",
                "h2" => "Dấu ấn Seiko 5 Sports",
                "content" => [
                    "Seiko SRPD41J1 sở hữu vành bezel xoay một chiều đặc trưng của đồng hồ thể thao, hỗ trợ theo dõi thời gian linh hoạt trong các hoạt động thường nhật. Thiết kế bezel khỏe khoắn, kết hợp hài hòa với mặt số xanh và bộ kim mạnh mẽ, tạo nên diện mạo năng động nhưng vẫn lịch lãm. Kết hợp cùng dây cao su tự nhiên cao cấp, mang lại cảm giác đeo êm ái, linh hoạt và đậm chất thể thao Ferrari."
                ],
                "img" => "../image/chitiet_seiko/seiko3-2.png"
            ],
             [
                "h3" => "CHẾ TÁC CƠ KHÍ TINH TẾ – MADE IN JAPAN",
                "h2" => "Độ tin cậy đã được khẳng định",
                "content" => [
                    "Trái tim của Seiko SRPD41J1 là bộ máy cơ tự động Seiko Caliber 4R36, hoạt động ổn định với khả năng lên cót tay và dừng kim giây (hacking stop). Cấu trúc máy bền bỉ, dễ bảo dưỡng, thể hiện triết lý chế tác chú trọng độ tin cậy và giá trị sử dụng lâu dài – một trong những nền tảng làm nên danh tiếng của Seiko.",
                    "Các chi tiết như nút bấm chronograph, khóa dây và vỏ phụ được chế tác từ Titanium, tối ưu trọng lượng và độ bền. Tổng thể cấu trúc thể hiện trình độ chế tác cao cấp và triết lý “Art of Fusion” đặc trưng Hublot."

                ],
                "img" => "../image/chitiet_seiko/seiko3-3.png"
            ],
             [
                "h3" => "DÂY THÉP KHÔNG GỈ CAO CẤP",
                "h2" => "Thoải mái – chắc chắn trên cổ tay",
                "content" => [
                    "Dây đeo thép không gỉ được thiết kế chắc chắn, ôm tay và mang lại cảm giác đeo thoải mái trong suốt thời gian dài. Khóa gập an toàn giúp cố định đồng hồ ổn định khi vận động, đồng thời tăng tính tiện dụng trong quá trình sử dụng hằng ngày.
 Bề mặt xử lý tinh tế giúp hạn chế mồ hôi, đảm bảo sự thoải mái và ổn định trong mọi điều kiện sử dụng."
                ],
                "img" => "../image/chitiet_seiko/seiko3-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO 5 SPORTS SRPD41J1",
                "h2" => "Sự cân bằng hoàn hảo giữa thể thao và thực dụng",
                "content" => [
                    "Seiko 5 Sports SRPD41J1 sở hữu đầy đủ những tính năng cốt lõi của một mẫu đồng hồ cơ thể thao đáng tin cậy:",
                    "Khả năng chống nước 100 mét (10 ATM): Phù hợp cho sinh hoạt hằng ngày, đi mưa và bơi nhẹ.",
                    "Bộ máy cơ tự động Seiko 4R36: Vận hành ổn định, bền bỉ, có lên cót tay và dừng kim giây.",
                    "Kính Hardlex độc quyền Seiko: Chống va đập tốt, phù hợp sử dụng thường xuyên.",
                    "Dạ quang Lumibrite trên kim và cọc số: Giúp xem giờ rõ ràng trong điều kiện thiếu sáng.",
                    "Núm vặn đặt tại vị trí 4 giờ: Tăng sự thoải mái khi đeo và giữ nét đặc trưng của Seiko 5."
                ],
                "img" => "../image/sp3-seiko.png"
            ],
        ]
    ],

     //SP 4
    48 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ & THIẾT KẾ DIVER CHUYÊN NGHIỆP",
                "h2" => "Tinh thần đồng hồ lặn đích thực của Seiko Prospex.",
                "content" => [
                    "Vỏ đồng hồ chế tác từ thép không gỉ cao cấp, đường kính 43.8mm, nổi bật với thiết kế góc cạnh “Samurai” mạnh mẽ. Kiểu dáng khỏe khoắn, chắc chắn, tối ưu cho khả năng chịu lực và độ bền khi sử dụng trong môi trường khắc nghiệt. Sự kết hợp giữa các bề mặt đánh bóng gương và phay xước tỉ mỉ không chỉ tạo nên vẻ ngoài lộng lẫy mà còn khẳng định giá trị vĩnh cửu của một món trang sức cao cấp."
                ],
                "img" => "../image/chitiet_seiko/seiko4-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL XOAY MỘT CHIỀU & DÂY THÉP KHÔNG GỈ",
                "h2" => "Công cụ lặn chuẩn mực, tính ứng dụng cao.",
                "content" => [
                    "Vành bezel xoay một chiều giúp theo dõi thời gian lặn an toàn. Dây thép không gỉ liền khối, hoàn thiện chắc chắn, mang lại cảm giác đầm tay, bền bỉ và phù hợp cho cả hoạt động thể thao lẫn sử dụng hằng ngày. Kỹ thuật nạm đá thủ công bậc thầy của Hublot giúp các viên kim cương bắt sáng tối đa, bao quanh mặt số trắng thanh khiết để tạo nên một tổng thể xa hoa và đầy nữ tính."
                ],
                "img" => "../image/chitiet_seiko/seiko4-2.png"
            ],
             [
                "h3" => "BỘ MÁY CƠ TỰ ĐỘNG SEIKO CAL. 4R35",
                "h2" => "Độ tin cậy Nhật Bản – bền bỉ theo thời gian.",
                "content" => [
                    " Seiko SRPE33K1 sử dụng bộ máy cơ tự động Caliber 4R35, có khả năng lên cót tay và dừng kim giây khi chỉnh giờ. Bộ máy vận hành ổn định, chính xác, thể hiện triết lý chế tác thực dụng và bền bỉ của Seiko. Mặt số trở nên thanh thoát hơn khi không có các mặt số phụ chronograph, tập trung hoàn toàn vào vẻ đẹp của sự cân đối và dễ dàng quan sát thời gian."
                ],
                "img" => "../image/chitiet_seiko/seiko4-3.png"
            ],
             [
                "h3" => "KÍNH HARDLEX & KHẢ NĂNG CHỐNG NƯỚC 200M",
                "h2" => "Sẵn sàng cho mọi thử thách dưới nước.",
                "content" => [
                    "Mặt kính Hardlex độc quyền của Seiko có khả năng chịu va đập tốt. Đồng hồ đạt chuẩn Diver’s 200m (20 ATM), phù hợp cho bơi lội, lặn biển và các hoạt động thể thao dưới nước chuyên nghiệp."
                ],
                "img" => "../image/chitiet_seiko/seiko4-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO PROSPEX SAMURAI SRPE33K1",
                "h2" => "Phong cách thể thao xa xỉ",
                "content" => [
                    "Sự hội tụ của nghệ thuật kim hoàn và độ bền. Sự kết hợp giữa độ bền, hiệu năng và thiết kế thể thao mạnh mẽ:",
                    "Chuẩn Diver’s 200m – phù hợp lặn biển chuyên nghiệp",
                    "Bộ máy cơ tự động Seiko 4R35, ổn định và bền bỉ",
                    "Dạ quang Lumibrite trên kim & cọc số",
                    "Bezel xoay một chiều an toàn khi lặn",
                    "Núm vặn screw-down tăng khả năng chống nước"

                ],
                "img" => "../image/sp4-seiko.png"
            ],
        ]
    ],

 //SP 5
    49 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ CAO CẤP",
                "h2" => "Sự tinh xảo trong từng đường nét chế tác Nhật Bản",
                "content" => [
                    "Mẫu SARX123 sở hữu bộ vỏ thép không gỉ cao cấp, hoàn thiện tỉ mỉ với các bề mặt chải xước và đánh bóng đan xen, tạo chiều sâu thị giác sang trọng. Đường kính 40.2mm lý tưởng, ôm tay gọn gàng, phù hợp nhiều cỡ cổ tay nam giới hiện đại, vừa lịch lãm nơi công sở vừa linh hoạt trong đời sống thường nhật."
                ],
                "img" => "../image/chitiet_seiko/seiko5-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL & MẶT KÍNH SAPPHIRE",
                "h2" => "Tối ưu độ bền và khả năng quan sát",
                "content" => [
                    "Vành bezel được xử lý gọn gàng, hài hòa với tổng thể thiết kế Sharp Edged. Mặt kính sapphire cao cấp phủ lớp chống phản xạ, giúp quan sát mặt số rõ nét dưới nhiều điều kiện ánh sáng, đồng thời tăng cường khả năng chống trầy xước trong quá trình sử dụng lâu dài.Vành bezel được xử lý gọn gàng, hài hòa với tổng thể thiết kế Sharp Edged. Mặt kính sapphire cao cấp phủ lớp chống phản xạ, giúp quan sát mặt số rõ nét dưới nhiều điều kiện ánh sáng, đồng thời tăng cường khả năng chống trầy xước trong quá trình sử dụng lâu dài."
                ],
                "img" => "../image/chitiet_seiko/seiko5-2.png"
            ],
             [
                "h3" => "BỘ MÁY CƠ TỰ ĐỘNG SEIKO IN-HOUSE",
                "h2" => "Độ chính xác và độ bền được kiểm chứng theo thời gian",
                "content" => [
                    "Seiko SARX123 sử dụng bộ máy cơ tự động do Seiko tự sản xuất, nổi tiếng về độ ổn định, bền bỉ và khả năng hoạt động chính xác. Cơ chế lên cót tự động mượt mà, kết hợp khả năng lên cót tay, mang đến trải nghiệm sử dụng tin cậy – đúng tinh thần “Made in Japan”. Seiko SARX123 sử dụng bộ máy cơ tự động do Seiko tự sản xuất, nổi tiếng về độ ổn định, bền bỉ và khả năng hoạt động chính xác. Cơ chế lên cót tự động mượt mà, kết hợp khả năng lên cót tay, mang đến trải nghiệm sử dụng tin cậy – đúng tinh thần “Made in Japan”."
                ],
                "img" => "../image/chitiet_seiko/seiko5-3.png"
            ],
             [
                "h3" => "DÂY ĐEO THÉP KHÔNG GỈ CAO CẤP",
                "h2" => "Cảm giác đeo chắc chắn và thoải mái",
                "content" => [
                    "Dây thép không gỉ được chế tác liền mạch, các mắt dây hoàn thiện kỹ lưỡng, mang lại cảm giác chắc tay nhưng vẫn thoải mái khi đeo lâu. Khóa gập an toàn, dễ thao tác, đảm bảo sự ổn định và yên tâm trong quá trình sử dụng hằng ngày. Dây thép không gỉ được chế tác liền mạch, các mắt dây hoàn thiện kỹ lưỡng, mang lại cảm giác chắc tay nhưng vẫn thoải mái khi đeo lâu. Khóa gập an toàn, dễ thao tác, đảm bảo sự ổn định và yên tâm trong quá trình sử dụng hằng ngày."
                ],
                "img" => "../image/chitiet_seiko/seiko5-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO SARX123",
                "h2" => "Sức mạnh cơ khí thuần túy",
                "content" => [
                    "Sự cân bằng hoàn hảo giữa thẩm mỹ và công năng:",
                    "Khả năng chống nước 100 mét (10 ATM): Phù hợp sinh hoạt hằng ngày, rửa tay, đi mưa và bơi nhẹ.",
                    "Bộ máy cơ tự động Seiko in-house: Hoạt động bền bỉ, chính xác và ổn định.",
                    "Kính sapphire chống trầy: Bảo vệ mặt số hiệu quả, tăng độ bền theo thời gian.",
                    "Dạ quang trên kim và cọc số: Hỗ trợ xem giờ rõ ràng trong điều kiện thiếu sáng.",
                    "Thiết kế Sharp Edged hiện đại: Tinh tế, sắc nét, đậm dấu ấn thẩm mỹ Nhật Bản."
                ],
                "img" => "../image/sp5-seiko.png"
            ],
        ]
],

 //SP 6
    50 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ CAO CẤP",
                "h2" => "Bền bỉ – Chuẩn mực đồng hồ lặn chuyên nghiệp",
                "content" => [
                    "Mẫu SRPE37J sở hữu bộ vỏ thép không gỉ cao cấp với đường kính 43.8mm, được hoàn thiện chắc chắn, chịu va đập tốt và chống ăn mòn hiệu quả. Thiết kế vỏ mang đậm phong cách diver cổ điển của Seiko, vừa mạnh mẽ vừa cân đối, phù hợp với những người yêu thích đồng hồ thể thao chuyên dụng.",
                    "Cái tên Samurai bắt nguồn từ những đường cắt vát sắc lẹm trên vỏ đồng hồ, gợi liên tưởng đến những nhát chém từ thanh kiếm Katana huyền thoại. Với kích thước 43.8mm, chiếc SRPE37J sở hữu diện mạo hầm hố, nam tính nhưng nhờ thiết kế càng (lugs) ngắn và ôm nên vẫn cực kỳ tôn dáng tay của phái mạnh Việt. Sự kết hợp giữa bề mặt phay xước và các góc cạnh đánh bóng tỉ mỉ tạo nên một tổng thể cứng cáp, sẵn sàng đương đầu với mọi thử thách."
                    ],
                "img" => "../image/chitiet_seiko/seiko6-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL XOAY MỘT CHIỀU & KÍNH SAPPHIRE",
                "h2" => "An toàn và chính xác khi lặn",
                "content" => [
                    "Đồng hồ được trang bị vành bezel xoay một chiều với thang đo phút rõ nét, hỗ trợ kiểm soát thời gian lặn an toàn. Mặt kính sapphire cao cấp giúp chống trầy xước hiệu quả, đồng thời đảm bảo khả năng quan sát mặt số sắc nét dưới ánh sáng mạnh hoặc dưới nước.",
                    "Đặc biệt, vành bezel xoay một chiều giờ đây đã được làm từ Ceramic đen bóng, không chỉ mang lại vẻ sang trọng mà còn sở hữu khả năng chống trầy xước tuyệt đối – điều mà các tín đồ Seiko luôn mong đợi."
                    ],
                "img" => "../image/chitiet_seiko/seiko6-2.png"
            ],
             [
                "h3" => "BỘ MÁY CƠ TỰ ĐỘNG SEIKO IN-HOUSE",
                "h2" => "Độ tin cậy đã được khẳng định",
                "content" => [
                    "Seiko SRPE37J sử dụng bộ máy cơ tự động in-house của Seiko, nổi tiếng với độ bền cao, vận hành ổn định và chính xác. Bộ máy hỗ trợ lên cót tay và dừng kim giây khi chỉnh giờ, mang đến trải nghiệm sử dụng tiện lợi và chuẩn xác – đúng tinh thần “Made in Japan”.",
                    "Đi kèm là bộ dây cao su Silicon màu đen siêu mềm mại, có độ co giãn tốt, giúp đồng hồ ôm sát cổ tay ngay cả khi vận động mạnh dưới nước. Với khả năng chống nước lên đến 200m, đây không chỉ là một món phụ kiện thời trang mà còn là một công cụ lặn thực thụ."
                    ],
                "img" => "../image/chitiet_seiko/seiko6-3.png"
            ],
             [
                "h3" => "DÂY ĐEO THÉP KHÔNG GỈ CHẮC CHẮN",
                "h2" => "Thoải mái và an tâm khi đeo lâu dài",
                "content" => [
                    "Dây thép không gỉ được thiết kế chắc chắn, các mắt dây hoàn thiện kỹ lưỡng, mang lại cảm giác đầm tay và an toàn. Khóa gập kèm cơ chế mở rộng, thuận tiện khi đeo ngoài bộ đồ lặn hoặc sử dụng trong các hoạt động thể thao dưới nước."
                ],
                "img" => "../image/chitiet_seiko/seiko6-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT – HUBLOT BIG BANG FERRARI UNICO",
                "h2" => "Sức mạnh cơ khí mang tinh thần tốc độ",
                "content" => [
                    "Sinh ra cho môi trường khắc nghiệt",
                    "- Khả năng chống nước 200 mét (20 ATM): Đáp ứng tiêu chuẩn đồng hồ lặn chuyên nghiệp.",
                    " -Bộ máy cơ tự động Seiko in-house: Bền bỉ, chính xác, ổn định theo thời gian.",
                    "- Vành bezel xoay một chiều: Hỗ trợ kiểm soát thời gian lặn an toàn.",
                    "- Kính sapphire chống trầy: Bảo vệ mặt số tối ưu.",
                    "- Dạ quang LumiBrite trên kim và cọc số: Giúp xem giờ rõ ràng trong điều kiện thiếu sáng hoặc dưới nước.",
                    "Seiko Prospex King Samurai SRPE37J1 (phiên bản nội địa Nhật) là mẫu đồng hồ lặn mang tinh thần võ sĩ đạo với những nâng cấp 'đáng đồng tiền bát gạo'"
                ],
                "img" => "../image/sp6-seiko.png"
            ],
        ]
],
    //SP 7
    51 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "KHUNG VỎ THÉP KHÔNG GỈ CAO CẤP",
                "h2" => "Bền bỉ – Hoàn thiện tinh tế",
                "content" => [
                    "Mẫu SARX123 sở hữu bộ vỏ thép không gỉ cao cấp, hoàn thiện tỉ mỉ với các bề mặt chải xước và đánh bóng đan xen, tạo chiều sâu thị giác sang trọng. Đường kính 40.2mm lý tưởng, ôm tay gọn gàng, phù hợp nhiều cỡ cổ tay nam giới hiện đại, vừa lịch lãm nơi công sở vừa linh hoạt trong đời sống thường nhật. Điểm cộng lớn nhất của SSC819P1 chính là kích thước mặt số 39mm cực kỳ tinh tế. Trong khi các mẫu Chronograph thường có xu hướng to bản và hầm hố, Seiko đã khéo léo đưa mẫu Speedtimer này về thông số vàng, giúp đồng hồ ôm sát và cân đối trên hầu hết cổ tay nam giới Á Đông."
                ],
                "img" => "../image/chitiet_seiko/seiko7-1.png"
            ],
             [
                "h3" => "MẶT SỐ CHRONOGRAPH RÕ NÉT & LỊCH NGÀY TIỆN DỤNG",
                "h2" => "Sự cân bằng giữa thể thao và thanh lịch",
                "content" => [
                    "Mặt số màu đen nổi bật với bố cục chronograph 3 mặt số phụ, mang lại khả năng đo thời gian chính xác và trực quan. Cửa sổ lịch ngày tại vị trí 4 giờ được bố trí khéo léo, đảm bảo tính tiện dụng mà không phá vỡ tổng thể thiết kế.",
                    "Đồng hồ được trang bị kính sapphire cong, giúp tăng độ bền và khả năng chống trầy xước. Lớp phủ chống phản xạ bên trong hỗ trợ quan sát mặt số rõ ràng trong nhiều điều kiện ánh sáng khác nhau, đặc biệt khi hoạt động ngoài trời."
                ],
                "img" => "../image/chitiet_seiko/seiko7-2.png"
            ],
             [
                "h3" => "BỘ MÁY SOLAR CHRONOGRAPH SEIKO IN-HOUSE",
                "h2" => "Chính xác – Thân thiện với môi trường",
                "content" => [
                    "Seiko SSC819P1 sử dụng bộ máy Solar Chronograph in-house của Seiko, hoạt động bằng năng lượng ánh sáng, không cần thay pin thường xuyên. Bộ máy mang lại độ chính xác cao, vận hành ổn định và phản ánh triết lý chế tác bền vững của Seiko.",
                    "Bên trong SSC819P1 là bộ máy Caliber V192 chạy bằng năng lượng ánh sáng (Solar). Bạn sẽ không bao giờ phải lo lắng về việc thay pin định kỳ hay đồng hồ bị dừng nếu không đeo thường xuyên. Chỉ cần tiếp xúc với ánh sáng mặt trời hoặc thậm chí là ánh sáng đèn điện, viên pin bên trong sẽ tự động sạc đầy và có thể duy trì hoạt động chính xác trong vòng 6 tháng ngay cả khi để trong bóng tối hoàn đầu."
                ],
                "img" => "../image/chitiet_seiko/seiko7-3.png"
            ],
             [
                "h3" => "DÂY ĐEO THÉP KHÔNG GỈ CHẮC CHẮN",
                "h2" => "Thoải mái và lịch lãm trên cổ tay",
                "content" => [
                    "Dây thép không gỉ được hoàn thiện chắc chắn, mang lại cảm giác đầm tay và thoải mái khi đeo lâu dài. Khóa gập an toàn giúp đồng hồ luôn cố định trên cổ tay trong mọi hoạt động thường ngày và thể thao. Dây da mềm mại, ôm sát cổ tay, mang lại sự thoải mái khi đeo lâu. Tông xanh trang nhã góp phần tôn lên vẻ sang trọng và đẳng cấp của mẫu Big Bang Gold Blue."
                ],
                "img" => "../image/chitiet_seiko/seiko7-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO PROSPEX SSC819P1",
                "h2" => "Phong cách thể thao xa xỉ chuẩn Thụy Sĩ",
                "content" => [
                    "Hiệu năng cao cho phong cách sống năng động",
                    "Bộ máy Solar Chronograph Seiko: Sử dụng năng lượng ánh sáng, chính xác và bền bỉ.",
                    "Chức năng chronograph: Hỗ trợ đo thời gian thể thao tiện lợi.",
                    "Chống nước 100 mét (10 ATM): Phù hợp cho sinh hoạt hằng ngày và bơi nhẹ.",
                    "Kính sapphire chống trầy: Bảo vệ mặt số hiệu quả.",
                    "Dạ quang LumiBrite trên kim và cọc số: Giúp xem giờ rõ ràng trong điều kiện thiếu sáng.",
                    "Dù nằm trong phân khúc tầm trung, nhưng Seiko không hề tiết kiệm khi trang bị cho SSC819P1 mặt kính Sapphire cong cao cấp với lớp phủ chống lóa phía trong, mang lại góc nhìn trong vắt và khả năng chống trầy xước vượt trội. Vành bezel cố định được tích hợp thước đo tốc độ Tachymeter với lớp phủ màu đen bóng bẩy, không chỉ tăng thêm tính năng kỹ thuật cho người dùng mà còn bảo vệ mặt kính khỏi những va chạm trực diện, khẳng định độ bền bỉ đặc trưng của dòng Prospex."

                ],
                "img" => "../image/sp7-seiko.png"
            ],
        ]
],
//SP 8
    52 => [
        "bg" => "#b7b7b7",
        "items" => [
            [
                "h3" => "THIẾT KẾ VỎ THÉP KHÔNG GỈ MẠNH MẼ",
                "h2" => "Tinh thần Field Watch đậm chất thực dụng",
                "content" => [
                    "Seiko SRPG21J1 sở hữu bộ vỏ thép không gỉ kích thước 43.8mm, hoàn thiện chải xước khỏe khoắn, mang đậm tinh thần đồng hồ dã chiến (Field Watch). Thiết kế tập trung vào tính bền bỉ, dễ đọc và khả năng thích nghi cao trong nhiều môi trường sử dụng, từ đời sống hằng ngày đến các hoạt động ngoài trời.",
                    "Điểm nhận diện quyền lực nhất của SRPG21J1 chính là logo PADI kiêu hãnh đặt tại góc 6 giờ, đi kèm với bộ kim viền xanh dương nổi bật trên nền mặt số đen. Mặt số không chỉ đơn thuần là màu đen phẳng mà được trang trí bằng họa tiết Globe (đường kinh tuyến/vĩ tuyến) dập nổi tinh xảo, tượng trưng cho bản đồ của những cuộc thám hiểm dưới lòng đại dương."
                ],
                "img" => "../image/chitiet_seiko/seiko8-1.png"
            ],
             [
                "h3" => "VÀNH BEZEL & MẶT SỐ DỄ QUAN SÁT",
                "h2" => "Ưu tiên khả năng đọc thời gian trong mọi điều kiện",
                "content" => [
                     "Mặt số màu xanh olive đặc trưng được bố trí cọc số lớn, rõ ràng cùng kim phủ dạ quang LumiBrite độc quyền của Seiko. Thiết kế tối giản, tương phản cao giúp người đeo quan sát thời gian nhanh chóng, chính xác ngay cả trong điều kiện thiếu sáng. Sự kết hợp giữa tông xanh lặn biển và sắc đen huyền bí tạo nên một diện mạo chuyên nghiệp, khẳng định đẳng cấp của một chiếc đồng hồ lặn đạt chuẩn quốc tế."
                ],
                "img" => "../image/chitiet_seiko/seiko8-2.png"
            ],
             [
                "h3" => "BỘ MÁY CƠ TỰ ĐỘNG SEIKO CAL.4R36",
                "h2" => "Ổn định – bền bỉ – đáng tin cậy",
                "content" => [
                    "Đồng hồ được trang bị bộ máy cơ tự động Calibre 4R36 do Seiko sản xuất in-house, hỗ trợ lên cót tay và dừng kim giây (hacking stop). Bộ máy hoạt động ổn định, dễ bảo dưỡng và phù hợp cho nhu cầu sử dụng lâu dài. Bộ vỏ 43.8mm của SRPG21J1 vẫn giữ nguyên những đường cắt vát mạnh mẽ như nhát kiếm của các võ sĩ đạo. Từng bề mặt thép 316L được xử lý phay xước xen kẽ đánh bóng gương, tạo nên hiệu ứng thị giác cứng cáp và nam tính. Dù có kích thước khá lớn, nhưng nhờ thiết kế tai càng (lugs) ngắn đặc trưng của dòng Samurai, chiếc đồng hồ vẫn ôm khít cổ tay, tạo cảm giác chắc chắn và tin cậy cho người đeo trong mọi hoạt động thể thao mạo hiểm."
                ], 
                "img" => "../image/chitiet_seiko/seiko8-3.png"
            ],
             [
                "h3" => "DÂY VẢI NYLON PHONG CÁCH QUÂN ĐỘI",
                "h2" => "Nhẹ – thoải mái – linh hoạt",
                "content" => [
                     "Seiko SRPG21J1 đi kèm dây vải nylon màu xanh olive, nhẹ và thoáng, mang lại cảm giác đeo thoải mái suốt cả ngày. Phong cách quân đội cổ điển kết hợp khóa kim loại chắc chắn, phù hợp với tinh thần phiêu lưu và khám phá. Seiko SRPG21J1 đi kèm dây vải nylon màu xanh olive, nhẹ và thoáng, mang lại cảm giác đeo thoải mái suốt cả ngày. Phong cách quân đội cổ điển kết hợp khóa kim loại chắc chắn, phù hợp với tinh thần phiêu lưu và khám phá.",
                ],
                "img" => "../image/chitiet_seiko/seiko8-4.png"
            ],
             [
                "h3" => "TÍNH NĂNG NỔI BẬT SEIKO SRPG21J1",
                "h2" => "Thực dụng – bền bỉ – chuẩn mực Seiko",
                "content" => [
                    "Khả năng chống nước 100 mét (10 ATM): Phù hợp cho sinh hoạt hằng ngày, đi mưa, rửa tay và các hoạt động ngoài trời nhẹ.
Lịch ngày & thứ tại vị trí 3 giờ: Hiển thị rõ ràng, tiện lợi khi sử dụng.
Kính Hardlex độc quyền Seiko: Chống va đập tốt, phù hợp với môi trường sử dụng thực tế.
Dạ quang LumiBrite trên kim và cọc số: Đảm bảo khả năng xem giờ trong bóng tối.",
                    "Đây là sự hợp tác giữa Seiko và PADI (Hiệp hội hướng dẫn lặn biển chuyên nghiệp thế giới). Nếu mẫu SRPE37J trước đó mang vẻ thanh lịch với mặt trắng, thì SRPG21J1 lại là một quái vật biển sâu thực thụ với tông màu xanh đen đặc trưng của đại dương."
                   
                   
                ],
                "img" => "../image/sp8-seiko.png"
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
            <?php 
                $item_img = trim($item['img'] ?? '');
                if (strpos($item_img, 'http://') === 0 || strpos($item_img, 'https://') === 0) {
                    $item_img_src = $item_img;
                } else {
                    $item_img_src = (strpos($item_img, '../') === 0) ? $item_img : '../' . $item_img;
                }
            ?>
            <div class="story-block">

                <div class="story-text">
                    <h3><?= $item['h3'] ?></h3>
                    <h2><?= $item['h2'] ?></h2>

                    <?php foreach ($item['content'] as $p): ?>
                        <p><?= $p ?></p>
                    <?php endforeach; ?>
                </div>

                <div class="story-img">
                    <img src="<?= $item_img_src ?>" 
                         onerror="this.onerror=null; this.src='../image/no-image.png';" alt="<?= htmlspecialchars($item['h2']) ?>">
                </div>

            </div>
        <?php endforeach; ?>

    </div>
</section>
<?php endif; ?>




  
</section>
    <?php
        $publicationImages = [
            45 => "../chitiet_seiko/seiko-anpham.png",
            46 => "../chitiet_seiko/seiko-anpham.png",
            47 => "../chitiet_seiko/seiko-anpham.png",
            48 => "../chitiet_seiko/seiko-anpham.png",
            49 => "../chitiet_seiko/seiko-anpham.png",
            50 => "../chitiet_seiko/seiko-anpham.png",
            51 => "../chitiet_seiko/seiko-anpham.png",
            52 => "../chitiet_seiko/seiko-anpham.png",
            53 => "../chitiet_seiko/seiko-anpham.png",
            54 => "../chitiet_seiko/seiko-anpham.png",
            55 => "../chitiet_seiko/seiko-anpham.png",
            56 => "../chitiet_seiko/seiko-anpham.png",
        ];

        // lấy id sản phẩm hiện tại
        $productId = $row['id'];

        // nếu không có thì dùng ảnh mặc định
        $pub_raw = $publicationImages[$productId] ?? "seiko-anpham.png";
        if (strpos($pub_raw, 'http://') === 0 || strpos($pub_raw, 'https://') === 0) {
            $pub_img_src = $pub_raw;
        } else {
            $pub_clean = ltrim(str_replace('../', '', $pub_raw), '/');
            if (strpos($pub_clean, 'image/') === 0) {
                $pub_img_src = '../' . $pub_clean;
            } elseif (strpos($pub_clean, 'chitiet_seiko/') === 0) {
                $pub_img_src = '../image/' . $pub_clean;
            } else {
                $pub_img_src = '../image/chitiet_seiko/' . $pub_clean;
            }
        }
    ?>
        <section class="bottom-info-section">
        <h3 class="cert-title">Chứng nhận</h3>
        <p class="cert-desc">Superlative Chronometer (chứng nhận COSC + Seiko sau khi lắp vỏ)</p>

        <h3 class="pub-title" style="text-align: center;">Ấn phẩm</h3>
        <a href="#" class="download-link">
            <i class="fa-solid fa-download"></i> Tải ấn phẩm
        </a>
        
        <img src="<?php echo $pub_img_src; ?>" 
            style="max-width:300px;" 
            alt="Ấn phẩm Seiko" 
            class="publication-img"
            onerror="this.onerror=null; this.src='../image/no-image.png';">
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
                <img src="<?php echo $anh_chinh; ?>" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>" onerror="this.onerror=null; this.src='../image/no-image.png';">
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

<?php
include '../ai-chatbot.php';
include '../thongbao.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>