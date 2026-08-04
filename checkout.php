<?php
session_start();
include 'admin/connect.php';

// 1. Chặn người chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// =========================================================================
// XỬ LÝ LƯU ĐƠN HÀNG VÀO DATABASE & GỬI EMAIL XÁC NHẬN BẰNG AZURE
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
            $thong_bao_loi .= "Sản phẩm [" . $check_kho['ten_san_pham'] . "] chỉ còn " . $check_kho['ton_kho'] . " chiếc.\\n";
        }
    }
    
    if ($het_hang) {
        $_SESSION['toast_msg'] = "Rất tiếc! Một số sản phẩm trong đơn đã hết hàng hoặc không đủ số lượng. Vui lòng kiểm tra lại.";
        $_SESSION['toast_type'] = "error";
        header("Location: cart.php");
        exit();
    }
    // ---------------------------------------------------------

    // Tự động lưu địa chỉ & SĐT mới vào Profile của khách
    $conn->query("UPDATE nguoi_dung SET ho_ten = '$ho_ten', so_dien_thoai = '$sdt', dia_chi = '$dia_chi' WHERE id = $user_id");
    $_SESSION['ho_ten'] = $ho_ten; 
    
    // Tính lại tổng tiền
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
        $sql_don_hang = "INSERT INTO don_hang (id_nguoi_dung, tong_tien, trang_thai, dia_chi_giao_hang, sdt_nguoi_nhan) 
                         VALUES ($user_id, $tong_tien, 'Chờ xác nhận', '$dia_chi', '$sdt')";
        
        if ($conn->query($sql_don_hang) === TRUE) {
            $id_don_hang = $conn->insert_id; 
            
            foreach ($arr_ids as $id_sp) {
                $id_sp = intval($id_sp);
                $qty = isset($_SESSION['cart'][$id_sp]) ? $_SESSION['cart'][$id_sp] : 1;
                $sp_info = $conn->query("SELECT gia_ban FROM san_pham WHERE id = $id_sp")->fetch_assoc();
                $gia = $sp_info['gia_ban'];
                
                // Lưu vào chi tiết đơn hàng
                $conn->query("INSERT INTO chi_tiet_don_hang (id_don_hang, id_san_pham, so_luong, don_gia) 
                              VALUES ($id_don_hang, $id_sp, $qty, $gia)");
                
                // BƯỚC 2: TRỪ SỐ LƯỢNG TRONG KHO
                $conn->query("UPDATE san_pham SET ton_kho = ton_kho - $qty WHERE id = $id_sp");

                // Mua xong xóa khỏi giỏ
                if (isset($_SESSION['cart'][$id_sp])) {
                    unset($_SESSION['cart'][$id_sp]); 
                }
            }
// =========================================================================
// ===== GỬI EMAIL XÁC NHẬN BẢO HÀNH (KÈM MÃ QR) - AZURE COMMUNICATION SERVICES =====
try {
    $user_query  = $conn->query("SELECT email FROM nguoi_dung WHERE id = $user_id")->fetch_assoc();
    $email_khach = trim($user_query['email'] ?? '');

    if (!empty($email_khach)) {
        include __DIR__ . '/email_logo.php';
        include __DIR__ . '/email_template.php';

        // 1. Tạo Link mã QR chứa thông tin Mã đơn hàng
        $qr_content = "MADON:" . $id_don_hang;
        $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($qr_content);

        // 2. Lấy nội dung Email HTML tiêu chuẩn từ template
        $base_html = getEmailHtml($ho_ten, $id_don_hang, $logo_base64);

        // 3. Khối HTML hiển thị Mã QR Code Bảo Hành trong Email
        $qr_html_block = '
        <div style="text-align: center; margin: 25px 0; padding: 15px; background: #fef9f1; border: 1px dashed #b58b5a; border-radius: 8px;">
            <h4 style="color: #b58b5a; margin: 0 0 10px 0; font-family: Arial, sans-serif; font-size: 16px;">
                MÃ QR XÁC NHẬN BẢO HÀNH
            </h4>
            <img src="' . $qr_api_url . '" alt="QR Bảo Hành" style="width: 150px; height: 150px; border: 1px solid #ddd; padding: 5px; background: #fff; border-radius: 5px;" />
            <p style="font-size: 12px; color: #666; margin: 10px 0 0 0; font-family: Arial, sans-serif;">
                Quý khách vui lòng lưu/chụp ảnh mã QR này để tải lên hệ thống khi gửi Yêu cầu Bảo hành.
            </p>
        </div>
        ';

        // 4. Ghép Mã QR vào cuối Email
        $final_email_html = $base_html . $qr_html_block;

        $acs_endpoint   = 'https://guithongbao-webdongho.unitedstates.communication.azure.com';
        $acs_accesskey  = '6uLkLsQyXlFn2Usw4QFAVV139yuIkrQBrfZp0xmtTA8a9m9tmncKJQQJ99CGACULyCpWm1mkAAAAAZCSeobd';
        $sender_address = 'DoNotReply@a11e9046-d6eb-4c1f-8644-4a7b05c6b749.azurecomm.net';

        $email_data = json_encode([
            "senderAddress" => $sender_address,
            "recipients"    => ["to" => [["address" => $email_khach, "displayName" => $ho_ten]]],
            "content"       => [
                "subject" => "[Timeless Watch] Xac nhan don hang #" . $id_don_hang,
                "html"    => $final_email_html
            ]
        ], JSON_UNESCAPED_UNICODE);

        $url_path       = '/emails:send?api-version=2023-03-31';
        $host           = parse_url($acs_endpoint, PHP_URL_HOST);
        $date           = gmdate('D, d M Y H:i:s \G\M\T');
        $content_hash   = base64_encode(hash('sha256', $email_data, true));
        $str_to_sign    = "POST\n" . $url_path . "\n" . $date . ";" . $host . ";" . $content_hash;
        $signature      = base64_encode(hash_hmac('sha256', $str_to_sign, base64_decode($acs_accesskey), true));

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
// ===== KẾT THÚC GỬI EMAIL =====
            // =========================================================================

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
    </style>
</head>
<body>

    <div id="smart-header">
        <header class="top-header" style="background: #fff; padding: 15px 50px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <div class="logo">
                <a href="index.php" class="logo-link" style="text-decoration: none; color: #000; display: flex; align-items: center; gap: 10px;">
                    <img src="image/logo.png" alt="Timeless Icon" style="height: 40px;">
                    <h1 style="margin: 0; font-size: 24px;">TIMELESS</h1>
                </a>
            </div>
            <div class="user-box">
                <a href="profile.php" style="text-decoration: none;"> 
                    <button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a; background: transparent; padding: 8px 15px; border-radius: 20px; cursor: pointer;">
                        <?php echo isset($_SESSION['ho_ten']) ? explode(' ', trim($_SESSION['ho_ten']))[count(explode(' ', trim($_SESSION['ho_ten'])))-1] : 'User'; ?> 
                        <i class="fa-solid fa-circle-user"></i>
                    </button>
                </a>
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
                            <img src="image/qr_code.png" alt="Mã QR" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png'">
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
</body>
</html>