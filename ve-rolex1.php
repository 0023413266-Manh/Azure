<?php
$path_prefix = ''; 
include $path_prefix . 'header.php';
?>

<!DOCTYPE html>

<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phía sau vương miện - Timeless</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="explore.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        /* KHUNG CHỨA ẢNH TRÀN VIỀN */
        .scroll-reveal-container {
            position: relative;
            width: 100vw;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            margin-bottom: 50px;
            margin-top: 20px;
            overflow: hidden; /* Đảm bảo lớp mờ không bị tràn ra ngoài */
        }

        /* ẢNH GỐC - CHIỀU DÀI AUTO ĐỂ KHÔNG BỊ CẮT XÉN */
        .reveal-img {
            width: 100%;
            height: auto; 
            display: block;
        }

        /* LỚP KÍNH ĐEN VÀ LÀM MỜ ẢNH (Bắt đầu là tàng hình: opacity = 0) */
        .reveal-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.75); /* Phủ màu đen */
            backdrop-filter: blur(8px); /* Lệnh làm mờ nhòe ảnh */
            -webkit-backdrop-filter: blur(8px);
            opacity: 0; 
            z-index: 2;
        }

        /* KHUNG CHỨA NỘI DUNG CHỮ (NẰM TRÊN ẢNH) */
        .reveal-content {
            position: absolute;
            bottom: 12%; /* Đặt chữ nằm ở khúc gần cuối ảnh */
            left: 50%;
            transform: translateX(-50%) translateY(30px);
            width: 100%;
            max-width: 1200px;
            padding: 0 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            opacity: 0; /* Ban đầu ẩn chữ đi */
            z-index: 3;
            color: #fff;
            box-sizing: border-box;
        }

        .reveal-content .reveal-left {
            flex: 1;
            min-width: 300px;
            font-family: 'Playfair Display', serif;
            font-size:40px;
            line-height: 1.4;
            font-weight: bold;
        }

        .reveal-content .reveal-right {
            flex: 1.2;
            min-width: 300px;
            font-size: 19px;
            line-height: 1.8;
            text-align: justify;
            color: #f0f0f0;
        }

        /* ================= CSS MỚI CHO CÂU TRÍCH DẪN FULL BỀ NGANG ================= */
        .fullwidth-quote-section {
            position: relative;
            width: 100vw;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            background-color: #fff; /* Nền trắng tinh */
            padding: 150px 20px; /* Khoảng trống trên dưới cực lớn tạo độ thoáng */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .quote-box {
            max-width: 800px; /* Căn gọn chữ vào giữa màn hình */
            width: 100%;
        }

        .quote-text {
            font-family: 'Playfair Display', serif;
            font-size: 44px; /* Chữ to sang trọng */
            line-height: 1.5;
            color: #222;
            margin-bottom: 30px;
        }

        .quote-author {
            font-size: 18px;
            color: #444;
            font-family: Arial, sans-serif;
        }
        /* ========================================================================= */

    </style>
</head>


    <div class="article-page-container">
        
        <div class="article-header">
            <span class="article-category">Câu chuyện thương hiệu</span>
            <h1 class="article-title">Phía sau vương miện</h1>
            <div class="article-meta">
                <span><i class="fa-regular fa-calendar"></i> 26/01/2026</span>
                <span><i class="fa-solid fa-user-pen"></i> Bởi: Timeless Expert</span>
            </div>
        </div>

        <div class="article-content">
            
            <div class="scroll-reveal-container" id="revealContainer">
                
                <img src="image/baiviet/rolex6.jpg" class="reveal-img" alt="Hans Wilsdorf">
                
                <div class="reveal-overlay" id="revealOverlay"></div>
                
                <div class="reveal-content" id="revealContent">
                    <div class="reveal-left">
                        Lịch sử của Rolex gắn liền với nhà sáng lập thương hiệu - Hans Wilsdorf.
                    </div>
                    <div class="reveal-right">
                        Hans Wilsdorf là một người có tầm nhìn xa trông rộng, đã tiên đoán được sự thay đổi và phát triển của xã hội trước một thế kỷ. Tin chắc rằng khả năng chống thấm nước sẽ biến đồng hồ đeo tay và trở thành thứ bắt buộc phải có đối với mọi người, ông đã cho ra mắt mẫu Oyster vào năm 1926. Nhận thức được tầm quan trọng của các kỷ lục và khám phá, ông đã thử nghiệm đồng hồ của mình trong những điều kiện khắc nghiệt cùng những vận động viên thể thao và nhà thám hiểm ở thế kỷ XX. Những cải tiến của Rolex đã để lại dấu ấn không thể phai mờ trong lịch sử chế tạo đồng hồ toàn cầu và là bằng chứng cho hành trình không ngừng kiếm tìm sự xuất sắc của nhà sáng lập.
                    </div>
                </div>
            </div>
            <div class="fullwidth-quote-section">
                <div class="quote-box">
                    <div class="quote-text">
                        “Chúng ta phải tìm cách tạo ra một chiếc đồng hồ đeo tay chống thấm nước.”
                    </div>
                    <div class="quote-author">
                        Hans Wilsdorf
                    </div>
                </div>
            </div>
            <div class="scroll-reveal-container">
                <img src="image/baiviet/rolex7.avif" class="reveal-img" alt="Sự tiến hóa của Rolex" onerror="this.src='image/rolex7.avif'">
                
                <div class="reveal-overlay"></div>
                
                <div class="reveal-content">
                    <div class="reveal-left" style="font-size: 36px;">
                        Đầu thế kỷ 20, đồng hồ bỏ túi là vật dụng phổ biến và thiết thực nhất để mọi người theo dõi thời gian.
                    </div>
                    <div class="reveal-right">
                        Hans Wilsdorf, bắt đầu sự nghiệp từ năm 1900, làm việc cho một công ty đồng hồ ở La Chaux-de-Fonds, ông luôn quan sát những thay đổi trong lối sống thời đại, đặc biệt là sự phổ biến không ngừng của các hoạt động thể thao và vận động ngoài trời. Vài năm sau khi sáng lập nên thương hiệu Rolex, ông nhận ra rằng đồng hồ bỏ túi, vật dụng vốn luôn được bảo vệ trong các nếp gấp quần áo, đã không còn phù hợp với những thói quen sử dụng mới của thời đại. Với tầm nhìn táo bạo, ông quyết định sáng tạo nên những chiếc đồng hồ đeo trên cổ tay người dùng, một vật dụng có thể khiến chủ nhân tin tưởng với độ chính xác và đáng tin cậy, phù hợp với đời sống năng động, hiện đại.
                    </div>
                </div>
            </div>

<div style="max-width: 900px; margin: 0 auto; padding: 20px 0;">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 38px; color: #222; margin-bottom: 20px;">
                    Thế giới dưới mặt nước
                </h2>
                
                <p style="font-size: 18px; line-height: 1.8; color: #444; text-align: justify; margin-bottom: 40px;">
                    Giống như việc thay đổi lối sống đã thúc đẩy Rolex phát minh ra một chiếc vỏ chống thấm nước, thương hiệu đã chuyển hướng sang thiết kế và phát triển đồng hồ đeo tay có thể đáp ứng nhu cầu của những chuyên gia tiên phong trên hành trình lặn sâu. Năm 1953, Submariner là chiếc đồng hồ đeo tay đầu tiên dành cho thợ lặn với khả năng chống thấm nước ở độ sâu lên đến 100 mét (330 feet). Vành đồng hồ xoay với các vòng số chia độ cho phép thợ lặn theo dõi thời gian của họ dưới nước và dễ dàng quản lý bình dự trữ khí thở. Tính an toàn của vỏ Oyster tiếp tục được tăng cường với núm lên dây siết ngược mới và hệ thống Twinlock tích hợp hai gioăng bảo vệ kín nước.
                </p>

<div style="display: flex; flex-direction: column; align-items: center; margin: 40px 0; width: 100%;">
    <img src="image/baiviet/rolex8.avif" alt="Rolex Submariner Anchor" onerror="this.src='image/rolex8.avif'" 
         style="width: 100%; max-width: 450px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: block;">
</div>

                <p style="font-size: 18px; line-height: 1.8; color: #444; text-align: justify; margin-bottom: 50px;">
                    Năm 1970, tăng thêm vùng bảo vệ với sự ra đời của gioăng thứ ba, núm vặn Triplock đã ra đời. Vạch dấu giờ và kim được phủ lên mình một vật liệu phát quang, cho phép thợ lặn đọc thời gian trong điều kiện tối dưới nước. Rolex tiếp tục đạt được những tiến bộ kỹ thuật hơn nữa đảm bảo Submariner không thấm nước ở độ sâu 200 mét (660 feet) vào năm 1954 và 300 mét (1.000 feet) vào năm 1989. Phiên bản có thông số ngày được giới thiệu vào năm 1969, có khả năng chống thấm nước ở độ sâu 300 mét (1.000 feet) vào năm 1979.
                </p>

                <p style="font-size: 18px; line-height: 1.8; color: #444; text-align: justify; margin-bottom: 40px;">
                    Rolex là một trong những thương hiệu đầu tiên đồng hành cùng các cá nhân xuất sắc trong hành trình thám hiểm của họ. Nhận thức được nhiều thuận lợi cho cả hai phía và coi thế giới thực như một phòng thí nghiệm sống, Hans Wilsdorf đã trang bị đồng hồ Oyster cho các hành trình khám phá của nhiều nhà thám hiểm. Để thử nghiệm độ tin cậy của đồng hồ, Rolex đã yêu cầu các thợ lặn chuyên nghiệp đeo chúng trong các nhiệm vụ, sau đó thu thập đề xuất để cải tiến công thái học hoặc kỹ thuật chế tác. Điều này đã trở thành một phần không thể thiếu trong quá trình phát triển Rolex.
                </p>

<div style="display: flex; flex-direction: column; align-items: center; margin: 40px 0; width: 100%;">
    <img src="image/baiviet/rolex10.avif" alt="Dimitri Rebikoff" onerror="this.src='image/rolex10.avif'" 
         style="width: 100%; max-width: 700px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: block;">
    <p style="font-size: 14px; color: #888; margin-top: 15px; font-style: italic; text-align: center;">
        Nhiếp ảnh gia kiêm kỹ sư Dimitri Rebikoff
    </p>
</div>

                <p style="font-size: 18px; line-height: 1.8; color: #444; text-align: justify; margin-bottom: 50px;">
                    Một trong những nhân vật cộng tác cùng Rolex để thử nghiệm đồng hồ Submariner là nhiếp ảnh gia dưới biển sâu kiêm kỹ sư và nhà thám hiểm người Pháp Dimitri Rebikoff. Trong suốt 5 tháng, Rebikoff đã thực hiện 132 thử nghiệm lặn với đồng hồ Rolex, cùng ông chinh phục độ sâu từ 12 đến 60 mét. Phản hồi của ông rất tích cực: “Chúng tôi có thể xác nhận rằng chiếc đồng hồ này không chỉ hoàn toàn thỏa mãn các tiêu chí trong điều kiện lặn cực kỳ khó khăn và đặc biệt nguy hiểm đối với vật liệu được sử dụng, nó còn chứng tỏ bản thân là một thiết bị độc lập, một phụ kiện không thể thiếu cho việc lặn.”
                </p>

            <div style="background-color: #000b1e; width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; margin-top: 50px; color: #fff;">
                
                <img src="image/baiviet/rolex11.avif" alt="Rolex Deepsea" onerror="this.src='image/rolex11.avif'" 
                     style="width: 100%; display: block; -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%); mask-image: linear-gradient(to bottom, black 80%, transparent 100%);">
                
                <div style="max-width: 850px; margin: 0 auto; padding: 60px 20px; text-align: center;">
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 48px; font-weight: bold; margin-bottom: 30px; color: #fff;">Vùng sâu thăm thẳm</h2>
                    <p style="font-size: 19px; line-height: 1.8; color: #d1d9e6; text-align: justify; margin-bottom: 25px;">
                        Rolex tiếp tục thách thức áp lực dưới nước trong hành trình không khoan nhượng trước các tiêu chuẩn hoàn hảo cho sản phẩm đồng hồ của mình. Năm 2008, thương hiệu đã ra mắt Rolex Deepsea, với kết cấu vỏ đã được cấp bằng sáng chế – hệ thống Ringlock – cho phép đồng hồ chịu được tác động của áp lực ở độ sâu 3.900 mét (12.800 feet). Hệ thống này bao gồm mặt kính sapphire dáng bầu nhẹ, vòng nén bằng thép hợp kim ni-tơ và nắp lưng được chế tác từ hợp kim titanium. Vành đồng hồ xoay một chiều của Rolex Deepsea được gắn vòng số Cerachrom màu đen có các vạch chia 60 phút cho phép thợ lặn theo dõi thời gian dưới nước một cách an toàn.
                    </p>
                    <p style="font-size: 19px; line-height: 1.8; color: #d1d9e6; text-align: justify;">
                        Các đặc tính của gốm công nghệ cao này tạo nên vòng số đặc biệt mạnh mẽ, hầu như không bị trầy xước và màu sắc không bị ảnh hưởng bởi các tia cực tím, giữ vững độ ổn định theo thời gian. Chiếc đồng hồ đáp ứng độ sâu cực cao này cũng được trang bị một phát minh độc quyền khác giúp tăng cường khả năng đọc: màn hình hiển thị Chromalight. Một vật liệu phát quang cách tân phát ra ánh sáng màu xanh được phủ lên kim, vạch dấu giờ và bộ phát quang trên vành đồng hồ. Thời gian phát sáng gần gấp đôi so với vật liệu lân quang tiêu chuẩn và cường độ phát sáng phù hợp hơn theo thời gian phát xạ.
                    </p>
                </div>
<div style="max-width: 900px; margin: 0 auto; padding: 40px 20px 50px 20px;">
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 40px; width: 100%;">
                        <img src="image/baiviet/rolex9.avif" alt="Rolex Deepsea Challenge" onerror="this.src='image/rolex9.avif'" 
                             style="width: 100%; max-width: 600px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    </div>
                    <p style="font-size: 19px; line-height: 1.8; color: #d1d9e6; text-align: justify; margin-bottom: 25px;">
                        Theo tiêu chuẩn của dòng đồng hồ này, tất cả đồng hồ Rolex dành cho thợ lặn đều được kiểm nghiệm khả năng chống thấm nước ở độ sâu tăng thêm 25%. Điều này có nghĩa là tại phòng thí nghiệm, trong bình cao áp do Rolex và Comex hợp tác phát triển, đồng hồ Rolex Deepsea (phải chịu áp lực tác động ở độ sâu 3.900 mét) được thử nghiệm đảm bảo chống thấm nước hiệu quả đến 4.875 mét.
                    </p>
                    <p style="font-size: 19px; line-height: 1.8; color: #d1d9e6; text-align: justify; margin-bottom: 0;">
                        Rolex Deepsea chính là nguồn cảm hứng cho Rolex Deepsea Challenge - mẫu đồng hồ dành cho thợ lặn, đã được gắn liền với một bên tàu lặn do nhà thám hiểm và nhà làm phim James Cameron điều khiển vào ngày 26 tháng 3 năm 2012, để chinh phục độ sâu mà Jacques Piccard và Don Walsh đã thử thách vào năm 1960: đó là rãnh Mariana. Bảo đảm chống thấm nước ở độ sâu 12.000 mét (39.370 feet), chiếc đồng hồ mang trong mình tất cả các cải tiến kỹ thuật của thương hiệu về khả năng chống thấm nước, và trong các giai đoạn thử nghiệm đã chống chịu thành công với áp lực từ độ sâu 15.000 mét. Ở độ sâu này, vòng trung tâm của hệ thống Ringlock chịu áp lực tương đương với trọng lượng 20 tấn.
                    </p>
                </div>
                
<div class="director-container" style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; overflow: hidden; background: #fff; margin-top: -1px; display: flex; flex-direction: column;">
                <img src="image/baiviet/avatar.avif" alt="James Cameron" onerror="this.src='image/avatar.avif'" 
                     style="width: 100%; height: auto; display: block; margin: 0; padding: 0;">
                
<div style="padding: 50px 20px; text-align: center; font-size: 20px; min-height: 1.5em; background: #fff; color: #000; font-family: 'Segoe UI', Arial, sans-serif;">
                
                <span style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 24px;">James Cameron</span>
                
                <span style="font-weight: normal;"> — Đạo diễn loạt bom tấn Avatar, người đồng hành cùng Rolex </span>
                
                <span id="typewriter-caption" style="font-weight: normal;"></span>
                
            </div>
            </div>
            </div>
            </div>
            </div>
        </div>
    </div>
                
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


    <script>
        window.addEventListener('scroll', function() {
            // Lấy TẤT CẢ các khối có class 'scroll-reveal-container'
            const containers = document.querySelectorAll('.scroll-reveal-container');
            const windowHeight = window.innerHeight;

            // Chạy vòng lặp để áp dụng hiệu ứng cho từng khối một
            containers.forEach(container => {
                const overlay = container.querySelector('.reveal-overlay');
                const content = container.querySelector('.reveal-content');
                
                if (!overlay || !content) return;

                const rect = container.getBoundingClientRect();
                let distanceToBottom = rect.bottom - windowHeight;

                let startFade = 400; 
                let endFade = 0;    

                let progress = (startFade - distanceToBottom) / (startFade - endFade);
                progress = Math.max(0, Math.min(1, progress));

                overlay.style.opacity = progress;
                content.style.opacity = progress;
                content.style.transform = `translateX(-50%) translateY(${30 - (progress * 30)}px)`;
            });
        });
    </script>


<script>
        document.addEventListener("DOMContentLoaded", function() {
            const dynamicElement = document.getElementById('typewriter-caption');
            
            // CHỈ GÕ NHỮNG CHỮ NÀY
            const textToType = "chinh phục rãnh Mariana."; 
            let isStarted = false;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    // Khi lướt tới khu vực có ảnh đạo diễn (ngưỡng 50%)
                    if (entry.isIntersecting && !isStarted) {
                        isStarted = true;
                        typeWriterEffect(textToType, 0);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 }); 

            function typeWriterEffect(text, i) {
                if (i < text.length) {
                    dynamicElement.innerHTML += text.charAt(i);
                    setTimeout(() => typeWriterEffect(text, i + 1), 80); // Tốc độ gõ chữ 80ms
                }
            }

            // Gắn radar theo dõi vào toàn bộ khối ảnh
            const container = document.querySelector('.director-container');
            if (container && dynamicElement) {
                observer.observe(container);
            }
        });
    </script>

<?php
//include 'thongbao.php';
include 'ai-chatbot.php';
include $path_prefix . 'footer.php'; 
?>