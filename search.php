<?php
// 1. NẾU CHƯA CÓ SESSION THÌ BẬT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KẾT NỐI DATABASE
include_once 'admin/connect.php'; 

// Cấu hình Azure
require_once __DIR__ . '/env_loader.php';

$searchService = $_ENV['AZURE_SEARCH_SERVICE'] ?? '';
$searchApiKey  = $_ENV['AZURE_SEARCH_API_KEY'] ?? '';
$indexName     = $_ENV['AZURE_SEARCH_INDEX'] ?? '';
$visionEndpoint = $_ENV['AZURE_VISION_ENDPOINT'] ?? '';
$visionApiKey   = $_ENV['AZURE_VISION_API_KEY'] ?? '';
$azureUrl       = "https://{$searchService}.search.windows.net/indexes/{$indexName}/docs/search?api-version=2023-11-01";

// Khởi tạo các biến mặc định
$is_image_search     = false;
$uploaded_image_path = '';
$detected_tags       = [];
$matched_ids         = [];
$result_all          = null;

// Hàm gửi Yêu cầu API sang Azure AI Search
function executeAzureSearch($url, $apiKey, $payload) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// =========================================================================
// 3. XỬ LÝ LỆNH TÌM KIẾM
// =========================================================================

// 📌 TRƯỜNG HỢP A: NGƯỜI DÙNG TÌM BẰNG ẢNH (POST file upload)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0) {
    $is_image_search = true;

    // 1. Lưu file ảnh vào thư mục uploads/ trên server để hiển thị lên web
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['search_image']['name'], PATHINFO_EXTENSION));
    $newFileName = 'search_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['search_image']['tmp_name'], $targetPath)) {
        $uploaded_image_path = $targetPath;
        $imageData = file_get_contents($uploaded_image_path);

        // 2. Lấy Vector của ảnh từ Azure AI Vision API
        $vectorApiUrl = rtrim($visionEndpoint, '/') . "/computervision/retrieval:vectorizeImage?api-version=2024-02-01&model-version=2023-04-15";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vectorApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/octet-stream',
            'Ocp-Apim-Subscription-Key: ' . $visionApiKey
        ]);
        $vectorResponse = curl_exec($ch);
        curl_close($ch);

        $vectorResult = json_decode($vectorResponse, true);

        if (isset($vectorResult['vector'])) {
    $image_vector = $vectorResult['vector'];

    $vectorPayload = [
        'vectorQueries' => [
            [
                'kind'   => 'vector',
                'vector' => $image_vector,
                'fields' => 'image_vector',
                'k'      => 1 // Đổi k = 1 để ép Azure Search chỉ trả về duy nhất 1 kết quả chính xác nhất
            ]
        ],
        'select' => 'id'
    ];

    $resVector = executeAzureSearch($azureUrl, $searchApiKey, $vectorPayload);
    if (isset($resVector['value'][0]['id'])) {
        $matched_ids[] = (int)$resVector['value'][0]['id'];
    }
}
        
    }
}

// 📌 TRƯỜNG HỢP B: NGƯỜI DÙNG TÌM BẰNG VĂN BẢN (GET/POST query/keyword)
$search = isset($_GET['query']) ? trim($_GET['query']) : (isset($_POST['query']) ? trim($_POST['query']) : (isset($_GET['keyword']) ? trim($_GET['keyword']) : ''));
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'new';
$price  = isset($_GET['price']) ? $_GET['price'] : 'all';

if (!$is_image_search && !empty($search)) {
    $query = mb_strtolower($search, 'UTF-8');
    $query = str_replace('nằm', 'nam', $query);
    $query = preg_replace('/(\d+)\s*m\b/u', '$1mm', $query);
    $query = preg_replace('/(\d+)\s*ly\b/u', '$1mm', $query);

    $stop_words = ['đồng hồ', 'dong ho', 'chiếc', 'mẫu', 'cần tìm', 'tìm', 'bán', 'đồng', 'hồ'];
    foreach ($stop_words as $word) {
        $query = str_replace($word, '', $query);
    }

    $query = trim(preg_replace('/\s+/', ' ', $query));
    $query_without_gender = trim(preg_replace('/(?<=\s|^)(nam|nữ|nu)(?=\s|$)/u', '', $query));
    if (!empty($query_without_gender)) {
        $query = $query_without_gender;
    }

    if (empty($query)) {
        $query = $search;
    }

    // TẦNG 1: Tìm CHÍNH XÁC trên Azure Search
    $payload1 = [
        'search'       => $query,
        'queryType'    => 'simple',
        'searchMode'   => 'any',
        'searchFields' => 'ten_san_pham,thuong_hieu,mo_ta',
        'select'       => 'id',
        'top'          => 50
    ];
    
    $res1 = executeAzureSearch($azureUrl, $searchApiKey, $payload1);
    if (isset($res1['value']) && !empty($res1['value'])) {
        foreach ($res1['value'] as $item) {
            $matched_ids[] = (int)$item['id'];
        }
    }

    // TẦNG 2: Fuzzy Search trên Azure Search
    if (empty($matched_ids)) {
        $words = array_filter(explode(' ', $query));
        $fuzzyQuery = implode('~1 ', $words) . '~1';

        $payload2 = [
            'search'       => $fuzzyQuery,
            'queryType'    => 'full',
            'searchMode'   => 'any',
            'searchFields' => 'ten_san_pham,thuong_hieu',
            'select'       => 'id',
            'top'          => 50
        ];

        $res2 = executeAzureSearch($azureUrl, $searchApiKey, $payload2);
        if (isset($res2['value']) && !empty($res2['value'])) {
            foreach ($res2['value'] as $item) {
                $matched_ids[] = (int)$item['id'];
            }
        }
    }

    // TẦNG 3: Fallback tìm kiếm bằng MySQL
    if (empty($matched_ids)) {
        $clean_search = $conn->real_escape_string($search);
        $sql_fallback = "SELECT id FROM san_pham WHERE ten_san_pham LIKE '%$clean_search%'";
        $fb_res = $conn->query($sql_fallback);
        if ($fb_res && $fb_res->num_rows > 0) {
            while ($fb_row = $fb_res->fetch_assoc()) {
                $matched_ids[] = (int)$fb_row['id'];
            }
        }
    }
}

// =========================================================================
// 4. TRUY VẤN MYSQL THEO KẾT QUẢ VÀ ÁP DỤNG BỘ LỌC GIÁ & SẮP XẾP
// =========================================================================
if (!empty($matched_ids)) {
    $matched_ids = array_unique($matched_ids);
    $ids_string  = implode(',', array_map('intval', $matched_ids));

    $price_sql = "";
    if ($price == 'under10') { $price_sql = " AND gia_ban < 10000000"; } 
    elseif ($price == '10to50') { $price_sql = " AND gia_ban BETWEEN 10000000 AND 50000000"; } 
    elseif ($price == 'over50') { $price_sql = " AND gia_ban > 50000000"; }

    $order_sql = "ORDER BY FIELD(id, $ids_string)";
    if ($sort == 'asc') { $order_sql = "ORDER BY gia_ban ASC"; } 
    elseif ($sort == 'desc') { $order_sql = "ORDER BY gia_ban DESC"; }

    $sql = "SELECT * FROM san_pham WHERE id IN ($ids_string) $price_sql $order_sql";
    $result_all = $conn->query($sql);
}

// 5. CHÈN HEADER DÙNG CHUNG CỦA WEBSITE
include_once 'header.php';
?>

<!-- CSS RIÊNG DÀNH CHO TRANG SEARCH -->
<style>
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
.brand-badge { display: inline-block; background: #f4f4f4; padding: 4px 10px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; color: #555; border-radius: 4px; }
.empty-search { text-align: center; width: 100%; padding: 50px 0; color: #888; }
.empty-search i { font-size: 50px; color: #ddd; margin-bottom: 15px; }

/* CSS KHUNG HIỂN THỊ ẢNH XEM TRƯỚC VÀ TAG AI */
.image-preview-card { background: #fff8f0; border: 1px solid #e0c9a6; border-radius: 12px; padding: 20px; max-width: 600px; margin: 0 auto 25px auto; display: flex; align-items: center; gap: 20px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.image-preview-card img { width: 110px; height: 110px; object-fit: cover; border-radius: 8px; border: 2px solid #b58b5a; }
.ai-tag-badge { display: inline-block; background: #b58b5a; color: #fff; padding: 3px 8px; font-size: 11px; border-radius: 12px; margin-right: 5px; margin-top: 5px; }
</style>

<!-- KHU VỰC HIỂN THỊ KẾT QUẢ TÌM KIẾM -->
<div class="product-page-header" style="margin-top: 40px; text-align: center;">
    
    <!-- KHUNG HIỂN THỊ ẢNH VỪA TẢI LÊN (NẾU CÓ TÌM BẰNG ẢNH) -->
    <?php if ($is_image_search && !empty($uploaded_image_path)): ?>
        <div class="image-preview-card">
            <img src="<?php echo htmlspecialchars($uploaded_image_path); ?>" alt="Ảnh tải lên">
            <div>
                <h4 style="margin: 0 0 5px 0; color: #b58b5a;"><i class="fa-solid fa-camera"></i> Ảnh bạn đã tải lên</h4>
                
                <?php if (!empty($detected_tags)): ?>
                    <p style="font-size: 12px; margin: 5px 0; color: #666;"><b>AI phát hiện đặc trưng:</b></p>
                    <div>
                        <?php foreach ($detected_tags as $tag): ?>
                            <span class="ai-tag-badge"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <h1 class="main-title" style="text-transform: none; margin-bottom: 20px;">
        <?php if ($is_image_search): ?>
            📷 Kết quả tìm kiếm theo Hình Ảnh AI (Vector Similarity)
        <?php else: ?>
            Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search); ?>"
        <?php endif; ?>
    </h1>

    <!-- FORM TÌM KIẾM VĂN BẢN -->
    <form action="search.php" method="GET" style="display: flex; justify-content: center; margin-bottom: 25px;">
        <div style="position: relative; display: flex; align-items: center; width: 100%; max-width: 550px;">
            <input type="text" name="query" id="searchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nhập từ khóa tìm kiếm..." style="width: 100%; padding: 12px 115px 12px 20px; border-radius: 30px; border: 2px solid #b58b5a; outline: none; font-size: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">

            <!-- Icon Micro -->
            <button type="button" id="azureMicBtn" title="Tìm kiếm bằng giọng nói" style="position: absolute; right: 75px; background: none; border: none; cursor: pointer; padding: 5px; outline: none;">
                <i class="fa-solid fa-microphone" id="azureMicIcon" style="color: #b58b5a; font-size: 18px;"></i>
            </button>

            <!-- Icon Máy ảnh (GỌI HÀM JAVASCRIPT DIRECT) -->
            <div onclick="openImagePicker()" title="Tìm kiếm bằng hình ảnh" style="position: absolute; right: 45px; cursor: pointer; padding: 5px; display: flex; align-items: center;">
                <i class="fa-solid fa-camera" style="color: #007bff; font-size: 18px;"></i>
            </div>

            <!-- Icon Kính lúp -->
            <button type="submit" style="position: absolute; right: 15px; background: none; border: none; cursor: pointer; padding: 5px; outline: none;">
                <i class="fa-solid fa-magnifying-glass" style="color: #555; font-size: 18px;"></i>
            </button>
        </div>
    </form>

    <!-- FORM ẨN DÙNG CHUNG ĐỂ UPLOAD ẢNH -->
<form action="search.php" method="POST" enctype="multipart/form-data" id="globalImageSearchForm" style="display: none;">
    <input type="file" name="search_image" id="globalImageInput" accept="image/*" onchange="submitImageSearch()">
</form>

<!-- JAVASCRIPT XỬ LÝ SỰ KIỆN NÚT BẤM -->
<script>
function openImagePicker() {
    // Mở khung chọn file từ máy tính/điện thoại
    document.getElementById('globalImageInput').click();
}

function submitImageSearch() {
    const fileInput = document.getElementById('globalImageInput');
    if (fileInput.files && fileInput.files[0]) {
        // Kiểm tra dung lượng file (Giới hạn 10MB)
        const fileSize = fileInput.files[0].size / 1024 / 1024; // MB
        if (fileSize > 10) {
            alert('File ảnh quá lớn (' + fileSize.toFixed(1) + 'MB). Vui lòng chọn ảnh nhỏ hơn 10MB!');
            return;
        }
        // Hiển thị trạng thái đang xử lý (tùy chọn)
        document.body.style.cursor = 'wait';
        // Gửi form
        document.getElementById('globalImageSearchForm').submit();
    }
}
</script>
    <!-- BỘ LỌC GIÁ & SẮP XẾP -->
    <?php if (!empty($search) || $is_image_search): ?>
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

<!-- DANH SÁCH SẢN PHẨM TÌM THẤY -->
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
                        <h3>Không tìm thấy sản phẩm tương thích!</h3>
                        <p>Hệ thống không tìm thấy sản phẩm nào phù hợp với yêu cầu tìm kiếm của bạn.</p>
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
</body>
</html>