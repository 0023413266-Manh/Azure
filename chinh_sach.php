<?php
// 1. Khai báo biến đường dẫn & CSS riêng (nếu có)
$path_prefix = ''; // File nằm ở thư mục gốc

// 2. Nhúng Header chung (Đã bao gồm session_start(), connect DB và Dịch thuật Azure)
include $path_prefix . 'header.php';

// 1. Nhận biến 'type' từ URL
$type = isset($_GET['type']) ? $_GET['type'] : 'trahang';

// 2. MẢNG NỘI DUNG VỚI VĂN PHONG NHÀ VĂN (Đầy đủ, Trang trọng và Chi tiết)
$policies = [
    'trahang' => [
        'title' => 'Chính sách Đổi trả và Hoàn tiền: Cam kết của sự Tôn trọng',
        'content' => '
            <h3>1. Lời ngỏ về sự Thấu hiểu</h3>
            <p>Tại Timeless, chúng tôi không chỉ bán những cỗ máy thời gian, chúng tôi trao gửi những tuyệt tác nghệ thuật. Chúng tôi hiểu rằng, mỗi chiếc đồng hồ là một câu chuyện, và chúng tôi muốn câu chuyện của bạn với Timeless phải là một kỷ niệm đẹp đẽ và trọn vẹn nhất. Vì vậy, chính sách đổi trả của chúng tôi được xây dựng trên nền tảng của sự thấu hiểu và tôn trọng tối đa đối với quyết định của quý khách.</p>
            <h3>2. Thời gian và Quyền lợi</h3>
            <p>Quý khách có quyền yêu cầu đổi trả sản phẩm trong vòng <b>07 ngày</b> kể từ ngày nhận hàng thành công (căn cứ theo biên bản giao nhận hoặc dấu bưu điện). Đây là khoảng thời gian đủ để quý khách thấu cảm và chắc chắn về sự lựa chọn của mình.</p>
            <h3>3. Điều kiện của sự Nguyên vẹn</h3>
            <p>• Sản phẩm phải còn nguyên vẹn 100%, chưa qua sử dụng, không có dấu hiệu trầy xước, va đập hoặc tự ý tháo mở bộ máy.</p>
            <p>• Còn đầy đủ bao bì, hộp đựng cao cấp, sổ hướng dẫn, thẻ bảo hành quốc tế và các quà tặng kèm theo (nếu có).</p>
            <p>• Có đầy đủ hóa đơn mua hàng hoặc thông tin đơn hàng trên hệ thống Timeless.</p>
            <h3>4. Trường hợp không được đổi trả</h3>
            <p>• Sản phẩm bị hư hỏng do lỗi sử dụng sai quy định (tiếp xúc hóa chất, va đập mạnh, chỉnh giờ khi đang ở dưới nước...).</p>
            <p>• Sản phẩm thuộc chương trình xả kho hoặc giảm giá trên 50%.</p>
            <h3>5. Quy trình hoàn tiền và Chi phí</h3>
            <p>Sau khi tiếp nhận và kiểm tra tình trạng máy, Timeless sẽ hoàn tiền cho quý khách qua chuyển khoản ngân hàng trong vòng 3-5 ngày làm việc. Phí vận chuyển chiều về sẽ do khách hàng thanh toán trừ trường hợp lỗi từ nhà sản xuất.</p>
        '
    ],
    'baohanh' => [
        'title' => 'Chế độ Bảo hành Đặc quyền: Sự Gửi gắm Niềm tin',
        'content' => '
            <h3>1. Lời ngỏ về sự Bền bỉ</h3>
            <p>Mỗi chiếc đồng hồ tại Timeless là một minh chứng cho sự bền bỉ của thời gian. Chúng tôi không chỉ bảo hành, chúng tôi gửi gắm vào đó một lời cam kết về chất lượng và sự an tâm tuyệt đối. Niềm tin của bạn là tài sản quý giá nhất của chúng tôi, và chúng tôi sẽ luôn đồng hành cùng bạn trên mọi hành trình.</p>
            <h3>2. Bảo hành Quốc tế và Timeless</h3>
            <p>• Tất cả sản phẩm tại Timeless đều được hưởng chế độ bảo hành toàn cầu từ nhà sản xuất (Rolex, Hublot, Omega...) với thời gian từ <b>02 đến 05 năm</b> tùy dòng máy.</p>
            <p>• Chúng tôi cam kết bảo hành <b>TRỌN ĐỜI</b> đối với các lỗi kỹ thuật về máy (lau dầu, cân chỉnh độ chính xác).</p>
            <p>• Hỗ trợ thay pin miễn phí trọn đời đối với các dòng đồng hồ Quartz.</p>
            <p>• Miễn phí đánh bóng vỏ và dây kim loại lần đầu tiên trong năm đầu sử dụng.</p>
            <h3>3. Các hạng mục không nằm trong bảo hành</h3>
            <p>• Hao mòn tự nhiên theo thời gian của dây da, mặt kính (trừ lỗi nứt tự nhiên).</p>
            <p>• Các lỗi do thiên tai, hỏa hoạn hoặc khách hàng tự ý sửa chữa tại các cơ sở không thuộc hệ thống ủy quyền của hãng.</p>
        '
    ],
    'vanchuyen' => [
        'title' => 'Chính sách Vận chuyển và Kiểm hàng: Hành trình của sự An toàn',
        'content' => '
            <h3>1. Lời ngỏ về sự An toàn</h3>
            <p>Tại Timeless, chúng tôi không chỉ giao hàng, chúng tôi đang chuyển đến bạn một tác phẩm nghệ thuật, một người bạn đồng hành. Vì vậy, mỗi hành trình giao nhận đều được chúng tôi chăm chút, đảm bảo sự an toàn và nguyên vẹn tuyệt đối. Chúng tôi không chỉ vận chuyển, chúng tôi đang xây dựng một cầu nối của sự tin tưởng.</p>
            <h3>2. Phí vận chuyển và Thời gian</h3>
            <p>• Timeless thực hiện chính sách <b>Miễn phí vận chuyển (Free Ship) 100%</b> cho tất cả đơn hàng trên toàn lãnh thổ Việt Nam, không giới hạn giá trị đơn hàng.</p>
            <p>• Khu vực nội thành (TP.HCM, Hà Nội): Giao nhanh trong vòng 2 - 4 giờ hoặc theo lịch hẹn cụ thể.</p>
            <p>• Khu vực tỉnh/thành khác: Giao hàng hỏa tốc trong 1-3 ngày làm việc thông qua đối tác vận chuyển ủy quyền.</p>
            <h3>3. Quyền lợi kiểm hàng (Đồng kiểm)</h3>
            <p>Nhằm đảm bảo an tâm tuyệt đối, khách hàng có quyền <b>mở hộp kiểm tra sản phẩm trước khi thanh toán</b>. Quý khách vui lòng quay phim quá trình mở hộp để làm căn cứ nếu có khiếu nại phát sinh.</p>
            <h3>4. Bảo hiểm hàng hóa</h3>
            <p>100% đơn hàng được Timeless mua bảo hiểm giá trị cao. Mọi mất mát hoặc hư hại trong quá trình vận chuyển sẽ do chúng tôi hoàn toàn chịu trách nhiệm.</p>
        '
    ],
    'dieukhoan' => [
        'title' => 'Điều khoản sử dụng và Dịch vụ: Bản giao ước của sự Tôn trọng',
        'content' => '
            <h3>1. Lời ngỏ về sự Tôn trọng</h3>
            <p>Bản giao ước này được xây dựng trên nền tảng của sự tôn trọng tối đa đối với quý khách hàng. Chúng tôi không chỉ quy định, chúng tôi đang cùng bạn xây dựng một không gian mua sắm văn minh, an toàn và đầy cảm hứng. Niềm tin của bạn là tài sản quý giá nhất của chúng tôi, và chúng tôi sẽ luôn tôn trọng nó.</p>
            <h3>2. Bản quyền nội dung và Trách nhiệm</h3>
            <p>• Toàn bộ hình ảnh, video và mô tả sản phẩm trên website thuộc sở hữu của Timeless. Mọi hành vi sao chép trái phép sẽ bị xử lý theo quy định pháp luật.</p>
            <p>• Quý khách cam kết cung cấp thông tin liên hệ chính xác để phục vụ quá trình giao hàng và bảo hành. Chúng tôi có quyền từ chối phục vụ nếu phát hiện hành vi đặt hàng giả mạo hoặc phá hoại hệ thống.</p>
            <h3>3. Bảo mật thông tin</h3>
            <p>Thông tin cá nhân của bạn chỉ được sử dụng cho mục đích phục vụ đơn hàng và gửi ưu đãi thành viên, cam kết không chia sẻ cho bên thứ ba.</p>
            <h3>4. Disclaimer</h3>
            <p>Timeless không chịu trách nhiệm về các lỗi website, gián đoạn phục vụ do nguyên nhân khách quan. Mọi tranh chấp phát sinh sẽ được giải quyết trên tinh thần thiện chí và tôn trọng.</p>
        '
    ],
    'thanhtoan' => [
        'title' => 'Phương thức Thanh toán Bảo mật: Sự Gửi gắm Niềm tin',
        'content' => '
            <h3>1. Lời ngỏ về sự Bảo mật</h3>
            <p>Tại Timeless, chúng tôi không chỉ nhận thanh toán, chúng tôi đang nhận về mình một sự gửi gắm niềm tin. Chúng tôi hiểu rằng, mỗi giao dịch là một cam kết về sự an toàn và bảo mật tuyệt đối. Chúng tôi không chỉ cung cấp dịch vụ, chúng tôi đang xây dựng một cầu nối của sự tin tưởng.</p>
            <h3>2. Phương thức Thanh toán</h3>
            <p>• <b>Thanh toán khi nhận hàng (COD)</b>: Khách hàng thanh toán trực tiếp bằng tiền mặt cho nhân viên giao hàng sau khi đã kiểm tra sản phẩm ưng ý.</p>
            <p>• <b>Chuyển khoản ngân hàng</b>: Áp dụng cho khách hàng muốn thanh toán trước để nhận thêm ưu đãi hoặc tặng quà cho người thân. Hệ thống sẽ tự động xác nhận đơn hàng sau khi nhận được tiền.</p>
            <p>• <b>Thanh toán qua Thẻ (Visa/MasterCard)</b>: Hệ thống thanh toán trực tuyến của chúng tôi áp dụng tiêu chuẩn bảo mật <b>SSL 256-bit</b>, đảm bảo thông tin thẻ của quý khách được mã hóa an toàn tuyệt đối.</p>
            <p>• <b>Trả góp 0% lãi suất</b>: Hỗ trợ trả góp qua thẻ tín dụng của hơn 20 ngân hàng liên kết, thủ tục đơn giản, duyệt hồ sơ nhanh chóng.</p>
        '
    ]
];

if (!array_key_exists($type, $policies)) { $type = 'trahang'; }
$current_policy = $policies[$type];
?>
    <style>
        body { background-color: #f8f9fa; }
        
.policy-container { 
            max-width: 1050px; /* Thu hẹp số này lại để ép hai bên lề xích vào giữa */
            margin: 110px auto 80px auto; 
            display: flex; 
            gap: 60px; /* Tăng khoảng cách giữa menu và phần chữ cho thoáng */
            align-items: flex-start; 
            padding: 0 20px;
        }
        
        /* SIDEBAR KHÔNG BỨC TƯỜNG TRẮNG */
        .policy-sidebar { 
            flex: 0 0 300px; 
            background: transparent !important; 
            box-shadow: none !important;
            border: none !important;
            position: sticky; 
            top: 100px;
        }
        .policy-sidebar h3 { 
            color: #b58b5a; 
            margin: 0; 
            padding: 10px 15px 25px 0; 
            font-size: 18px; 
            letter-spacing: 1.5px;
            text-transform: uppercase; 
            font-family: 'Playfair Display', serif;
            border-bottom: 2px solid #b58b5a;
            background: transparent;
        }
        .policy-sidebar ul { list-style: none; padding: 0; margin: 0; margin-top: 15px;}
        .policy-sidebar a { 
            display: flex; 
            align-items: center; 
            padding: 15px 0; 
            text-decoration: none; 
            color: #666; 
            font-weight: 500; 
            transition: 0.3s; 
            border-bottom: 1px dashed #ddd;
            font-size: 15px;
        }
        .policy-sidebar a:hover { color: #b58b5a; padding-left: 10px; }
        .policy-sidebar a.active { 
            color: #b58b5a; 
            font-weight: bold; 
        }
        .policy-sidebar a.active::before {
            content: "» ";
            margin-right: 8px;
        }
        
        /* NỘI DUNG CHÍNH SÁCH */
        .policy-content { 
            flex: 1; 
            background: #fff; 
            padding: 50px; 
            border-radius: 12px; 
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
        }
        .policy-content h1 { 
            font-family: 'Playfair Display', serif; 
            color: #1a1a1a; 
            font-size: 32px; 
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        .policy-content h1::after {
            content: ''; position: absolute; left: 0; bottom: 0; width: 60px; height: 3px; background: #b58b5a;
        }
        .policy-content h3 { color: #b58b5a; margin-top: 30px; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 10px;}
        .policy-content h3::before { content: '\f058'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 16px;}
        .policy-content p { color: #555; line-height: 1.8; margin-bottom: 15px; font-size: 15px; text-align: justify;}
    </style>


<div class="policy-container">
    <div class="policy-sidebar">
        <h3>Trung tâm hỗ trợ</h3>
        <ul>
            <li><a href="?type=trahang" class="<?php echo ($type == 'trahang') ? 'active' : ''; ?>">Chính sách đổi trả hàng</a></li>
            <li><a href="?type=baohanh" class="<?php echo ($type == 'baohanh') ? 'active' : ''; ?>">Chính sách bảo hành sản phẩm</a></li>
            <li><a href="?type=vanchuyen" class="<?php echo ($type == 'vanchuyen') ? 'active' : ''; ?>">Chính sách vận chuyển</a></li>
            <li><a href="?type=dieukhoan" class="<?php echo ($type == 'dieukhoan') ? 'active' : ''; ?>">Điều khoản sử dụng</a></li>
            <li><a href="?type=thanhtoan" class="<?php echo ($type == 'thanhtoan') ? 'active' : ''; ?>">Chính sách thanh toán</a></li>
        </ul>
    </div>

    <div class="policy-content">
        <h1><?php echo $current_policy['title']; ?></h1>
        <div class="policy-text-body">
            <?php echo $current_policy['content']; ?>
        </div>
    </div>
</div>

<section class="testimonial-section">
    <h2 class="testimonial-title">NHẬN XÉT KHÁCH HÀNG</h2>
    <div class="testimonial-list">
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Tôi rất hài lòng với chiếc Rolex vừa mua."</p>
            </div>
            <div class="testimonial-user">
                <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Dịch vụ tuyệt vời! Đồng hồ Hublot đẹp hơn cả trong hình. Giao hàng nhanh và đóng gói rất cẩn thận."</p>
            </div>
            <div class="testimonial-user">
                <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
        <div class="testimonial-item">
            <div class="testimonial-bubble">
                <p>Cửa hàng tư vấn rất kỹ về cách bảo quản và chế độ bảo hành. Chắc chắn sẽ quay lại!</p>
            </div>
            <div class="testimonial-user">
                <img src="image/nen.jpg" onerror="this.src='image/logo.png'">
                <div>
                    <strong>Pham Phuoc Manh</strong>
                    <span>Sinh Viên</span>
                </div>
            </div>
        </div>
    </div>
    <div class="testimonial-info">
        <div>📞<span>0825549816</span></div>
        <div>📍<span>03-05 Pasteur, P. Nguyễn Thái Bình, Q.1, TP. Hồ Chí Minh</span></div>
        <div>✉️<span>htha4067@gmail.com</span></div>
    </div>
</section>

<footer class="footer">
   <div class="footer-left" style="background: transparent !important; box-shadow: none !important; border: none !important; padding-left: 80px !important;">
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
<?php
include 'ai-chatbot.php';
// 3. FOOTER BẮT BUỘC PHẢI NẰM Ở DÒNG CUỐI CÙNG CỦA FILE
include 'footer.php'; 
?>