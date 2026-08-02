<?php
session_start();
include 'admin/connect.php';
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Về Rolex - Phát triển bền vững - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="explore.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div id="smart-header">
        
        <header class="top-header">
            <div class="logo">
                <a href="index.php" class="logo-link">
                    <h1>TIMELESS</h1>
                    <img src="image/logo.png" alt="Timeless Icon">
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
                    <a href="profile.php" style="text-decoration: none;"> 
                        <button class="btn-user" style="color: #b58b5a; font-weight: bold; border-color: #b58b5a;">
                            <?php echo $ten_ngan; ?> <i class="fa-solid fa-circle-user"></i>
                        </button>
                    </a>
                <?php } else { ?>
                    <a href="login.php" style="text-decoration: none;"> 
                        <button class="btn-user">User <i class="fa-solid fa-circle-user"></i></button>
                    </a>
                <?php } ?>
            </div>
        </header>

        <nav class="main-nav">
            
            <a href="explore.php" class="header-back-arrow" title="Quay lại Khám phá">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <ul>
                <li><a href="index.php">TRANG CHỦ</a></li>
                <li class="dropdown">
                    <a href="#">THƯƠNG HIỆU <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="all_rolex.php">ROLEX</a></li>
                        <li><a href="all_omega.php">OMEGA</a></li>
                        <li><a href="all_casio.php">CASIO</a></li>
                        <li><a href="all_seiko.php">SEIKO</a></li>
                        <li><a href="all_hublot.php">HUBLOT</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">SẢN PHẨM <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="Dongho_nam.php">DÀNH CHO NAM</a></li>
                        <li><a href="Dongho_nu.php">DÀNH CHO NỮ</a></li>
                    </ul>
                </li>
                <li><a href="explore.php" style="color: #b58b5a; font-weight: bold;">KHÁM PHÁ</a></li>
                <li><a href="contact.php">LIÊN HỆ</a></li>
            </ul>
        </nav>
    </div> 
    <div class="article-page-container">
        
        <div class="article-header">
            <span class="article-category">Câu chuyện thương hiệu</span>
            <h1 class="article-title">Về Rolex - Tầm nhìn và Cam kết phát triển bền vững</h1>
            <div class="article-meta">
                <span><i class="fa-regular fa-calendar"></i> 25/02/2026</span>
                <span><i class="fa-solid fa-user-pen"></i> Bởi: Timeless Expert</span>
            </div>
        </div>

        <div class="article-content">
            <h3>Tính bền vững: Trọng tâm của mọi hoạt động</h3>
            <p>Đặt tiêu chuẩn về chất lượng, tinh thần đổi mới và các giá trị xuất sắc là trọng tâm trong các cam kết phát triển bền vững của chúng tôi.</p>
            <ul>
                <li style="margin-bottom: 10px;">Bằng cách kết hợp sứ mệnh này trong các quyết định và hành động của công ty chúng tôi.</li>
                <li style="margin-bottom: 10px;">Bằng cách đảm bảo trách nhiệm xã hội và môi trường được tích hợp vào các hoạt động của chúng tôi trên toàn bộ chuỗi giá trị của chúng tôi.</li>
                <li style="margin-bottom: 10px;">Bằng cách liên tục đổi mới để giảm tác động từ các hoạt động của chúng tôi.</li>
                <li style="margin-bottom: 10px;">Bằng cách tiếp tục thiết kế và sản xuất những chiếc đồng hồ có chất lượng vượt trội và bền lâu.</li>
            </ul>

            <div class="parallax-box" style="background-image: url('image/baiviet/rolex1.avif'); margin-top: 50px; margin-bottom: 50px;">
                <div class="parallax-content">
                    <h3>Tính bền vững</h3>
                    <p>Cam kết cho tương lai</p>
                </div>
            </div>

            <h3>Chất lượng vượt thời gian - Nền tảng của sự bền vững</h3>
            <p>Tại Rolex, chúng tôi tin rằng một sản phẩm bền vững nhất là một sản phẩm không bao giờ bị vứt bỏ. Bằng việc sử dụng các vật liệu cao cấp nhất như thép Oystersteel đặc biệt chống ăn mòn và vàng 18 ct được tinh luyện tại xưởng đúc riêng, mỗi chiếc đồng hồ Rolex được chế tác để trường tồn qua nhiều thế hệ.</p>
            
            <h3>Mục tiêu giảm phát thải khí nhà kính</h3>
            <p>Thông qua chương trình Science Based Targets initiative, chúng tôi đã cam kết giảm lượng phát thải khí nhà kính tuyệt đối trước năm 2030. Mục tiêu của chúng tôi phù hợp với các yêu cầu khử cacbon cần thiết để hạn chế tình trạng nóng lên toàn cầu vào năm 2050, theo Thỏa thuận Paris.</p>
            <p>Chương trình Science Based Targets initiative (SBTi) – triển khai vào năm 2015 và được công nhận trên toàn cầu – khuyến khích các công ty đặt ra mục tiêu giảm phát thải khí nhà kính (GHG), đảm bảo mức tăng nhiệt độ toàn cầu không vượt quá 2°C hay thậm chí là 1,5°C so với nhiệt độ thời tiền công nghiệp.</p>
            <p>Chúng tôi đã cùng với TUDOR đệ trình các mục tiêu giảm khí nhà kính của mình lên SBTi. Chúng tôi đã đo lượng phát thải khí nhà kính này hàng năm kể từ 2019. Dựa trên kết quả theo dõi lượng khí thải carbon năm 2021, chúng tôi đã đặt ra mục tiêu giảm thiểu và ưu tiên các hành động có tác động lớn nhất.</p>
            <p>Dựa trên mức phát thải năm 2021, mục tiêu giảm phát thải tuyệt đối vào năm 2030 của chúng tôi như sau:<br>
            <strong style="color: #0d1b3e;">Phạm vi 1 và 2: - 42% và Phạm vi 3: - 25%</strong></p>
            <p>SBTi đã phê duyệt các mục tiêu này vào năm 2024. Những thông tin sau sẽ được công bố trên bảng điều khiển SBTi.</p>

            <img src="image/baiviet/rolex2.avif" alt="Mục tiêu giảm phát thải" class="rolex-normal-image">

            <h3 style="margin-top: 40px;">Dấu chân carbon của chúng tôi</h3>
            <p>Lượng khí thải carbon của chúng tôi được tính toán theo Giao thức khí nhà kính được quốc tế công nhận, tiêu chuẩn hóa việc đo lượng phát thải khí nhà kính và phân loại chúng thành ba ‘phạm vi’:</p>
            <ul>
                <li style="margin-bottom: 10px;"><strong>Phạm vi 1</strong> liên quan đến phát thải khí nhà kính trực tiếp từ các nguồn trong công ty và do đó nằm dưới sự kiểm soát trực tiếp của công ty.</li>
                <li style="margin-bottom: 10px;"><strong>Phạm vi 2</strong> bao gồm phát thải khí nhà kính gián tiếp liên quan đến việc mua điện mà công ty tiêu thụ.</li>
                <li style="margin-bottom: 10px;"><strong>Phạm vi 3</strong> bao gồm phát thải khí nhà kính gián tiếp liên quan đến các hoạt động ở thượng nguồn và hạ nguồn của công ty không thuộc quyền kiểm soát trực tiếp của công ty. Lượng khí thải thuộc Phạm vi 3 chiếm phần lớn lượng khí thải carbon của chúng ta.</li>
            </ul>
            <p>3 phạm vi bao gồm trong dấu chân carbon của chúng tôi:</p>

            <img src="image/baiviet/rolex6.avif" alt="Dấu chân carbon 3 phạm vi" class="rolex-normal-image">

            <p>Lượng khí thải carbon của Rolex – theo sau là các mục tiêu giảm khí nhà kính – được thực thi bởi Rolex SA, Xưởng sản xuất Montres Rolex SA, Roldeco SA, các công ty liên kết của Tập đoàn Rolex ở Thụy Sĩ và các chi nhánh dịch vụ và phân phối ở nước ngoài. Năm 2025, TUDOR sẽ báo cáo riêng về lượng khí thải carbon của mình cho năm 2024.</p>
            <p>Năm 2024, tổng lượng khí thải carbon của chúng ta là 839 kt CO2e, trong đó 98% thuộc về phạm vi 3. Kim loại quý (vàng, bạch kim, palladium và bạc) đóng góp đáng kể vào kết quả này, chiếm 71% tổng lượng khí thải carbon.</p>
        </div>
    </div> 

    <div class="rolex-standards-section">
        <div class="rolex-standards-content">
            <p class="bold-intro">Bên cạnh các quy định và khuôn khổ tham chiếu quốc gia và quốc tế do ILO và OECD thiết lập, chúng tôi tuân thủ các chứng nhận và quy định về môi trường sau đây:</p>
            <ul class="rolex-standards-list">
                <li>Hiệp hội thị trường vàng thỏi London (LBMA)</li>
                <li>Thị trường Bạch kim và Palladium London (LPPM)</li>
                <li>Quy trình đảm bảo khoáng sản có trách nhiệm (RMAP)</li>
                <li>Sáng kiến ​​khoáng sản có trách nhiệm (RMI)</li>
                <li>Mẫu báo cáo về khoáng sản xung đột (CMRT)/Quy trình đảm bảo khoáng sản có trách nhiệm (RMAP)</li>
                <li>Chuỗi giám sát của Hội đồng trang sức có trách nhiệm (RJC COC)</li>
                <li>Công ước về buôn bán quốc tế các loài động vật, thực vật hoang dã có nguy cơ tuyệt chủng (CITES)</li>
                <li>Quy trình Kimberley (KP)</li>
                <li>Đăng ký, đánh giá và cấp phép hóa chất (REACH)</li>
            </ul>
        </div>
    </div>

    <div class="rolex-gold-fullwidth">
        <div class="rolex-gold-content">
            <h3>Vàng</h3>
            <p>Với tư cách là nhà chế tác đồng hồ hàng đầu, Rolex tự trang bị cho mình xưởng đúc riêng để tạo ra các hợp kim vàng 18 ct chất lượng cao nhất.</p>
            <p>Bằng cách kiểm soát toàn bộ quy trình từ khâu tinh luyện đến đúc khuôn, chúng tôi đảm bảo nguồn vàng được khai thác một cách có trách nhiệm.</p>
            <p>Tính bền vững trong chế tác vàng không chỉ đảm bảo sự tuân thủ các tiêu chuẩn đạo đức nghiêm ngặt nhất, mà còn lưu giữ sự lấp lánh vĩnh cửu cho mỗi chiếc đồng hồ.</p>
        </div>
    </div>

    <div class="image-fade-wrapper">
        <img src="image/baiviet/rolex3.png" alt="Vàng Rolex 18ct" class="fullwidth-image">
    </div>

    <div class="rolex-diamond-fullwidth">
        <div class="rolex-diamond-content">
            <h3>Kim cương</h3>
            <p>100% kim cương chúng tôi sử dụng đều được chứng nhận theo Quy trình Kimberley, đảm bảo việc mua kim cương thô không liên quan đến bất kỳ khu vực xung đột nào.</p>
            <p>Để đảm bảo nguồn cung ứng có trách nhiệm, công ty chỉ lấy kim cương từ một số nhà cung cấp, tất cả đều là đối tác đáng tin cậy trong nhiều năm.</p>
            <p>Đối với mỗi nguồn cung cấp, chúng tôi yêu cầu các nhà cung cấp phải khai báo nguồn gốc của từng lô hàng và yêu cầu họ tiếp tục chú ý đến trách nhiệm xã hội và tiêu chí về tính bền vững để đánh giá rủi ro và hướng nguồn cung ứng đến các quốc gia có nền chính trị ổn định và chống tham nhũng.</p>
            <p>Yêu cầu này giúp rút ngắn chuỗi cung ứng và giảm tỷ lệ đá đến từ thị trường tự do. Chúng tôi có khả năng truy xuất nguồn gốc đầy đủ đối với kim cương, bao gồm cả quốc gia nơi kim cương được khai thác và cắt gọt. Mục đích của việc này là để chuyển từ bản đồ khai báo sang bản đồ được ghi lại.</p>
            <p>Cho đến lúc đó, tham vọng của chúng tôi là triển khai khả năng truy xuất nguồn gốc hoàn toàn kỹ thuật số, được xác thực bằng các cuộc kiểm toán bên ngoài và đánh giá các nhà máy cắt mỗi ba năm một lần. Chúng tôi cũng đảm bảo chất lượng cao nhất và nguồn gốc tự nhiên của kim cương thông qua phòng thí nghiệm đá quý của riêng mình.</p>
        </div>
    </div>

    <div class="image-fade-wrapper-diamond">
        <img src="image/baiviet/rolex4.png" alt="Kim cương Rolex" class="fullwidth-image">
    </div>
    
    <div class="rolex-normal-section">
        <div class="rolex-normal-content">
            <h3>Thép</h3>
            <p>Thép Oystersteel là hợp kim mà các nhà cung cấp độc quyền của Rolex đã sử dụng chất thải từ ngành công nghiệp tại châu Âu trong quy trình sản xuất của họ. Lượng chất thải này chiếm trung bình 60% khối lượng đúc và bao gồm chất thải hợp kim (từ hợp kim) và không hợp kim (từ kim loại nguyên chất). Phần trăm còn lại bao gồm hợp kim ferro, chứa tỷ lệ cao các nguyên tố tạo nên thép Oystersteel và được thêm vào theo tỷ lệ khác nhau vào sản phẩm đúc để thu được loại thép thành phẩm mong muốn (904L).</p>
            <p>Thông qua việc lập bản đồ chính xác, chúng tôi biết được nguồn gốc của từng loại khoáng chất được sử dụng trong hợp kim thép do nhà cung cấp cung cấp. Chúng tôi cũng đã bảo đảm nguồn gốc của các thành phần hợp kim được sử dụng để sản xuất thép Oystersteel, ngoại trừ các khu vực có nguy cơ cao.</p>
            
            <img src="image/baiviet/rolex5.avif" alt="Thép Oystersteel" class="rolex-normal-image">
            
            <p>Để giảm lượng khí thải carbon liên quan đến chuỗi cung ứng thép Oystersteel, chúng tôi đã triển khai một dự án tái chế thí điểm cho chất thải sản xuất của mình, hợp tác với nhà cung cấp chính. Do loại chất thải này giàu các thành phần hợp kim hơn chất thải thông thường nên việc tái chế nó thành vật đúc chính sẽ làm giảm đáng kể tác động của nó đến môi trường. Tổng cộng đã có tám lần đúc thử nghiệm được thực hiện kể từ tháng 12 năm 2022, giúp cải thiện tỷ lệ tái chế thêm 10 điểm và giảm 35% lượng phát thải khí nhà kính. Mục tiêu hiện nay là giảm 30% lượng khí thải vào năm 2025.</p>
        </div>
    </div>

    <div class="rolex-normal-section" style="padding-top: 20px;"> 
        <div class="rolex-normal-content">
            <h3>Vonfram, tantalum và thiếc</h3>
            <p>Chúng tôi tuân thủ các nguyên tắc trong Thẩm định Chuyên sâu OECD để đảm bảo việc tìm nguồn cung ứng vonfram, tantalum và thiếc sẽ diễn ra một cách có trách nhiệm.</p>
            <p>Các nhà cung cấp của chúng tôi, những người mà chúng tôi đã xây dựng mối quan hệ lâu dài, sử dụng các xưởng đúc được chứng nhận bởi Sáng kiến ​​Khoáng sản có trách nhiệm (RMI). Chứng nhận này đảm bảo rằng chuỗi giá trị đã được kiểm toán toàn bộ từ khi khai thác đến khi ra khỏi mỏ và nhân quyền được tôn trọng trong xuyên suốt chuỗi cung ứng, bao gồm cả quy trình tại xưởng đúc và nhà máy tinh chế.</p>
            <p>Những đối tác đáng tin cậy này gửi cho chúng tôi chứng nhận ‘Mẫu báo cáo khoáng sản xung đột’ (CMRT) cho các nhà cung cấp của chính họ.</p>
            <p>Họ cũng có nghĩa vụ thông báo cho chúng tôi ngay lập tức nếu họ sử dụng bất kỳ bên mới nào trong chuỗi cung ứng của họ để chúng tôi có thể đánh giá một cách có hệ thống về rủi ro mà các bên đó có thể gây ra.</p>
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
    </script>
</body>
</html>