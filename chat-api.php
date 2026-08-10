<?php
// 1. TẮT HIỂN THỊ LỖI DẠNG HTML ĐỂ KHÔNG BỊ VĂNG LỖI "Unexpected token '<'" TRÊN JS
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    if (!extension_loaded('curl') || !function_exists('curl_init')) {
        throw new Exception("PHP trên XAMPP chưa bật cURL extension!");
    }

    // 2. TỰ ĐỘNG NẠP FILE ENV_LOADER.PHP (TÌM CẢ Ở FOLDER HIỆN TẠI VÀ FOLDER CHA)
    $envPath = __DIR__ . '/env_loader.php';
    if (!file_exists($envPath)) {
        $envPath = __DIR__ . '/../env_loader.php'; // Phòng trường hợp file API nằm trong folder con (ajax/api)
    }

    if (file_exists($envPath)) {
        require_once $envPath;
    } else {
        throw new Exception("Không tìm thấy file env_loader.php ở cả thư mục hiện tại lẫn thư mục cha!");
    }

    // 3. KẾT NỐI DATABASE MYSQL (LẤY TỪ .ENV)
    $db_host = $_ENV['DB_HOST'] ?? 'localhost';
    $db_user = $_ENV['DB_USER'] ?? 'root';
    $db_pass = $_ENV['DB_PASS'] ?? '';
    $db_name = $_ENV['DB_NAME'] ?? 'timeless';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Không thể kết nối Database MySQL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // 4. TRUY VẤN LẤY DỮ LIỆU SẢN PHẨM TỰ ĐỘNG
    $sql = "SELECT id, ten_san_pham, gia_ban, anh_san_pham FROM san_pham WHERE ton_kho > 0 ORDER BY id DESC LIMIT 20";
    $result = $conn->query($sql);

    $danhSachSanPham = "";
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $giaFmt = number_format($row['gia_ban'], 0, ',', '.') . " VNĐ";
            $danhSachSanPham .= "SẢN PHẨM: " . $row['ten_san_pham'] . "\n";
            $danhSachSanPham .= "- Giá: " . $giaFmt . "\n";
            $danhSachSanPham .= "- Cú pháp Ảnh: [ANH: " . $row['anh_san_pham'] . "]\n";
            $danhSachSanPham .= "- Cú pháp Nút xem: [NUT: 👉 Xem chi tiết sản phẩm | chi_tiet_sp/chi_tiet_rolex.php?id=" . $row['id'] . "]\n\n";
        }
    } else {
        $danhSachSanPham = "Hiện tại cửa hàng đang cập nhật thêm mẫu mới.";
    }
    $conn->close();

    // 5. NHẬN LỊCH SỬ TIN NHẮN TỪ FRONTEND
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $history = isset($input['history']) && is_array($input['history']) ? $input['history'] : [];

    if (empty($history)) {
        echo json_encode(['status' => 'error', 'reply' => 'Chưa nhận được câu hỏi từ bạn.']);
        exit;
    }

    // 6. LẤY API KEY & ENDPOINT TỪ BIẾN MÔI TRƯỜNG (.ENV)
    $apiKey   = $_ENV['AZURE_OPENAI_KEY'] ?? getenv('AZURE_OPENAI_KEY') ?? '';
    $endpoint = $_ENV['AZURE_OPENAI_ENDPOINT'] ?? getenv('AZURE_OPENAI_ENDPOINT') ?? '';

    // Xóa dấu ngoặc kép hoặc khoảng trắng dư thừa nếu có trong file .env
    $apiKey   = trim($apiKey, " \t\n\r\0\x0B\"'");
    $endpoint = trim($endpoint, " \t\n\r\0\x0B\"'");

    // Cấu trúc Json báo lỗi đã được CHUẨN HÓA lại cho Javascript
    if (empty($apiKey) || empty($endpoint)) {
        echo json_encode(['status' => 'error', 'reply' => 'Chưa cấu hình AZURE_OPENAI_KEY hoặc AZURE_OPENAI_ENDPOINT trong file .env!']);
        exit;
    }

    // 7. TẠO SYSTEM PROMPT VÀ MẢNG MESSAGES
    $systemPrompt = "Bạn là AI Fashion Advisor - Trợ lý tư vấn gu thời trang và đồng hồ chuyên nghiệp cho cửa hàng Timeless.
Nhiệm vụ của bạn:
1. Lắng nghe phong cách trang phục, sự kiện hoặc ngân sách/kích thước cổ tay của khách hàng.
2. Đưa ra lời khuyên phối đồ chuẩn gu thời trang.
3. Gợi ý 1-2 mẫu đồng hồ phù hợp NHẤT từ danh sách sản phẩm thật bên dưới.

DANH SÁCH SẢN PHẨM HIỆN CÓ TRONG DATABASE CỬA HÀNG:
" . $danhSachSanPham . "

QUY TẮC BẮT BUỘC KHI GỢI Ý SẢN PHẨM:
- Khi gợi ý mẫu đồng hồ nào, BẮT BUỘC chèn đúng nguyên văn 'Cú pháp Ảnh' và 'Cú pháp Nút xem' tương ứng của mẫu đó.
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

    // Sử dụng 'max_tokens' thay vì 'max_completion_tokens' để tương thích 100% các model Azure OpenAI
    $payload = [
        'messages' => $messagesPayload,
        'temperature' => 1,
        'max_completion_tokens' => 1000
    ];

    // 8. GỬI REQUEST SANG AZURE OPENAI
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
            CURLOPT_SSL_VERIFYPEER => false, // Bắt buộc cho Localhost XAMPP
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
        throw new Exception("Lỗi cURL: " . $curlError);
    }

    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        $reply = $result['choices'][0]['message']['content'] ?? 'AI không phản hồi nội dung.';
        echo json_encode(['status' => 'success', 'reply' => $reply]);
    } else {
        $errObj = json_decode($response, true);
        $errMsg = $errObj['error']['message'] ?? $response;
        
        if ($httpCode === 0 || empty($errMsg)) {
            $errMsg = "Không thể kết nối tới Azure. Vui lòng kiểm tra AZURE_OPENAI_ENDPOINT trong file .env!";
        }
        
        echo json_encode(['status' => 'error', 'reply' => "Lỗi Azure (Mã $httpCode): " . $errMsg]);
    }

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(['status' => 'error', 'reply' => 'Lỗi Backend PHP: ' . $e->getMessage()]);
}
?>