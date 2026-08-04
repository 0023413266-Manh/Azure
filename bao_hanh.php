<?php
session_start();
include 'admin/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// XỬ LÝ LƯU YÊU CẦU BẢO HÀNH
if (isset($_POST['btn_submit_baohanh'])) {
    $ho_ten = $conn->real_escape_string(trim($_POST['ho_ten']));
    $so_dien_thoai = $conn->real_escape_string(trim($_POST['so_dien_thoai']));
    $ma_don_hang = (int)$_POST['ma_don_hang'];
    $mo_ta_loi = $conn->real_escape_string(trim($_POST['mo_ta_loi']));
    $ngay_mua = date('Y-m-d');

    // 1. KIỂM TRA ĐƠN HÀNG CÓ TỒN TẠI, THUỘC USER VÀ ĐÃ GIAO HAY CHƯA
    $check_order = $conn->query("SELECT * FROM don_hang WHERE id = '$ma_don_hang' AND id_nguoi_dung = '$user_id' AND trang_thai = 'Đã giao'");

    if ($check_order->num_rows == 0) {
        $_SESSION['toast_msg'] = "Đơn hàng #$ma_don_hang không hợp lệ hoặc chưa được giao thành công!";
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

    // 3. THÊM YÊU CẦU BẢO HÀNH VÀO DATABASE
    $ten_san_pham_default = "Sản phẩm thuộc Đơn hàng #" . $ma_don_hang;
    $sql_insert = "INSERT INTO yeu_cau_bao_hanh (id_nguoi_dung, ho_ten, so_dien_thoai, ma_dong_ho, so_series, ngay_mua, mo_ta_loi, hinh_anh, trang_thai, ngay_tao) 
                   VALUES ('$user_id', '$ho_ten', '$so_dien_thoai', '$ma_don_hang', '$ten_san_pham_default', '$ngay_mua', '$mo_ta_loi', '$hinh_anh', 'Đang chờ', NOW())";

    if ($conn->query($sql_insert) === TRUE) {
        $_SESSION['toast_msg'] = "Gửi yêu cầu bảo hành thành công! Admin sẽ kiểm tra đơn hàng của bạn.";
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

        #prism-toast.toast-show { bottom: 40px !important; opacity: 1 !important; pointer-events: auto !important; }
        @keyframes lightSweep { 0% { transform: translateX(-100%) skewX(-45deg); opacity: 1; } 100% { transform: translateX(100%) skewX(-45deg); opacity: 0; } }
    </style>
</head>
<body>

    <!-- TOAST NOTIFICATION -->
    <div id="prism-toast" style="position: fixed; bottom: -100px; right: 30px; background: rgba(15, 15, 15, 0.85); backdrop-filter: blur(15px); color: #fff; padding: 18px 28px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 15px 35px rgba(0,0,0,0.3); font-size: 16px; z-index: 9999999; display: flex; align-items: center; gap: 12px; transition: all 0.5s; opacity: 0; pointer-events: none; overflow: hidden;">
        <i id="toast-icon" class="fa-solid fa-circle-check" style="font-size: 22px;"></i>
        <span id="toast-text"></span>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background: rgba(0, 0, 0, 0.5);">
            <div id="light-bar" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
        </div>
    </div>

    <!-- HEADER -->
    <div class="profile-header" id="smart-profile-header">
        <div class="header-logo"><img src="image/logo.png" alt="Logo"> TIMELESS</div>
        <form action="search.php" method="GET" class="search-bar">
            <input type="text" name="query" placeholder="Tìm kiếm..." required style="border: none; outline: none; background: transparent; width: 100%;">
            <button type="submit" style="border: none; background: transparent; cursor: pointer; color: #888;"><i class="fa fa-search"></i></button>
        </form>
        <a href="cart.php" class="header-cart" style="text-decoration: none; color: inherit;">Giỏ hàng <i class="fa fa-shopping-cart"></i></a>
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
                        
                        <!-- CỤM TÍCH HỢP AZURE AI DOCUMENT INTELLIGENCE -->
                        <div class="bh-form-group" style="background: #fdfbf7; padding: 18px; border-radius: 8px; border: 1px dashed #b58b5a; margin-bottom: 25px;">
                            <label style="color: #b58b5a; font-size: 15px; margin-bottom: 5px;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Tự động điền dữ liệu bằng Azure AI Document Intelligence
                            </label>
                            <p style="font-size: 13px; color: #666; margin-bottom: 12px;">Upload ảnh/PDF Hóa đơn hoặc Thẻ bảo hành để AI tự động quét mã đơn hàng và trích xuất thông tin!</p>
                            
                            <div style="display: flex; gap: 10px;">
                                <input type="file" id="ai_document_file" accept="image/*,application/pdf" style="flex: 1; padding: 8px;">
                                <button type="button" onclick="scanDocumentWithAzureAI()" id="btn-scan-ai" style="background: #28a745; color: white; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: bold; white-space: nowrap; transition: 0.3s;">
                                    <i class="fa-solid fa-robot"></i> Quét bằng AI
                                </button>
                            </div>
                            <div id="ai-loading" style="display: none; margin-top: 10px; color: #007bff; font-size: 13px;">
                                <i class="fa-solid fa-spinner fa-spin"></i> Azure AI đang đọc dữ liệu từ hóa đơn/thẻ bảo hành...
                            </div>
                        </div>

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
                                <select name="ma_don_hang" id="ma_don_hang_select" required>
                                    <option value="">-- Chọn đơn hàng bạn đã nhận --</option>
                                    <?php 
                                    // Lấy các đơn hàng ĐÃ GIAO của User
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
                                <textarea name="mo_ta_loi" id="mo_ta_loi_textarea" rows="4" placeholder="Nhập tên sản phẩm cần bảo hành và tình trạng lỗi (Ví dụ: Đồng hồ Seiko trong đơn bị vào nước, rạn kính...)" required></textarea>
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

    <!-- AZURE AI DOCUMENT INTELLIGENCE SCRIPT -->
    <script>
    async function scanDocumentWithAzureAI() {
        const fileInput = document.getElementById('ai_document_file');
        const loadingDiv = document.getElementById('ai-loading');
        const btnScan = document.getElementById('btn-scan-ai');

        if (!fileInput.files || fileInput.files.length === 0) {
            showGlassPrismToast("Vui lòng chọn ảnh/PDF hóa đơn trước khi quét!", "fa-circle-exclamation", "#f0ad4e");
            return;
        }

        const file = fileInput.files[0];

        // Thông số kết nối Azure AI
        const azureKey = "BLgMSgLhoU27SWH98R2NMK9kdJ7kJfKSlFxO8i1M8gVT7KkDUvmrJQQJ99CHACqBBLyXJ3w3AAALACOGbftF"; 
        const azureEndpoint = "https://timeless-doc-ai.cognitiveservices.azure.com/";

        const apiUrl = "https://timeless-doc-ai.cognitiveservices.azure.com/documentintelligence/documentModels/prebuilt-read:analyze?api-version=2023-07-31";

        loadingDiv.style.display = 'block';
        btnScan.disabled = true;

        try {
            // Check an toàn cho nút bấm & loading trước khi chạy
            if (typeof loadingDiv !== 'undefined' && loadingDiv) loadingDiv.style.display = 'block';
            if (typeof btnScan !== 'undefined' && btnScan) btnScan.disabled = true;

            // Bước 1: Gửi Request lên Azure AI
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Ocp-Apim-Subscription-Key': azureKey,
                    'Content-Type': file.type || 'application/octet-stream'
                },
                body: file
            });

            if (!response.ok) {
                console.warn(`[Azure Status ${response.status}]: Dịch vụ Azure AI chưa sẵn sàng.`);
                throw new Error("Hệ thống quét AI đang bảo trì. Bạn vui lòng chọn/nhập mã đơn hàng thủ công nhé!");
            }

            const operationLocation = response.headers.get('Operation-Location');
            if (!operationLocation) {
                throw new Error("Không thể lấy vị trí xử lý từ Azure. Vui lòng thử lại với ảnh rõ hơn!");
            }

            // Bước 2: Polling lấy kết quả
            let resultData = null;
            let attempts = 0;
            const maxAttempts = 15; // Tránh vòng lặp vô tận (Timeout sau 22.5s)

            while (attempts < maxAttempts) {
                attempts++;
                await new Promise(r => setTimeout(r, 1500));
                
                const resultResponse = await fetch(operationLocation, {
                    headers: { 'Ocp-Apim-Subscription-Key': azureKey }
                });
                
                if (!resultResponse.ok) {
                    throw new Error("Lỗi khi kết nối lấy kết quả từ Azure!");
                }

                resultData = await resultResponse.json();

                if (resultData.status === 'succeeded') break;
                if (resultData.status === 'failed') throw new Error("Azure AI không thể nhận diện dữ liệu trong ảnh này!");
            }

            if (!resultData || resultData.status !== 'succeeded') {
                throw new Error("Quá thời gian xử lý ảnh (Timeout). Vui lòng thử lại!");
            }

            // Bước 3: Trích xuất thông tin
            const fields = resultData.analyzeResult?.documents?.[0]?.fields;
            
            if (fields) {
                const invoiceId = fields.InvoiceId?.valueString || fields.InvoiceId?.content || "";
                const selectOrder = document.getElementById('ma_don_hang_select');

                if (invoiceId && selectOrder) {
                    const cleanId = invoiceId.replace(/[^0-9]/g, '');
                    let matched = false;

                    for (let i = 0; i < selectOrder.options.length; i++) {
                        if (selectOrder.options[i].value === cleanId) {
                            selectOrder.selectedIndex = i;
                            matched = true;
                            break;
                        }
                    }
                    
                    // Nếu mã đơn chưa có trong danh sách dropdown -> Tự thêm option mới
                    if (!matched && cleanId) {
                        const newOption = new Option(`Đơn hàng #${cleanId} (AI nhận diện)`, cleanId, true, true);
                        selectOrder.add(newOption);
                    }
                }

                // Điền mô tả chi tiết
                const textareaMoTa = document.getElementById('mo_ta_loi_textarea');
                if (textareaMoTa) {
                    let aiSummary = "[Dữ liệu Azure AI tự động quét từ Hóa đơn/Thẻ]:\n";
                    if (invoiceId) aiSummary += `• Mã đơn/Hóa đơn: ${invoiceId}\n`;
                    if (fields.InvoiceDate?.content) aiSummary += `• Ngày lập: ${fields.InvoiceDate.content}\n`;
                    if (fields.CustomerName?.content) aiSummary += `• Tên khách hàng: ${fields.CustomerName.content}\n`;
                    
                    textareaMoTa.value = aiSummary + "\n[Mô tả tình trạng lỗi/hư hỏng sản phẩm tại đây]: ";
                }

                // Thông báo thành công
                if (typeof showGlassPrismToast === 'function') {
                    showGlassPrismToast("Azure AI đã quét và điền dữ liệu thành công!", "fa-circle-check", "#b58b5a");
                } else {
                    alert("Azure AI đã quét thành công!");
                }

            } else {
                if (typeof showGlassPrismToast === 'function') {
                    showGlassPrismToast("AI đã quét nhưng không tìm thấy dữ liệu hóa đơn!", "fa-circle-exclamation", "#f0ad4e");
                } else {
                    alert("AI đã quét nhưng không đọc được thông tin!");
                }
            }

        } catch (error) {
            console.error("Scan error details:", error);

            // Hiển thị thông báo an toàn (Tránh crash nếu hàm Toast bị thiếu)
            if (typeof showGlassPrismToast === 'function') {
                showGlassPrismToast(error.message, "fa-circle-exclamation", "#d9534f");
            } else if (typeof showToast === 'function') {
                showToast(error.message, "warning");
            } else {
                alert(error.message);
            }

            // Focus an toàn vào thẻ select mã đơn hàng
            const selectOrder = document.getElementById('ma_don_hang_select');
            if (selectOrder) selectOrder.focus();

        } finally {
            // Luôn ẩn Loading và trả lại trạng thái cho Nút bấm
            if (typeof loadingDiv !== 'undefined' && loadingDiv) loadingDiv.style.display = 'none';
            if (typeof btnScan !== 'undefined' && btnScan) btnScan.disabled = false;
        }
    }
    </script>

    <!-- TOAST SCRIPT -->
    <script>
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
    </script>
</body>
</html>