<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$path_prefix = ''; 
include 'admin/connect.php';
// ================= 1. BỘ XỬ LÝ NGÔN NGỮ (KHÔNG CẦN HEADER) =================
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('site_lang', $_GET['lang'], time() + 86400 * 30, '/');
    $_COOKIE['site_lang'] = $_GET['lang'];
}
$current_lang = $_COOKIE['site_lang'] ?? ($_SESSION['lang'] ?? 'vi');

// Hàm giữ nguyên tham số URL khi đổi ngôn ngữ
function getLangUrl($lang) {
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}

// Bật bộ hứng HTML dịch tự động nếu không phải Tiếng Việt
if ($current_lang !== 'vi') {
    ob_start();
}
if (file_exists('azure_translator.php')) {
    require_once 'azure_translator.php';
}
// Check session cho việc hiển thị TRANG HTML bình thường
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// =========================================================================
// 🎯 XỬ LÝ AJAX: QUÉT FILE PDF BẰNG AZURE AI DOCUMENT INTELLIGENCE
// =========================================================================
if (isset($_POST['action']) && $_POST['action'] === 'scan_pdf_ai') {
    header('Content-Type: application/json; charset=utf-8');

    // 🔑 THÔNG TIN AZURE AI
    require_once __DIR__ . '/env_loader.php';

    $endpoint        = $_ENV['DOC_INTEL_ENDPOINT'] ?? '';
    $subscriptionKey = $_ENV['DOC_INTEL_KEY'] ?? '';

    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn file PDF hợp lệ!']);
        exit;
    }

    $pdfPath = $_FILES['pdf_file']['tmp_name'];
    $pdfData = file_get_contents($pdfPath);

    // 1. Gửi file PDF lên Azure AI Document Intelligence
    $analyzeUrl = rtrim($endpoint, '/') . '/formrecognizer/documentModels/prebuilt-layout:analyze?api-version=2023-07-31';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $analyzeUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdfData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        'Content-Type: application/pdf'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 202) {
        echo json_encode(['status' => 'error', 'message' => 'Không thể gửi file tới Azure AI. Mã lỗi: ' . $httpCode]);
        exit;
    }

    // 2. Lấy Operation-Location
    preg_match('/Operation-Location:\s*(.*)\r\n/i', $response, $matches);
    $operationUrl = isset($matches[1]) ? trim($matches[1]) : '';

    if (empty($operationUrl)) {
        echo json_encode(['status' => 'error', 'message' => 'Không lấy được đường dẫn xử lý từ Azure.']);
        exit;
    }

    // 3. Chờ AI xử lý bóc tách chữ
    $maxTries = 10;
    $status = 'running';

    while ($maxTries > 0 && $status === 'running') {
        sleep(1);
        $chState = curl_init();
        curl_setopt($chState, CURLOPT_URL, $operationUrl);
        curl_setopt($chState, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chState, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chState, CURLOPT_HTTPHEADER, [
            'Ocp-Apim-Subscription-Key: ' . $subscriptionKey
        ]);
        $resState = curl_exec($chState);
        curl_close($chState);

        $resultData = json_decode($resState, true);
        $status = $resultData['status'] ?? 'failed';

        if ($status === 'succeeded') {
            $extractedContent = $resultData['analyzeResult']['content'] ?? '';

            // Tự động tìm Mã Đơn Hàng trong file PDF
            $fileName = $_FILES['pdf_file']['name'] ?? '';
            $found_order_id = '';
            if (preg_match('/#(\d+)/', $fileName, $match_filename)) {
                $found_order_id = $match_filename[1];
            }
            // 2. Nếu tên file không có #74 thì mới tìm trong NỘI DUNG PDF
            if (empty($found_order_id)) {
                if (preg_match('/#(\d+)/', $extractedContent, $match_id)) {
                    $found_order_id = $match_id[1];
                } elseif (preg_match('/(?:Mã\s*đơn\s*hàng|Đơn\s*hàng|Mã\s*đơn|Order)\s*[:\-\#]?\s*(\d+)/ui', $extractedContent, $match_id)) {
                    $found_order_id = $match_id[1];
                }
            }

            // Tự động tìm Tên Sản Phẩm thực tế từ Database dựa theo Mã đơn vừa tìm được
            $found_ten_sp = '';
            if (!empty($found_order_id)) {
                $sql_sp = "SELECT sp.ten_san_pham 
                           FROM chi_tiet_don_hang ct 
                           JOIN san_pham sp ON ct.id_san_pham = sp.id 
                           WHERE ct.id_don_hang = '$found_order_id' LIMIT 1";
                $res_sp = $conn->query($sql_sp);
                if ($res_sp && $res_sp->num_rows > 0) {
                    $row_sp = $res_sp->fetch_assoc();
                    $found_ten_sp = $row_sp['ten_san_pham'];
                }
            }

            echo json_encode([
                'status'         => 'success',
                'extracted_text' => $extractedContent,
                'order_id'       => $found_order_id,
                'ten_san_pham'   => $found_ten_sp,
                'message'        => 'Azure AI đã trích xuất thông tin thành công!'
            ]);
            exit;
        }
        $maxTries--;
    }

    echo json_encode(['status' => 'error', 'message' => 'Quá thời gian chờ Azure xử lý.']);
    exit;
}

// =========================================================================
// 🎯 XỬ LÝ LƯU YÊU CẦU BẢO HÀNH (KHI BẤM SUBMIT FORM)
// =========================================================================
if (isset($_POST['btn_submit_baohanh'])) {
    $ho_ten = $conn->real_escape_string(trim($_POST['ho_ten']));
    $so_dien_thoai = $conn->real_escape_string(trim($_POST['so_dien_thoai']));
    $ma_don_hang = (int)$_POST['ma_don_hang'];
    $mo_ta_loi = $conn->real_escape_string(trim($_POST['mo_ta_loi']));
    $ngay_mua = date('Y-m-d');

    // 1. KIỂM TRA ĐƠN HÀNG CÓ TỒN TẠI VÀ ĐÃ GIAO THÀNH CÔNG CHƯA
    $check_order = $conn->query("SELECT * FROM don_hang WHERE id = '$ma_don_hang' AND id_nguoi_dung = '$user_id' AND trang_thai = 'Đã giao'");

    if ($check_order->num_rows == 0) {
        $_SESSION['toast_msg'] = "Đơn hàng #$ma_don_hang không hợp lệ hoặc chưa ở trạng thái 'Đã giao'!";
        $_SESSION['toast_type'] = "error";
        header("Location: bao_hanh.php");
        exit();
    }

    // 2. XỬ LÝ UPLOAD HÌNH ẢNH LỖI
    $hinh_anh = "";
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }
        $file_extension = pathinfo($_FILES["hinh_anh"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
            $hinh_anh = $target_file;
        }
    }

    // 3. ✨ [BỔ SUNG MỚI] LẤY TÊN SẢN PHẨM THỰC TẾ TỪ CSDL DỰA THEO MÃ ĐƠN HÀNG
    $sql_get_sp = "SELECT sp.ten_san_pham 
                   FROM chi_tiet_don_hang ct 
                   JOIN san_pham sp ON ct.id_san_pham = sp.id 
                   WHERE ct.id_don_hang = '$ma_don_hang' LIMIT 1";
    $res_get_sp = $conn->query($sql_get_sp);
    
    $ten_san_pham_real = "Đơn hàng #" . $ma_don_hang;
    if ($res_get_sp && $res_get_sp->num_rows > 0) {
        $sp_data = $res_get_sp->fetch_assoc();
        $ten_san_pham_real = $sp_data['ten_san_pham']; // Ví dụ: Đồng hồ Rolex Submariner
    }

    // 4. THÊM YÊU CẦU BẢO HÀNH VÀO DATABASE (Lưu tên sản phẩm thật vào cột so_series)
    $sql_insert = "INSERT INTO yeu_cau_bao_hanh (id_nguoi_dung, ho_ten, so_dien_thoai, ma_dong_ho, so_series, ngay_mua, mo_ta_loi, hinh_anh, trang_thai, ngay_tao) 
                   VALUES ('$user_id', '$ho_ten', '$so_dien_thoai', '$ma_don_hang', '$ten_san_pham_real', '$ngay_mua', '$mo_ta_loi', '$hinh_anh', 'Đang chờ', NOW())";

    if ($conn->query($sql_insert) === TRUE) {
        $_SESSION['toast_msg'] = "Gửi yêu cầu bảo hành thành công!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Lỗi CSDL: " . $conn->error;
        $_SESSION['toast_type'] = "error";
    }
    header("Location: bao_hanh.php"); 
    exit();
}

// LẤY THÔNG BÁO TOAST NẾU CÓ
$toast_msg = ""; $toast_type = "";
if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_type = $_SESSION['toast_type'];
    unset($_SESSION['toast_msg']); unset($_SESSION['toast_type']);
}

// LẤY THÔNG TIN USER HIỆN TẠI
$sql_user = "SELECT * FROM nguoi_dung WHERE id = $user_id";
$user_data = $conn->query($sql_user)->fetch_assoc();

// THỐNG KÊ ĐƠN HÀNG
$count_order = $conn->query("SELECT COUNT(*) as total, SUM(tong_tien) as sum_money FROM don_hang WHERE id_nguoi_dung = $user_id")->fetch_assoc();
$tong_don = $count_order['total'] ? $count_order['total'] : 0;
$tich_luy = $count_order['sum_money'] ? number_format($count_order['sum_money'], 0, ',', '.') : '0';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảo hành sản phẩm - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .bh-form-container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .bh-form-group { margin-bottom: 20px; }
        .bh-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; font-size: 14px; }
        .bh-form-group input, .bh-form-group textarea, .bh-form-group select { 
            width: 100%; 
            padding: 12px; 
            box-sizing: border-box; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            font-size: 15px; 
            transition: 0.3s; 
            font-family: inherit; 
            background-color: #fff;
        }
        .bh-form-group input:focus, .bh-form-group textarea:focus, .bh-form-group select:focus { 
            border-color: #b58b5a; 
            outline: none; 
            box-shadow: 0 0 5px rgba(181, 139, 90, 0.3); 
        }
        .bh-row { display: flex; gap: 20px; }
        .bh-col { flex: 1; }
        .btn-submit-bh { 
            background-color: #b58b5a; 
            color: #fff; 
            padding: 14px 20px; 
            border: none; 
            cursor: pointer; 
            width: 100%; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: bold; 
            transition: 0.3s; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 10px; 
        }
        .btn-submit-bh:hover { background-color: #9c764a; }

        /* CSS TAB */
        .bh-tabs-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e4e4e7;
        }
        .bh-tab-btn {
            flex: 1;
            padding: 14px 18px;
            border: none;
            background: #f4f4f5;
            color: #71717a;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .bh-tab-btn.active {
            background: #b58b5a;
            color: #fff;
        }
        .bh-tab-content {
            display: none;
        }
        .bh-tab-content.active {
            display: block;
        }

        /* KHUNG AI UPLOAD PDF */
        .ai-pdf-card {
            background: linear-gradient(135deg, #fdfbf7 0%, #eef2f5 100%);
            border: 2px dashed #b58b5a;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .ai-pdf-card h4 { margin: 0 0 10px 0; color: #b58b5a; font-size: 16px; }
        .ai-pdf-card p { margin: 0 0 15px 0; color: #666; font-size: 13px; }
        .btn-ai-scan {
            background: #2b303a;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-ai-scan:hover { background: #000; }

        #prism-toast.toast-show { bottom: 40px !important; opacity: 1 !important; pointer-events: auto !important; }
        @keyframes lightSweep { 0% { transform: translateX(-100%) skewX(-45deg); opacity: 1; } 100% { transform: translateX(100%) skewX(-45deg); opacity: 0; } }

        /* CSS Nút chuyển ngôn ngữ hình Capsule bo tròn */
.lang-switcher-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #f8f9fa; /* Nền xám rất nhẹ */
    border: 1px solid #e2e8f0; /* Viền xám mảnh */
    border-radius: 30px;        /* Bo tròn dạng viên thuốc (Pill) */
    padding: 4px 14px;
    font-size: 13px;
    font-weight: 500;
}

/* Icon quả địa cầu màu vàng */
.lang-switcher-pill .fa-globe {
    color: #b58b5a; 
    font-size: 14px;
}

/* Kiểu chữ của các ngôn ngữ */
.lang-switcher-pill a {
    text-decoration: none;
    color: #4A5568; /* Màu chữ chưa chọn (xám đậm) */
    transition: color 0.2s ease;
}

/* Ngôn ngữ đang được chọn (Màu vàng ánh kim) */
.lang-switcher-pill a.active {
    color: #b58b5a;
    font-weight: bold;
}

.lang-switcher-pill a:hover {
    color: #b58b5a;
}

/* Dấu gạch đứng ngăn cách */
.lang-switcher-pill .lang-divider {
    color: #cbd5e0;
    font-weight: 300;
}
    </style>
</head>
<body>

    <div class="profile-header" id="smart-profile-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 30px;">
        <!-- 1. LOGO TRÊN HEADER -->
        <a href="index.php" class="header-logo" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
            <img src="image/logo.png" alt="Logo" style="height: 30px;"> TIMELESS
        </a>

        <!-- 2. KHU VỰC BÊN PHẢI: GIỎ HÀNG & NÚT CHUYỂN NGÔN NGỮ NẰM LIỀN KỀ NHAU -->
        <div style="display: flex; align-items: center; gap: 20px;">
            
            <!-- Nút Giỏ Hàng -->
            <a href="cart.php" class="icon-cart" style="text-decoration: none; color: #333; font-weight: bold; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
            </a>

            <!-- Đường gạch đứng ngăn cách -->
            <span style="color: #ddd;">|</span>

            <!-- 🎯 NÚT NGÔN NGỮ NẰM KẾ BÊN GIỎ HÀNG -->
<!-- NÚT NGÔN NGỮ ĐÚNG CHUẨN GIAO DIỆN MẪU -->
<div class="lang-switcher-pill">
    <i class="fa-solid fa-globe"></i>
    <a href="<?= getLangUrl('vi') ?>" class="<?= $current_lang == 'vi' ? 'active' : '' ?>">VI</a>
    <span class="lang-divider">|</span>
    <a href="<?= getLangUrl('en') ?>" class="<?= $current_lang == 'en' ? 'active' : '' ?>">EN</a>
    <span class="lang-divider">|</span>
    <a href="<?= getLangUrl('ja') ?>" class="<?= $current_lang == 'ja' ? 'active' : '' ?>">JA</a>
</div>
</div>
</div>

    <div class="container">
        <!-- USER BANNER -->
        <div class="user-banner">
            <div class="user-info">
                <div class="user-avatar"><i class="fa fa-user"></i></div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user_data['ho_ten']); ?></h3>
                    <p><?php echo htmlspecialchars($user_data['so_dien_thoai']); ?></p>
                </div>
            </div>
            <div class="user-stat"><div class="stat-icon"><i class="fa fa-check"></i></div><div class="stat-info"><h2><?php echo $tong_don; ?></h2><p>Đơn hàng</p></div></div>
            <div class="user-stat"><div class="stat-icon"><i class="fa fa-dollar-sign"></i></div><div class="stat-info"><h2><?php echo $tich_luy; ?>đ</h2><p>Tích lũy</p></div></div>
        </div>
        
        <div class="main-content">
            <!-- SIDEBAR -->
            <div class="sidebar">
                <ul>
                    <li><a href="index.php"><i class="fa fa-arrow-left"></i> Trang chủ</a></li>
                    <li><a href="profile.php"><i class="fa fa-home"></i> Tổng quan</a></li>
                    <li><a href="profile-history.php"><i class="fa fa-history"></i> Lịch sử mua hàng</a></li>
                    <li><a href="profile-info.php"><i class="fa fa-user"></i> Thông tin tài khoản</a></li>
                    <li><a href="bao_hanh.php" class="active"><i class="fa fa-shield-halved"></i> Bảo hành sản phẩm</a></li>
                    <li><a href="profile.php?tab=feedback"><i class="fa fa-envelope"></i> Phản hồi - Hỗ Trợ</a></li>
                    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </div>

            <!-- CONTENT AREA -->
            <div class="content-area">
                <div class="info-section">
                    <div class="section-title">Yêu cầu bảo hành sản phẩm</div>
                    
                    <div class="bh-form-container">
                        
                        <!-- Thanh chuyển 2 Tab -->
                        <div class="bh-tabs-nav">
                            <button class="bh-tab-btn active" onclick="switchBhTab('tab-ai-mode', this)">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> 1. Tự Động Bằng AI (Có PDF)
                            </button>
                            <button class="bh-tab-btn" onclick="switchBhTab('tab-manual-mode', this)">
                                <i class="fa-solid fa-pen-to-square"></i> 2. Nhập Thủ Công (Không PDF)
                            </button>
                        </div>

                        <!-- ================================================================= -->
                        <!-- TAB 1: AI TỰ ĐỘNG BÓC TÁCH KHÔNG CẦN CHỌN TAY -->
                        <!-- ================================================================= -->
                        <div id="tab-ai-mode" class="bh-tab-content active">
                            <form action="" method="POST" enctype="multipart/form-data" id="form_ai_mode">
                                
                                <div class="ai-pdf-card">
                                    <h4><i class="fa-solid fa-file-pdf"></i> Tải Phiếu Bảo Hành (File PDF)</h4>
                                    <p>Tải phiếu bảo hành PDF lên, bấm "Phân tích AI". Hệ thống sẽ tự bóc tách Đơn hàng & Tên sản phẩm giúp bạn!</p>
                                    <div style="display: flex; gap: 10px; justify-content: center; align-items: center; max-width: 500px; margin: 0 auto;">
                                        <input type="file" id="ai_pdf_file" accept="application/pdf" style="margin: 0;">
                                        <button type="button" class="btn-ai-scan" id="btn_scan_ai">
                                            <i class="fa-solid fa-robot"></i> Phân tích AI
                                        </button>
                                    </div>
                                    <small id="ai_loading_text" style="display:none; color: #b58b5a; font-weight: bold; margin-top: 10px;">
                                        ⏳ Azure AI đang đọc dữ liệu file PDF, vui lòng đợi vài giây...
                                    </small>
                                    <small id="ai_success_badge" style="display:none; color: #28a745; font-weight: bold; margin-top: 10px; font-size: 14px;">
                                        ✅ Đã nhận diện Đơn hàng thành công!
                                    </small>
                                </div>

                                <!-- Input Ẩn lưu Họ tên, SĐT, Mã Đơn -->
                                <input type="hidden" name="ho_ten" value="<?php echo htmlspecialchars($user_data['ho_ten']); ?>">
                                <input type="hidden" name="so_dien_thoai" value="<?php echo htmlspecialchars($user_data['so_dien_thoai']); ?>">
                                <input type="hidden" name="ma_don_hang" id="ai_hidden_ma_don_hang" value="">

                                <div class="bh-form-group">
                                    <label>Mô tả chi tiết sự cố / Sản phẩm bị lỗi (*)</label>
                                    <textarea name="mo_ta_loi" id="ai_mo_ta_loi" rows="4" placeholder="Nhập chi tiết tình trạng lỗi sản phẩm..." required></textarea>
                                </div>

                                <div class="bh-form-group">
                                    <label>Hình ảnh thực tế đính kèm (Bắt buộc) (*)</label>
                                    <input type="file" name="hinh_anh" accept="image/*" required>
                                </div>

                                <button type="submit" name="btn_submit_baohanh" class="btn-submit-bh">
                                    <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Bảo Hành
                                </button>
                            </form>
                        </div>

                        <!-- ================================================================= -->
                        <!-- TAB 2: NHẬP THỦ CÔNG (ĐẦY ĐỦ CÁC Ô) -->
                        <!-- ================================================================= -->
                        <div id="tab-manual-mode" class="bh-tab-content">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="bh-row">
                                    <div class="bh-col bh-form-group">
                                        <label>Họ và tên người gửi (*)</label>
                                        <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($user_data['ho_ten']); ?>" required>
                                    </div>
                                    <div class="bh-col bh-form-group">
                                        <label>Số điện thoại liên hệ (*)</label>
                                        <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($user_data['so_dien_thoai']); ?>" required>
                                    </div>
                                </div>

                                <div class="bh-form-group">
                                    <label>Chọn Đơn hàng cần bảo hành (*) <small style="color: #888;">(Chỉ hiển thị các đơn Đã giao thành công)</small></label>
                                    <select name="ma_don_hang" required>
                                        <option value="">-- Chọn đơn hàng bạn đã nhận --</option>
                                        <?php 
                                        $sql_orders = "SELECT * FROM don_hang WHERE id_nguoi_dung = '$user_id' AND trang_thai = 'Đã giao' ORDER BY id DESC";
                                        $res_orders = $conn->query($sql_orders);
                                        
                                        if ($res_orders && $res_orders->num_rows > 0) {
                                            while ($ord = $res_orders->fetch_assoc()) {
                                                $tong_fmt = number_format($ord['tong_tien'], 0, ',', '.') . 'đ';
                                                echo "<option value='".$ord['id']."'>";
                                                echo "Mã đơn hàng #".$ord['id']." - Tổng thanh toán: ".$tong_fmt;
                                                echo "</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>Bạn chưa có đơn hàng nào 'Đã giao' để gửi bảo hành</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="bh-form-group">
                                    <label>Mô tả chi tiết sự cố / Sản phẩm bị lỗi (*)</label>
                                    <textarea name="mo_ta_loi" rows="4" placeholder="Nhập tên sản phẩm và lỗi..." required></textarea>
                                </div>

                                <div class="bh-form-group">
                                    <label>Hình ảnh thực tế đính kèm (Bắt buộc) (*)</label>
                                    <input type="file" name="hinh_anh" accept="image/*" required>
                                </div>

                                <button type="submit" name="btn_submit_baohanh" class="btn-submit-bh">
                                    <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Bảo Hành
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT CHUYỂN TAB, TOAST VÀ AI SCAN -->
    <script>
        function switchBhTab(tabId, btnElement) {
            var contents = document.getElementsByClassName('bh-tab-content');
            for (var i = 0; i < contents.length; i++) {
                contents[i].classList.remove('active');
            }
            var buttons = document.getElementsByClassName('bh-tab-btn');
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].classList.remove('active');
            }
            document.getElementById(tabId).classList.add('active');
            btnElement.classList.add('active');
        }

        function showGlassPrismToast(message, iconClass, color) {
            const toast = document.getElementById('prism-toast');
            const lightBar = document.getElementById('light-bar');
            const toastIcon = document.getElementById('toast-icon');
            const toastText = document.getElementById('toast-text');

            toastText.innerText = message;
            toastIcon.className = 'fa-solid ' + iconClass;
            toastIcon.style.color = color;
            lightBar.style.background = `linear-gradient(90deg, transparent, ${color}, ${color}, transparent)`;

            toast.classList.add('toast-show');
            lightBar.style.animation = 'none';
            void lightBar.offsetWidth; 
            lightBar.style.animation = 'lightSweep 5s ease-out forwards';
            
            setTimeout(() => { toast.classList.remove('toast-show'); }, 5500);
        }

        <?php if ($toast_msg != ""): ?>
            window.onload = function() {
                var msg = "<?php echo addslashes($toast_msg); ?>";
                var type = "<?php echo $toast_type; ?>";
                var icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
                var color = type === 'success' ? '#b58b5a' : '#d9534f';
                showGlassPrismToast(msg, icon, color);
            };
        <?php endif; ?>

        // GỬI FILE PDF SANG AZURE AI BẰNG AJAX
        document.getElementById('btn_scan_ai').addEventListener('click', function() {
            var fileInput = document.getElementById('ai_pdf_file');
            var loadingText = document.getElementById('ai_loading_text');
            var successBadge = document.getElementById('ai_success_badge');

            if (fileInput.files.length === 0) {
                showGlassPrismToast("Vui lòng chọn 1 file PDF bảo hành trước!", "fa-circle-xmark", "#d9534f");
                return;
            }

            var formData = new FormData();
            formData.append('action', 'scan_pdf_ai');
            formData.append('pdf_file', fileInput.files[0]);

            loadingText.style.display = 'block';
            successBadge.style.display = 'none';

            fetch('bao_hanh.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingText.style.display = 'none';
                if (data.status === 'success') {
                    if (data.order_id) {
                        document.getElementById('ai_hidden_ma_don_hang').value = data.order_id;
                        
                        var spInfo = data.ten_san_pham ? (" - " + data.ten_san_pham) : "";
                        successBadge.innerHTML = "✅ Azure AI đã nhận diện: Đơn hàng #" + data.order_id + spInfo;
                        successBadge.style.display = 'block';

                        showGlassPrismToast("Bóc tách thành công Đơn hàng #" + data.order_id, "fa-circle-check", "#b58b5a");
                    } else {
                        showGlassPrismToast("Không tìm thấy Mã Đơn Hàng trong PDF. Vui lòng thử Tab Nhập Thủ Công!", "fa-circle-xmark", "#d9534f");
                    }
                } else {
                    showGlassPrismToast("Lỗi: " + data.message, "fa-circle-xmark", "#d9534f");
                }
            })
            .catch(error => {
                loadingText.style.display = 'none';
                showGlassPrismToast("Lỗi kết nối tới Azure AI!", "fa-circle-xmark", "#d9534f");
            });
        });

        // BẢO VỆ TAB AI KHI SUBMIT CHƯA SCAN PDF
        document.getElementById('form_ai_mode').addEventListener('submit', function(e) {
            var orderId = document.getElementById('ai_hidden_ma_don_hang').value;
            if (!orderId) {
                e.preventDefault();
                showGlassPrismToast("Vui lòng chọn file PDF và bấm 'Phân tích AI' trước!", "fa-circle-xmark", "#d9534f");
            }
        });
    </script>
<?php
include 'ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>