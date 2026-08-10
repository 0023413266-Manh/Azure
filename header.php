<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$path = isset($path_prefix) ? $path_prefix : '';

include_once $path . 'admin/connect.php';

if (file_exists($path . 'azure_translator.php')) {
    require_once $path . 'azure_translator.php';
}

// 1. Cập nhật Ngôn ngữ được chọn
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('site_lang', $_GET['lang'], time() + 86400 * 30, '/');
    $_COOKIE['site_lang'] = $_GET['lang'];
}
$current_lang = $_COOKIE['site_lang'] ?? ($_SESSION['lang'] ?? 'vi');

// 2. Hàm giữ nguyên URL khi đổi ngôn ngữ
function getLangUrl($lang) {
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}

// 3. Bật bộ hứng HTML nếu không phải tiếng Việt
if ($current_lang !== 'vi') {
    ob_start();
}

// Xác định mã ngôn ngữ cho Azure Speech STT
$stt_lang = 'vi-VN';
if ($current_lang === 'en') $stt_lang = 'en-US';
if ($current_lang === 'ja') $stt_lang = 'ja-JP';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeless - Thế giới đồng hồ</title>
    
    <!-- CSS CHÍNH -->
    <link rel="stylesheet" href="<?= $path ?>style.css">
    
    <!-- CSS RIÊNG CỦA TỪNG TRANG -->
    <?php if (isset($custom_css)): ?>
        <link rel="stylesheet" href="<?= $custom_css ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        .main-nav ul li a.active-menu {
            color: #b58b5a !important;
            font-weight: bold;
        }
        .user-box { display: flex; align-items: center; gap: 12px; }
        .lang-switch { display: flex; align-items: center; gap: 6px; background: #f8f8f8; padding: 4px 10px; border-radius: 20px; border: 1px solid #e0e0e0; font-size: 13px; }
        .lang-switch a { text-decoration: none; color: #555; font-weight: 600; transition: 0.2s; }
        .lang-switch a:hover, .lang-switch a.active { color: #b58b5a; }
        
        /* Cấu hình ô tìm kiếm gắn Mic ở Header */
        .header-search-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .header-search-input {
            padding: 6px 60px 6px 12px !important;
            border-radius: 18px !important;
            border: 1px solid #b58b5a !important;
            outline: none;
            font-size: 13px;
            width: 180px;
            transition: all 0.3s ease;
        }
        .header-search-input:focus {
            width: 230px;
        }
        .btn-mic-header {
            position: absolute;
            right: 28px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 4px;
            outline: none;
        }
        .btn-search-header {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 4px;
            outline: none;
        }
    </style>
</head>
<body>

<div id="smart-header">
    <header class="top-header">
        <div class="logo">
            <a href="<?= $path ?>index.php" class="logo-link">
                <h1>TIMELESS</h1>
                <img src="<?= $path ?>image/logo.png" alt="Timeless Icon">
            </a>
        </div>

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

    <nav class="main-nav">
        <ul>
            <li>
                <a href="<?= $path ?>index.php" class="<?= ($current_page == 'index.php') ? 'active-menu' : '' ?>">
                    TRANG CHỦ
                </a>
            </li>

            <li class="dropdown">
                <a href="#" class="<?= (strpos($current_page, 'all_') !== false || strpos($current_page, 'casio') !== false || strpos($current_page, 'rolex') !== false || strpos($current_page, 'omega') !== false || strpos($current_page, 'seiko') !== false || strpos($current_page, 'hublot') !== false) ? 'active-menu' : '' ?>">
                    THƯƠNG HIỆU <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-content">
                    <li><a href="<?= $path ?>all_rolex.php">ROLEX</a></li>
                    <li><a href="<?= $path ?>all_omega.php">OMEGA</a></li>
                    <li><a href="<?= $path ?>all_casio.php">CASIO</a></li>
                    <li><a href="<?= $path ?>all_seiko.php">SEIKO</a></li>
                    <li><a href="<?= $path ?>all_hublot.php">HUBLOT</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="<?= (in_array($current_page, ['Dongho_nam.php', 'Dongho_nu.php'])) ? 'active-menu' : '' ?>">
                    SẢN PHẨM <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-content">
                    <li><a href="<?= $path ?>Dongho_nam.php">DÀNH CHO NAM</a></li>
                    <li><a href="<?= $path ?>Dongho_nu.php">DÀNH CHO NỮ</a></li>
                </ul>
            </li>

            <li>
                <a href="<?= $path ?>explore.php" class="<?= ($current_page == 'explore.php') ? 'active-menu' : '' ?>">
                    KHÁM PHÁ
                </a>
            </li>

            <li>
                <a href="<?= $path ?>contact.php" class="<?= ($current_page == 'contact.php') ? 'active-menu' : '' ?>">
                    LIÊN HỆ
                </a>
            </li>

<!-- 1. FORM ẨN TỰ ĐỘNG GỬI ẢNH KHI BẤM CHỌN MÁY ẢNH (ĐÃ SỬA TÊN THÀNH search_image) -->
<form id="headerAiImageForm" action="<?= $path ?>search.php" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="file" id="headerAiFileInput" name="search_image" accept="image/*" onchange="submitHeaderImage()">
</form>

<script>
function submitHeaderImage() {
    var fileInput = document.getElementById('headerAiFileInput');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        // Kiểm tra nếu dung lượng quá lớn (>10MB)
        if (fileInput.files[0].size > 10 * 1024 * 1024) {
            alert('File ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 10MB.');
            return;
        }
        // Đổi con trỏ chuột sang dạng chờ và gửi form
        document.body.style.cursor = 'wait';
        document.getElementById('headerAiImageForm').submit();
    }
}
</script>

<!-- 2. KHUNG TÌM KIẾM TRÊN HEADER -->
<li class="nav-icons">
    <!-- KHUNG TÌM KIẾM CÓ MICRO VÀ MÁY ẢNH AI TRÊN HEADER -->
    <div class="search-box">
        <form action="<?= $path ?>search.php" method="GET" style="margin: 0;">
            <div class="header-search-container" style="display: inline-flex !important; align-items: center !important; justify-content: space-between !important; border: 1px solid #b58b5a; border-radius: 20px; padding: 2px 10px; background: #fff; width: 270px; position: relative;">
                
                <!-- Ô nhập chữ -->
                <input type="text" name="query" id="headerSearchInput" placeholder="Bạn tìm gì..." class="header-search-input" required style="border: none !important; outline: none !important; background: transparent !important; flex: 1 !important; width: 100% !important; font-size: 13px; padding: 2px 5px; margin: 0; position: static !important;">
                
                <!-- KHUNG CHỨA 3 ICON ĐỨNG XẾP HÀNG (MICRO - MÁY ẢNH - KÍNH LÚP) -->
                <div style="display: flex !important; align-items: center !important; gap: 8px !important; margin-left: 5px;">
                    
                    <!-- 1. Nút Micro -->
                    <button type="button" id="headerMicBtn" class="btn-mic-header" title="Tìm bằng giọng nói (Azure AI)" style="background: none !important; border: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer; display: flex !important; align-items: center !important; position: static !important; float: none !important;">
                        <i class="fa-solid fa-microphone" id="headerMicIcon" style="color: #b58b5a; font-size: 13px;"></i>
                    </button>

                    <!-- 2. Nút Máy ảnh 📷 (Dùng onclick trực tiếp thay cho <label> để không bị xung đột) -->
                    <div class="btn-camera-header" onclick="document.getElementById('headerAiFileInput').click();" title="Tìm kiếm bằng hình ảnh (Azure AI)" style="cursor: pointer; padding: 0 !important; margin: 0 !important; display: flex !important; align-items: center !important; position: static !important; float: none !important;">
                        <i class="fa-solid fa-camera" style="color: #007bff; font-size: 13px;"></i>
                    </div>

                    <!-- 3. Nút Kính lúp 🔍 -->
                    <button type="submit" class="btn-search-header" style="background: none !important; border: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer; display: flex !important; align-items: center !important; position: static !important; float: none !important;">
                        <i class="fa-solid fa-magnifying-glass" style="color: #666; font-size: 13px;"></i>
                    </button>

                </div>

            </div>
        </form>
    </div>
</li>

    <!-- Giỏ hàng -->
    <a href="<?= $path ?>cart.php" class="icon-cart">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-text">Giỏ hàng</span>
    </a>
</li>
        </ul>
    </nav>
</div>

<!-- JAVASCRIPT XỬ LÝ AZURE SPEECH TOÀN TRANG -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Tự động tìm đúng nút Micro và Ô Nhập Liệu dù đang ở Header hay Trang Search
    const micBtn = document.getElementById('azureMicBtn') || document.getElementById('headerMicBtn') || document.querySelector('.fa-microphone')?.parentElement;
    const searchInput = document.getElementById('searchInput') || document.getElementById('headerSearchInput') || document.querySelector('input[name="query"]');

    if (!micBtn || !searchInput) return;

    let audioContext;
    let recorderStream;
    let audioChunks = [];
    let isRecording = false;
    let processor;
    let input;
    let recordingTimer;
    let countdownInterval;

    micBtn.addEventListener('click', async function (e) {
        e.preventDefault();

        // Nếu đang thu âm mà bấm lại -> Dừng ngay và gửi dữ liệu
        if (isRecording) {
            stopRecording();
            return;
        }

        try {
            recorderStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioContext = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: 16000 });
            input = audioContext.createMediaStreamSource(recorderStream);
            processor = audioContext.createScriptProcessor(4096, 1, 1);

            audioChunks = [];
            processor.onaudioprocess = (evt) => {
                const channel = evt.inputBuffer.getChannelData(0);
                audioChunks.push(new Float32Array(channel));
            };

            input.connect(processor);
            processor.connect(audioContext.destination);

            isRecording = true;
            micBtn.style.color = '#ff0000';

            // Cấu hình thời gian ghi âm: 8 Giây
            let timeLeft = 4;
            searchInput.placeholder = `Đang nghe... (còn ${timeLeft}s)`;

            // Hiệu ứng đếm ngược thời gian trên thanh tìm kiếm
            countdownInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft > 0) {
                    searchInput.placeholder = `Đang nghe... (còn ${timeLeft}s)`;
                } else {
                    clearInterval(countdownInterval);
                }
            }, 1000);

            // Tự động dừng sau đúng 8 giây (8000 ms)
            recordingTimer = setTimeout(() => {
                if (isRecording) stopRecording();
            }, 8000);

        } catch (err) {
            alert('Vui lòng cấp quyền Micro cho trình duyệt!');
        }

        async function stopRecording() {
            if (!isRecording) return;
            isRecording = false;

            // Dọn dẹp Timer đếm ngược
            clearTimeout(recordingTimer);
            clearInterval(countdownInterval);

            processor.disconnect();
            input.disconnect();
            recorderStream.getTracks().forEach(track => track.stop());

            micBtn.style.color = '';
            searchInput.placeholder = 'Azure đang xử lý...';

            const wavBlob = createWavBlob(audioChunks, 16000);
            const formData = new FormData();
            formData.append('audio', wavBlob, 'speech.wav');
            formData.append('lang', '<?= $stt_lang ?>'); // 🎯 ĐÃ SỬA: Gửi đúng ngôn ngữ đang chọn (vi-VN, en-US, ja-JP)

            try {
                const response = await fetch('<?= $path ?>azure_speech.php', { method: 'POST', body: formData });
                const result = await response.json();

                console.log("👉 Kết quả Azure trả về:", result);

                if (result.status === 'success' && result.text && result.text.trim() !== '') {
                    searchInput.value = result.text.replace(/\.$/, '');
                    searchInput.form.submit();
                } else {
                    searchInput.placeholder = 'Không nghe rõ, thử lại...';
                }
            } catch (err) {
                console.error("Lỗi:", err);
                searchInput.placeholder = 'Lỗi kết nối Server!';
            }
        }
    });

    // Hàm mã hóa WAV PCM 16kHz
    function createWavBlob(chunks, sampleRate) {
        let totalSamples = chunks.reduce((acc, curr) => acc + curr.length, 0);
        let samples = new Float32Array(totalSamples);
        let offset = 0;
        for (let chunk of chunks) {
            samples.set(chunk, offset);
            offset += chunk.length;
        }

        let buffer = new ArrayBuffer(44 + samples.length * 2);
        let view = new DataView(buffer);

        function writeString(view, offset, string) {
            for (let i = 0; i < string.length; i++) {
                view.setUint8(offset + i, string.charCodeAt(i));
            }
        }

        writeString(view, 0, 'RIFF');
        view.setUint32(4, 36 + samples.length * 2, true);
        writeString(view, 8, 'WAVE');
        writeString(view, 12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        view.setUint16(22, 1, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, sampleRate * 2, true);
        view.setUint16(32, 2, true);
        view.setUint16(34, 16, true);
        writeString(view, 36, 'data');
        view.setUint32(40, samples.length * 2, true);

        let idx = 44;
        for (let i = 0; i < samples.length; i++, idx += 2) {
            let s = Math.max(-1, Math.min(1, samples[i]));
            view.setInt16(idx, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
        }

        return new Blob([buffer], { type: 'audio/wav' });
    }

});
</script>