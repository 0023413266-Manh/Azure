<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

// Tắt output buffering để in kết quả ra màn hình ngay lập tức
if (ob_get_level()) ob_end_clean();
echo "<h2>🚀 TIẾN TRÌNH QUÉT VECTOR (CÓ TỰ ĐỘNG CHỜ RATE LIMIT)</h2><hr>";

// 1. KẾT NỐI DATABASE & CẤU HÌNH
include_once 'admin/connect.php'; 

// 1. Nhúng file đọc biến môi trường (Đảm bảo đường dẫn đúng tới env_loader.php)
require_once __DIR__ . '/env_loader.php';

// 2. Lấy thông tin từ file .env (Không còn hardcode key/endpoint)
$visionEndpoint = $_ENV['AZURE_VISION_ENDPOINT'] ?? ''; 
$visionApiKey   = $_ENV['AZURE_VISION_API_KEY'] ?? ''; 

$searchService  = $_ENV['AZURE_SEARCH_SERVICE'] ?? '';
$searchApiKey   = $_ENV['AZURE_SEARCH_API_KEY'] ?? ''; 
$indexName      = $_ENV['AZURE_SEARCH_INDEX'] ?? 'products-index';

// 3. Các đường dẫn API tự động ghép từ biến môi trường
$vectorApiUrl   = rtrim($visionEndpoint, '/') . "/computervision/retrieval:vectorizeImage?api-version=2024-02-01&model-version=2023-04-15";
$azureUpdateUrl = "https://{$searchService}.search.windows.net/indexes/{$indexName}/docs/index?api-version=2023-11-01";

// 2. LẤY TẤT CẢ SẢN PHẨM TỪ MYSQL
$sql = "SELECT id, ten_san_pham, anh_san_pham FROM san_pham";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $successCount = 0;
    $errorCount = 0;

    while ($row = $result->fetch_assoc()) {
        $productId = $row['id'];
        $imagePath = trim($row['anh_san_pham']); 

        echo "<p>Đang xử lý SP ID <b>#{$productId}</b> ({$row['ten_san_pham']}) ... ";

        if (!file_exists($imagePath) || is_dir($imagePath)) {
            echo "<span style='color:red;'>❌ FILE KHÔNG TỒN TẠI</span></p>";
            $errorCount++;
            continue;
        }

        $imageData = file_get_contents($imagePath);

        // a. Gọi API sinh Vector (Có cơ chế Retry nếu bị Rate Limit 429)
        $vectorResult = null;
        $maxTries = 3;

        for ($try = 1; $try <= $maxTries; $try++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $vectorApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/octet-stream',
                'Ocp-Apim-Subscription-Key: ' . $visionApiKey
            ]);
            
            $vectorResponse = curl_exec($ch);
            $httpCodeVision = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCodeVision == 200) {
                $vectorResult = json_decode($vectorResponse, true);
                break; // Thành công -> Thoát vòng lặp thử lại
            } elseif ($httpCodeVision == 429) {
                // Bị dính hạn ngạch 20 sp/phút -> Tạm nghỉ 10 giây rồi thử lại
                echo "<br><small style='color:orange;'>⏳ Bị giới hạn tốc độ (Rate Limit 429), đang chờ 10 giây để thử lại (Lần {$try}/{$maxTries})...</small>";
                sleep(10);
            } else {
                break; // Lỗi khác -> Thoát
            }
        }

        if (!isset($vectorResult['vector'])) {
            echo " <span style='color:red;'>❌ AI KHÔNG TẠO ĐƯỢC VECTOR (HTTP $httpCodeVision)</span></p>";
            $errorCount++;
            continue;
        }

        $imageVector = $vectorResult['vector']; 

        // b. Đẩy Vector lên Azure Search Index
        $payload = [
            'value' => [
                [
                    '@search.action' => 'mergeOrUpload',
                    'id'             => (string)$productId,
                    'image_vector'   => $imageVector
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $azureUpdateUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api-key: ' . $searchApiKey
        ]);
        $updateResponse = curl_exec($ch);
        $httpCodeSearch = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCodeSearch == 200 || $httpCodeSearch == 201) {
            echo " <span style='color:green;'>✅ THÀNH CÔNG</span></p>";
            $successCount++;
        } else {
            echo " <span style='color:red;'>❌ LỖI UPDATE AZURE SEARCH (HTTP $httpCodeSearch)</span></p>";
            $errorCount++;
        }

        flush();

        // 🛑 NGHỈ 3 GIÂY GIỮA MỖI ẢNH ĐỂ KHÔNG BỊ QUÁ TẢI HẠN NGẠCH 20SP/PHÚT
        sleep(3);
    }

    echo "<hr><h3>🎉 HOÀN TẤT! Thành công: {$successCount} sản phẩm | Lỗi: {$errorCount} sản phẩm.</h3>";
} else {
    echo "Không tìm thấy sản phẩm nào trong CSDL!";
}
?>