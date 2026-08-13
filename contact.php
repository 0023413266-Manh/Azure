<?php
$path_prefix = ''; 
include $path_prefix . 'header.php';

// -------------------------------------------------------------
// HÀM KIỂM DUYỆT NỘI DUNG TỰ ĐỘNG BẰNG AZURE AI CONTENT SAFETY
// -------------------------------------------------------------
require_once __DIR__ . '/env_loader.php';

function checkContentSafetyAzure($text) {
    // Lấy Endpoint và Key từ file .env
    $azure_endpoint = $_ENV['CONTENT_SAFETY_ENDPOINT'] ?? ''; 
    $azure_key      = $_ENV['CONTENT_SAFETY_KEY'] ?? '';
    $url = rtrim($azure_endpoint, '/') . "/contentsafety/text:analyze?api-version=2023-10-01";

    $data = array(
        "text" => $text,
        "categories" => array("Hate", "SelfHarm", "Sexual", "Violence")
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5s
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Ocp-Apim-Subscription-Key: ' . $azure_key
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (isset($result['categoriesAnalysis'])) {
            foreach ($result['categoriesAnalysis'] as $category) {
                // Severity > 0 là phát hiện vi phạm
                if ($category['severity'] > 0) { 
                    return false; // NỘI DUNG VI PHẠM
                }
            }
        }
    }

    return true; // AN TOÀN
}

// XỬ LÝ KHI KHÁCH BẤM GỬI LỜI NHẮN
if (isset($_POST['gui_lien_he'])) {
    $ho_ten = $conn->real_escape_string($_POST['ho_ten']);
    $so_dien_thoai = $conn->real_escape_string($_POST['so_dien_thoai']);
    $email = $conn->real_escape_string($_POST['email']);
    $noi_dung = $conn->real_escape_string($_POST['noi_dung']);

    // 🛡️ BẢO VỆ AI: BẮT ĐẦU KIỂM DUYỆT NỘI DUNG LIÊN HỆ
    if (!empty($noi_dung)) {
        $is_safe = checkContentSafetyAzure($noi_dung);
        if (!$is_safe) {
            $_SESSION['toast_msg'] = "Nội dung lời nhắn chứa từ ngữ xúc phạm hoặc vi phạm tiêu chuẩn cộng đồng!";
            $_SESSION['toast_type'] = "error";
            header("Location: contact.php");
            exit();
        }
    }

    // Nếu an toàn -> Lưu CSDL
    $sql_insert = "INSERT INTO lien_he (ho_ten, so_dien_thoai, email, noi_dung) 
                   VALUES ('$ho_ten', '$so_dien_thoai', '$email', '$noi_dung')";
    
    if ($conn->query($sql_insert) === TRUE) {
        $_SESSION['toast_msg'] = "Cảm ơn bạn! Lời nhắn đã được gửi tới TIMELESS.";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Lỗi: Không thể gửi tin nhắn lúc này.";
        $_SESSION['toast_type'] = "error";
    }
    header("Location: contact.php");
    exit();
}

?>
    
    <div class="contact-page-container">
        <h2 class="contact-title">Liên hệ với chúng tôi</h2>
        <p class="contact-subtitle">TIMELESS luôn sẵn sàng lắng nghe và hỗ trợ quý khách hàng 24/7.</p>
        <div class="contact-wrapper">
            <div class="contact-info-col">
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="info-text">
                        <h4>Showroom Chính</h4>
                        <p>03-05 Pasteur, Phường Nguyễn Thái Bình, Quận 1, TP. Hồ Chí Minh</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div class="info-text">
                        <h4>Hotline Hỗ Trợ</h4>
                        <p>082 554 9816 (Zalo / Viber / Call)</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="info-text">
                        <h4>Email Phản Hồi</h4>
                        <p>htha4067@gmail.com</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fa-regular fa-clock"></i></div>
                    <div class="info-text">
                        <h4>Giờ Mở Cửa</h4>
                        <p>Thứ 2 - Chủ Nhật: 08:30 - 21:00</p>
                    </div>
                </div>
                
                <div class="map-container" style="margin-top: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                   <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1959.7779099215275!2d106.69929395655167!3d10.768675297333859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752fdecc18c3d1%3A0x30affd1d8e6f5d1f!2sEmpire%20Luxury!5e0!3m2!1svi!2s!4v1786550739483!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>

            </div>
            <div class="contact-form-col">
                <h3>Gửi lời nhắn</h3>
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label>Họ và tên của bạn</label>
                        <input type="text" name="ho_ten" placeholder="Nhập họ tên đầy đủ..." required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="so_dien_thoai" placeholder="Nhập số điện thoại liên hệ..." required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="Nhập địa chỉ email..." required>
                    </div>
                    <div class="form-group">
                        <label>Nội dung cần hỗ trợ</label>
                        <textarea name="noi_dung" placeholder="Bạn đang quan tâm sản phẩm nào hoặc cần hỗ trợ vấn đề gì..." required></textarea>
                    </div>
                    <button type="submit" name="gui_lien_he" class="btn-submit-contact">Gửi yêu cầu ngay</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-left">
            <div class="footer-logo">
                <img src="image/logo.png" alt="Timeless">
            </div>
            <h3 class="footer-title">TIMELESS</h3>
            <div class="footer-line"></div>
            <p>03-05 Pasteur, P. Nguyễn Thái Bình, Quận 1, TPHCM</p>
            <p><i class="fa fa-phone"></i> 0825549816</p>
            <p class="footer-desc">
                TIMELESS CHUYÊN CUNG CẤP – PHÂN PHỐI ĐỒNG HỒ CHÍNH HÃNG NHẬP KHẨU TỪ CHÂU ÂU
            </p>
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
            <h4>CHÍNH SÁCH KHÁCH HÀNG</h4> <ul>
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

    <?php include 'thongbao.php'; ?>

<?php
include 'ai-chatbot.php';
// Dòng này BẮT BUỘC nằm ở cuối cùng của file
include $path_prefix . 'footer.php'; 
?>