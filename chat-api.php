<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    if (!extension_loaded('curl') || !function_exists('curl_init')) {
        throw new Exception("PHP server chưa bật cURL extension!");
    }

    // 1. TỰ ĐỘNG NẠP FILE ENV_LOADER.PHP
    $envPath = __DIR__ . '/env_loader.php';
    if (!file_exists($envPath)) {
        $envPath = __DIR__ . '/../env_loader.php';
    }

    if (file_exists($envPath)) {
        require_once $envPath;
    }

    // 2. KẾT NỐI DATABASE MYSQL
    $db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
    $db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $db_pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    $db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'timeless';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối MySQL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // 3. NHẬN LỊCH SỬ TIN NHẮN TỪ FRONTEND
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $history = isset($input['history']) && is_array($input['history']) ? $input['history'] : [];

    if (empty($history)) {
        echo json_encode(['status' => 'error', 'reply' => 'Chưa nhận được câu hỏi từ bạn.']);
        exit;
    }

    // Lấy câu hỏi mới nhất của người dùng
    $lastUserMsg = '';
    for ($i = count($history) - 1; $i >= 0; $i--) {
        if (isset($history[$i]['role']) && $history[$i]['role'] === 'user') {
            $lastUserMsg = mb_strtolower($history[$i]['content'], 'UTF-8');
            break;
        }
    }

    // 4. TRUY VẤN DỮ LIỆU SẢN PHẨM THÔNG MINH
    $searchCondition = "";
    if (!empty($lastUserMsg)) {
        $brands = ['rolex', 'hublot', 'omega', 'casio', 'seiko', 'tissot', 'orient', 'citizen', 'cartier', 'patek'];
        $foundBrands = [];
        foreach ($brands as $b) {
            if (strpos($lastUserMsg, $b) !== false) {
                $foundBrands[] = "LOWER(ten_san_pham) LIKE '%" . $conn->real_escape_string($b) . "%'";
            }
        }
        if (!empty($foundBrands)) {
            $searchCondition = " AND (" . implode(" OR ", $foundBrands) . ")";
        }
    }

    if (!empty($searchCondition)) {
        $sql = "SELECT id, ten_san_pham, gia_ban, anh_san_pham FROM san_pham WHERE ton_kho > 0 $searchCondition ORDER BY id DESC LIMIT 30";
    } else {
        $sql = "SELECT id, ten_san_pham, gia_ban, anh_san_pham FROM san_pham WHERE ton_kho > 0 ORDER BY id DESC LIMIT 60";
    }

    $result = $conn->query($sql);

    $danhSachSanPham = "";
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $giaFmt = number_format($row['gia_ban'], 0, ',', '.') . " VNĐ";
            $tenSpLower = mb_strtolower($row['ten_san_pham'], 'UTF-8');

            // 🎯 TỰ ĐỘNG CHỌN ĐÚNG FILE PHP CHI TIẾT THEO HÃNG
            $fileDetail = 'chi_tiet_rolex.php'; // File mặc định
            if (strpos($tenSpLower, 'hublot') !== false) {
                $fileDetail = 'chi_tiet_hublot.php';
            } elseif (strpos($tenSpLower, 'omega') !== false) {
                $fileDetail = 'chi_tiet_omega.php';
            } elseif (strpos($tenSpLower, 'casio') !== false) {
                $fileDetail = 'chi_tiet_casio.php';
            } elseif (strpos($tenSpLower, 'seiko') !== false) {
                $fileDetail = 'chi_tiet_seiko.php';
            } elseif (strpos($tenSpLower, 'rolex') !== false) {
                $fileDetail = 'chi_tiet_rolex.php';
            }

            // Dấu / ở đầu (/chi_tiet_sp/...) để chống lỗi 404 lặp thư mục
            $linkChiTiet = "chi_tiet_sp/" . $fileDetail . "?id=" . $row['id'];

            $danhSachSanPham .= "SẢN PHẨM: " . $row['ten_san_pham'] . "\n";
            $danhSachSanPham .= "- Giá: " . $giaFmt . "\n";
            $danhSachSanPham .= "- Cú pháp Ảnh: [ANH: " . $row['anh_san_pham'] . "]\n";
            $danhSachSanPham .= "- Cú pháp Nút xem: [NUT: 👉 Xem chi tiết sản phẩm | " . $linkChiTiet . "]\n\n";
        }
    } else {
        $danhSachSanPham = "Không tìm thấy sản phẩm nào phù hợp trực tiếp trong hệ thống.";
    }
    $conn->close();

    // 5. LẤY API KEY & ENDPOINT
    $apiKey   = $_ENV['AZURE_OPENAI_KEY'] ?? getenv('AZURE_OPENAI_KEY') ?? '';
    $endpoint = $_ENV['AZURE_OPENAI_ENDPOINT'] ?? getenv('AZURE_OPENAI_ENDPOINT') ?? '';

    $apiKey   = trim($apiKey, " \t\n\r\0\x0B\"'");
    $endpoint = trim($endpoint, " \t\n\r\0\x0B\"'");

    if (empty($apiKey) || empty($endpoint)) {
        echo json_encode(['status' => 'error', 'reply' => 'Chưa cấu hình AZURE_OPENAI_KEY hoặc AZURE_OPENAI_ENDPOINT!']);
        exit;
    }

    // 6. TẠO SYSTEM PROMPT
    $systemPrompt = "Bạn là AI Fashion Advisor - Trợ lý tư vấn gu thời trang và đồng hồ chuyên nghiệp cho cửa hàng Timeless.
Nhiệm vụ của bạn:
1. Lắng nghe phong cách trang phục, sự kiện hoặc ngân sách/kích thước cổ tay của khách hàng.
2. Đưa ra lời khuyên phối đồ chuẩn gu thời trang.
3. Gợi ý 1-2 mẫu đồng hồ phù hợp NHẤT từ danh sách sản phẩm thật bên dưới.

DANH SÁCH SẢN PHẨM HIỆN CÓ TRONG DATABASE CỬA HÀNG:
" . $danhSachSanPham . "

QUY TẮC BẮT BUỘC KHI GỢI Ý SẢN PHẨM:
- Chỉ tư vấn và giới thiệu các sản phẩm CÓ TRONG DANH SÁCH TRÊN.
- Khi gợi ý mẫu đồng hồ nào, BẮT BUỘC chèn đúng nguyên văn 'Cú pháp Ảnh' và 'Cú pháp Nút xem' tương ứng của mẫu đó.
- Tuyệt đối không sửa đổi hay giải thích đường dẫn URL trong 'Cú pháp Nút xem'.
- Không tự ý bịa thêm đường dẫn ảnh hoặc link sản phẩm không có trong danh sách trên.";

    $messagesPayload = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];

    foreach ($history as $msg) {
        if (isset($msg['role']) && isset($msg['content'])) {
            $messagesPayload[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ];
        }
    }

    $payload = [
        'messages' => $messagesPayload,
        'temperature' => 1,
        'max_completion_tokens' => 1000
    ];

    // 7. GỬI REQUEST SANG AZURE OPENAI
    $maxRetries = 3;
    $response = false;
    $curlError = '';
    $httpCode = 0;

    for ($i = 0; $i < $maxRetries; $i++) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'api-key: ' . $apiKey
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$curlError && $response !== false && $httpCode === 200) {
            break;
        }
        usleep(300000);
    }

    if ($curlError) {
        throw new Exception("Lỗi cURL kết nối Azure: " . $curlError);
    }

    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        $reply = $result['choices'][0]['message']['content'] ?? 'AI không phản hồi nội dung.';
        echo json_encode(['status' => 'success', 'reply' => $reply]);
    } else {
        $errObj = json_decode($response, true);
        $errMsg = $errObj['error']['message'] ?? $response;
        
        if ($httpCode === 0 || empty($errMsg)) {
            $errMsg = "Không thể kết nối tới Azure OpenAI. Vui lòng kiểm tra AZURE_OPENAI_ENDPOINT!";
        }
        
        echo json_encode(['status' => 'error', 'reply' => "Lỗi Azure (Mã $httpCode): " . $errMsg]);
    }

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(['status' => 'error', 'reply' => 'Lỗi Backend PHP: ' . $e->getMessage()]);
}
?>