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

// 1. Chặn người chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// =========================================================================
// XỬ LÝ LƯU ĐƠN HÀNG VÀO DATABASE & GỬI EMAIL XÁC NHẬN KÈM FILE PDF BẰNG AZURE
// =========================================================================
if (isset($_POST['btn_place_order'])) {
    // Chống hack SQL
    $ho_ten = $conn->real_escape_string($_POST['ho_ten_nguoi_nhan']);
    $sdt = $conn->real_escape_string($_POST['so_dien_thoai']);
    $dia_chi = $conn->real_escape_string($_POST['dia_chi_giao_hang']);
    
    $selected_ids_str = $_POST['selected_items']; 
    $arr_ids = explode(',', $selected_ids_str);
    
    // ---------------------------------------------------------
    // BƯỚC 1: KIỂM TRA TỒN KHO TRƯỚC KHI CHO ĐẶT HÀNG
    // ---------------------------------------------------------
    $het_hang = false;
    $thong_bao_loi = "";
    
    foreach ($arr_ids as $id_sp) {
        $id_sp = intval($id_sp);
        $qty = isset($_SESSION['cart'][$id_sp]) ? $_SESSION['cart'][$id_sp] : 1;
        
        $check_kho = $conn->query("SELECT ten_san_pham, ton_kho FROM san_pham WHERE id = $id_sp")->fetch_assoc();
        
        if ($check_kho && $check_kho['ton_kho'] < $qty) {
            $het_hang = true;
            $thong_bao_loi .= "Sản phẩm [" . $check_kho['ten_san_pham'] . "] chỉ còn " . $check_kho['ton_kho'] . " chiếc.\n";
        }
    }
    
    if ($het_hang) {
        $_SESSION['toast_msg'] = "Rất tiếc! Một số sản phẩm trong đơn đã hết hàng hoặc không đủ số lượng. Vui lòng kiểm tra lại.";
        $_SESSION['toast_type'] = "error";
        header("Location: cart.php");
        exit();
    }
    
    // ---------------------------------------------------------
    // BƯỚC 2: TẠO ĐƠN HÀNG VÀ LƯU CHI TIẾT VÀO DATABASE
    // ---------------------------------------------------------
    // 2.1. Tự động lưu địa chỉ & SĐT mới vào Profile của khách
    $conn->query("UPDATE nguoi_dung SET ho_ten = '$ho_ten', so_dien_thoai = '$sdt', dia_chi = '$dia_chi' WHERE id = $user_id");
    $_SESSION['ho_ten'] = $ho_ten; 
    
    // 2.2. Tính lại tổng tiền
    $tong_tien = 0;
    foreach ($arr_ids as $id_sp) {
        $id_sp = intval($id_sp);
        $qty = isset($_SESSION['cart'][$id_sp]) ? $_SESSION['cart'][$id_sp] : 1;
        $sp_info = $conn->query("SELECT gia_ban FROM san_pham WHERE id = $id_sp")->fetch_assoc();
        if ($sp_info) {
            $tong_tien += ($sp_info['gia_ban'] * $qty);
        }
    }

    if ($tong_tien > 0) {
        // 2.3. Tạo đơn hàng chính trong bảng don_hang
        $sql_don_hang = "INSERT INTO don_hang (id_nguoi_dung, tong_tien, trang_thai, dia_chi_giao_hang, sdt_nguoi_nhan) 
                         VALUES ($user_id, $tong_tien, 'Chờ xác nhận', '$dia_chi', '$sdt')";
        
        if ($conn->query($sql_don_hang) === TRUE) {
            $id_don_hang = $conn->insert_id; // Lấy ID đơn hàng vừa tạo
            
            $ten_san_pham_arr = []; // Khởi tạo mảng lưu tên các sản phẩm để dùng cho Azure PDF
            
            // 2.4. Lưu từng sản phẩm vào bảng chi_tiet_don_hang & trừ kho
            foreach ($arr_ids as $id_sp) {
                $id_sp = intval($id_sp);
                $qty = isset($_SESSION['cart'][$id_sp]) ? $_SESSION['cart'][$id_sp] : 1;
                $sp_info = $conn->query("SELECT ten_san_pham, gia_ban FROM san_pham WHERE id = $id_sp")->fetch_assoc();
                
                if ($sp_info) {
                    $gia = $sp_info['gia_ban'];
                    $ten_san_pham_arr[] = $sp_info['ten_san_pham'];
                    
                    // Lưu vào chi tiết đơn hàng
                    $conn->query("INSERT INTO chi_tiet_don_hang (id_don_hang, id_san_pham, so_luong, don_gia) 
                                  VALUES ($id_don_hang, $id_sp, $qty, $gia)");
                    
                    // Trừ số lượng tồn kho
                    $conn->query("UPDATE san_pham SET ton_kho = ton_kho - $qty WHERE id = $id_sp");

                    // Xóa sản phẩm khỏi giỏ hàng
                    if (isset($_SESSION['cart'][$id_sp])) {
                        unset($_SESSION['cart'][$id_sp]); 
                    }
                }
            }

            // Nối danh sách tên sản phẩm thành chuỗi text (dùng cho dữ liệu truyền tới Azure Function)
            $ten_san_pham_str = implode(', ', $ten_san_pham_arr);

            // =========================================================
            // BƯỚC 3: 🔔 BẮN THÔNG BÁO SIGNALR REAL-TIME
            // =========================================================
            require_once 'azure_signalr.php';
            $ten_hien_thi = $_SESSION['user_name'] ?? $_SESSION['ten_nguoi_dung'] ?? "Khách hàng #$user_id";

            $order_info = [
                'order_id'      => $id_don_hang,
                'customer_name' => $ten_hien_thi,
                'total_price'   => number_format($tong_tien) . ' VNĐ'
            ];
            sendOrderNotification($order_info);

            // =========================================================================
            // BƯỚC 4: 📄 TẠO FILE PDF PHIẾU BẢO HÀNH BẰNG AZURE FUNCTION
            // =========================================================================
            $pdf_base64 = "";
            try {
                // Lấy thông tin đơn hàng thực tế
                $payload_pdf = [
                    'id_don_hang'  => $id_don_hang,
                    'ho_ten'       => $ho_ten,            // Tên khách nhập ở Form
                    'sdt'          => $sdt,               // SĐT khách nhập ở Form
                    'ten_san_pham' => $ten_san_pham_str,  // Tên đồng hồ trong giỏ
                    'ngay_mua'     => date('d/m/Y')
                ];

                // 🌐 ĐỊA CHỈ AZURE FUNCTION CHÍNH THỨC
                $azure_function_url = 'https://timeless-pdf-service-c0cfbuewcfg6c6f0.southeastasia-01.azurewebsites.net/api/GeneratePDF';

                $ch_pdf = curl_init($azure_function_url);
                curl_setopt_array($ch_pdf, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => json_encode($payload_pdf, JSON_UNESCAPED_UNICODE),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);

                $pdf_response  = curl_exec($ch_pdf);
                // 🛑 ĐÃ THÊM: Lấy HTTP Code từ Azure Function trả về
                $http_code_pdf = curl_getinfo($ch_pdf, CURLINFO_HTTP_CODE); 
                curl_close($ch_pdf);

                if ($http_code_pdf === 200 && !empty($pdf_response)) {
                    $json_data = json_decode($pdf_response, true);
                    if (isset($json_data['pdfBase64'])) {
                        $pdf_base64 = $json_data['pdfBase64'];
                    } else {
                        $pdf_base64 = base64_encode($pdf_response);
                    }
                } else {
                    error_log("Lỗi Azure Function PDF [HTTP $http_code_pdf]: " . $pdf_response);
                }

            } catch (Exception $e) {
                error_log("Lỗi gọi Azure Function PDF: " . $e->getMessage());
            }
// =========================================================================
            // BƯỚC 5: ✉️ GỬI EMAIL XÁC NHẬN KÈM FILE PDF BẢO HÀNH (AZURE ACS)
            // =========================================================================
            try {
                $user_query  = $conn->query("SELECT email FROM nguoi_dung WHERE id = $user_id")->fetch_assoc();
                $email_khach = trim($user_query['email'] ?? '');

                if (!empty($email_khach)) {
                    // 🛑 Dùng require_once để chống trùng lặp hàm
                    require_once __DIR__ . '/email_logo.php';
                    require_once __DIR__ . '/email_template.php';

                    $final_email_html = getEmailHtml($ho_ten, $id_don_hang, $logo_base64);

                    require_once __DIR__ . '/env_loader.php';

                    $acs_endpoint   = $_ENV['ACS_ENDPOINT'] ?? '';
                    $acs_accesskey  = $_ENV['ACS_ACCESS_KEY'] ?? '';
                    $sender_address = $_ENV['ACS_SENDER_ADDRESS'] ?? '';

                    $payload_data = [
                        "senderAddress" => $sender_address,
                        "recipients"    => ["to" => [["address" => $email_khach, "displayName" => $ho_ten]]],
                        "content"       => [
                            "subject" => "[Timeless Watch] Xac nhan don hang & Phieu bao hanh #" . $id_don_hang,
                            "html"    => $final_email_html
                        ]
                    ];

                    // 📎 ĐÍNH KÈM PHIẾU BẢO HÀNH PDF VÀO MAIL NẾU CÓ
                    if (!empty($pdf_base64)) {
                        $payload_data["attachments"] = [
                            [
                                "name"            => "PhieuBaoHanh_Timeless_#" . $id_don_hang . ".pdf",
                                "contentType"     => "application/pdf",
                                "contentInBase64" => $pdf_base64
                            ]
                        ];
                    }

                    $email_data = json_encode($payload_data, JSON_UNESCAPED_UNICODE);

                    $url_path     = '/emails:send?api-version=2023-03-31';
                    $host         = parse_url($acs_endpoint, PHP_URL_HOST);
                    $date         = gmdate('D, d M Y H:i:s \G\M\T');
                    $content_hash = base64_encode(hash('sha256', $email_data, true));
                    $str_to_sign  = "POST\n" . $url_path . "\n" . $date . ";" . $host . ";" . $content_hash;
                    $signature    = base64_encode(hash_hmac('sha256', $str_to_sign, base64_decode($acs_accesskey), true));

                    $ch = curl_init($acs_endpoint . $url_path);
                    curl_setopt_array($ch, [
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => $email_data,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER     => [
                            'Content-Type: application/json',
                            'Date: '                . $date,
                            'x-ms-content-sha256: ' . $content_hash,
                            'Authorization: HMAC-SHA256 SignedHeaders=date;host;x-ms-content-sha256&Signature=' . $signature,
                            'host: '                . $host,
                        ]
                    ]);
                    $response  = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($http_code !== 202) {
                        error_log("Azure Email Error [$http_code]: " . $response);
                    }
                }
            } catch (Exception $e) {
                error_log("Email error: " . $e->getMessage());
            }

            $_SESSION['toast_msg'] = "Chốt đơn thành công! Đồng hồ sắp về tay bạn.";
            $_SESSION['toast_type'] = "success";
            header("Location: profile-history.php");
            exit();
        } else {
            echo "<script>alert('Lỗi DB: " . $conn->error . "');</script>";
        }
    }
}

// =========================================================================
// KIỂM TRA DỮ LIỆU ĐẦU VÀO
// =========================================================================
if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
    header("Location: cart.php");
    exit();
}

$selected_items = $conn->real_escape_string($_POST['selected_items']);
$user_info = $conn->query("SELECT * FROM nguoi_dung WHERE id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f9f9f9; margin: 0; padding: 0; }
        .checkout-page { max-width: 1200px; margin: 60px auto 100px auto; display: flex; gap: 40px; padding: 0 20px; }
        .checkout-left { flex: 6; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #ddd; }
        .checkout-left h2 { font-family: 'Segoe UI', Arial, sans-serif; font-size: 22px; margin-top: 0; color: #333; margin-bottom: 25px; }
        .checkout-right { flex: 4; background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #ddd; height: fit-content; border-top: 4px solid #b58b5a; }
        .checkout-right h2 { font-family: 'Segoe UI', Arial, sans-serif; font-size: 20px; margin-top: 0; color: #333; margin-bottom: 20px; border-bottom: none;}
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-family: inherit; font-size: 15px; box-sizing: border-box; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #b58b5a; outline: none; }
        .checkout-item { display: flex; align-items: center; border-bottom: 1px solid #eee; padding: 15px 0; gap: 15px; }
        .checkout-item img { width: 60px; height: 60px; object-fit: contain; border-radius: 5px; border: 1px solid #ddd; padding: 3px; background: #fff;}
        .item-info { flex: 1; }
        .item-info h4 { margin: 0 0 5px 0; font-size: 14px; color: #333; line-height: 1.4; font-weight: 600;}
        .item-price { font-weight: 700; color: #b58b5a; font-size: 15px; }
        .total-box { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: #555; }
        .total-final { font-size: 20px; font-weight: 700; color: #d9534f; }
        .btn-confirm { width: 100%; padding: 15px; background: #b58b5a; color: #fff; font-size: 16px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; margin-top: 25px; }
        .btn-confirm:hover { background: #9c764a; }
        .notice-box { background: #fef9f1; border-left: 3px solid #b58b5a; padding: 12px; margin-bottom: 25px; font-size: 13px; color: #666; border-radius: 3px; }
                     .main-nav ul li a.active-menu {
            color: #b58b5a !important;
            font-weight: bold;
        }
        .user-box { display: flex; align-items: center; gap: 12px; }
        .lang-switch { display: flex; align-items: center; gap: 6px; background: #f8f8f8; padding: 4px 10px; border-radius: 20px; border: 1px solid #e0e0e0; font-size: 13px; }
        .lang-switch a { text-decoration: none; color: #555; font-weight: 600; transition: 0.2s; }
        .lang-switch a:hover, .lang-switch a.active { color: #b58b5a; }
        
    </style>
</head>

<body>

<div id="smart-header">
    <header class="top-header">
        <div class="profile-header" id="smart-profile-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0px;">
        <!-- 1. LOGO TRÊN HEADER -->
        <a href="index.php" class="header-logo" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
                <img src="image/logo.png" alt="Logo" style="height: 80px;"> 
                <span style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; letter-spacing: 5px;">TIMELESS</span>
            </a>
       

        <div class="user-box">
            <div class="lang-switch" translate="no">
                <i class="fa-solid fa-globe" style="color: #b58b5a;"></i>
                <a href="<?= getLangUrl('vi') ?>" class="<?= $current_lang == 'vi' ? 'active' : '' ?>">VI</a> | 
                <a href="<?= getLangUrl('en') ?>" class="<?= $current_lang == 'en' ? 'active' : '' ?>">EN</a> | 
                <a href="<?= getLangUrl('ja') ?>" class="<?= $current_lang == 'ja' ? 'active' : '' ?>">JA</a>
            </div>

            <?php if(isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                $get_name = $conn->query("SELECT ho_ten FROM nguoi_dung WHERE id = $uid");
                $ten_ngan = "User";
                if($get_name && $get_name->num_rows > 0) {
                    $row_name = $get_name->fetch_assoc();
                    $mang_ten = explode(' ', trim($row_name['ho_ten']));
                    $ten_ngan = end($mang_ten); 
                }
            ?>
                <a href="<?= $path ?>profile.php" style="text-decoration: none;"> 
                    <button class="btn-user" translate="no" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;">
                        <?= $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i>
                    </button>
                </a>
            <?php } else { ?>
                <a href="<?= $path ?>login.php" style="text-decoration: none;"> 
                    <button class="btn-user" translate="no">User <i class="fa-solid fa-circle-user"></i></button>
                </a>
            <?php } ?>
        </div>

        </header>

        <nav class="main-nav" style="border-bottom: 1px solid #eee; padding: 10px 0; background: #fff;">
            <ul style="width: 100%; max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
                <li style="flex: 1; text-align: left;">
                    <a href="index.php" style="color: #333; text-decoration: none; font-weight: bold; font-size: 13px;"><i class="fa-solid fa-arrow-left"></i> TIẾP TỤC MUA SẮM</a>
                </li>
                <li style="flex: 2; text-align: center; color: #b58b5a; font-weight: bold; font-size: 18px; pointer-events: none; letter-spacing: 1px;">
                    THANH TOÁN ĐƠN HÀNG
                </li>
                <li class="nav-icons" style="flex: 1; display: flex; justify-content: flex-end;">
                    <a href="cart.php" class="icon-cart" style="color: #b58b5a; text-decoration: none; font-size: 14px;">
                        <i class="fa-solid fa-cart-shopping"></i> <span class="cart-text">Giỏ hàng</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <form action="checkout.php" method="POST">
        <div class="checkout-page">
            
            <div class="checkout-left">
                <h2>Thông tin giao hàng</h2>
                
                <div class="notice-box">
                    <i class="fa-solid fa-circle-info"></i> Nếu bạn thay đổi thông tin ở đây, hệ thống sẽ tự động cập nhật lại sổ địa chỉ của bạn để tiện cho lần mua sau.
                </div>
                
                <div class="form-group">
                    <label>Họ và tên người nhận (*)</label>
                    <input type="text" name="ho_ten_nguoi_nhan" value="<?php echo htmlspecialchars($user_info['ho_ten']); ?>" required placeholder="VD: Nguyễn Văn A">
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại liên hệ (*)</label>
                    <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($user_info['so_dien_thoai']); ?>" required placeholder="Số điện thoại người nhận hàng">
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ giao hàng chi tiết (*)</label>
                    <textarea name="dia_chi_giao_hang" rows="4" required placeholder="Vui lòng ghi rõ Số nhà, Phường/Xã, Quận/Huyện, Tỉnh/Thành phố..."><?php echo htmlspecialchars($user_info['dia_chi']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <select name="phuong_thuc_tt" id="phuong_thuc_tt" required onchange="toggleBankInfo(this.value)">
                        <option value="COD">Thanh toán tiền mặt khi nhận hàng (COD)</option>
                        <option value="Chuyển khoản ngân hàng">Chuyển khoản qua Ngân hàng</option>
                    </select>
                </div>

                <div id="bank-info" style="display: none; background: #fef9f1; border: 1px dashed #b58b5a; border-radius: 8px; padding: 15px; margin-top: 15px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0; color: #b58b5a; border-bottom: 1px solid #e0c8a0; padding-bottom: 8px;"><i class="fa-solid fa-building-columns"></i> Thông tin chuyển khoản</h4>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                        <div style="width: 120px; height: 120px; background: #fff; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img src="https://img.vietqr.io/image/BANK_NAME-ACCOUNT_NUMBER-compact2.png?amount=100000&addInfo=Thanh%20toan%20don%20hang" alt="QR VietQR" />
                        </div>
                        
                        <div style="line-height: 1.6;">
                            <p style="margin: 0;"><strong>Ngân hàng:</strong> Vietcombank</p>
                            <p style="margin: 0;"><strong>Chủ tài khoản:</strong> PHAM PHUOC MANH</p>
                            <p style="margin: 0; font-size: 16px;"><strong>Số tài khoản:</strong> <span style="color: #d9534f; font-weight: bold; letter-spacing: 1px;">0123456789</span></p>
                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #666; background: #fff; padding: 5px 10px; border-radius: 4px; border: 1px solid #eee;">
                                <i class="fa-solid fa-pen-to-square"></i> Lời nhắn: <strong>SĐT của bạn</strong>
                            </p>
                        </div>
                    </div>
                    <p style="margin: 10px 0 0 0; font-size: 13px; color: #888; text-align: center; font-style: italic;">
                        *Admin sẽ gọi xác nhận đơn hàng ngay sau khi nhận được thanh toán.
                    </p>
                </div>
            </div>

            <div class="checkout-right">
                <h2>Tóm tắt đơn hàng</h2>
                
                <div class="order-items-list">
                    <?php 
                    $tong_tien_don_hang = 0;
                    $sql_sp = "SELECT * FROM san_pham WHERE id IN ($selected_items)";
                    $result_sp = $conn->query($sql_sp);
                    
                    if($result_sp && $result_sp->num_rows > 0):
                        while ($row = $result_sp->fetch_assoc()):
                            $id_sp = $row['id'];
                            $so_luong = isset($_SESSION['cart'][$id_sp]) ? $_SESSION['cart'][$id_sp] : 1;
                            $thanh_tien = $row['gia_ban'] * $so_luong;
                            $tong_tien_don_hang += $thanh_tien;
                    ?>
                    <div class="checkout-item">
                        <img src="<?php echo $row['anh_san_pham']; ?>" onerror="this.src='image/logo.png'">
                        <div class="item-info">
                            <h4><?php echo $row['ten_san_pham']; ?></h4>
                            <p>Số lượng: <b><?php echo $so_luong; ?></b></p>
                        </div>
                        <div class="item-price"><?php echo number_format($thanh_tien, 0, ',', '.'); ?>đ</div>
                    </div>
                    <?php 
                        endwhile; 
                    endif;
                    ?>
                </div>

                <div class="total-box">
                    <div class="total-row">
                        <span>Tạm tính:</span>
                        <span style="font-weight: 600;"><?php echo number_format($tong_tien_don_hang, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="total-row">
                        <span>Phí vận chuyển:</span>
                        <span style="color: #28a745; font-weight: 600;">Miễn phí</span>
                    </div>
                    <div class="total-row" style="margin-top: 15px;">
                        <span style="font-size: 16px; font-weight: bold; color: #333;">TỔNG CỘNG:</span>
                        <span class="total-final"><?php echo number_format($tong_tien_don_hang, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>

                <input type="hidden" name="selected_items" value="<?php echo $selected_items; ?>">
                
                <button type="submit" name="btn_place_order" class="btn-confirm">
                    XÁC NHẬN ĐẶT HÀNG
                </button>
                
                <div style="text-align: center; margin-top: 15px; color: #888; font-size: 12px;">
                    <i class="fa-solid fa-lock"></i> Mọi thông tin đều được bảo mật 100%
                </div>
            </div>
            
        </div>
    </form>

    <script>
        function toggleBankInfo(phuong_thuc) {
            var bankInfo = document.getElementById('bank-info');
            if (phuong_thuc === 'Chuyển khoản ngân hàng') {
                bankInfo.style.display = 'block';
                bankInfo.style.animation = 'fadeIn 0.3s ease-in-out';
            } else {
                bankInfo.style.display = 'none';
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
<?php
include 'ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>