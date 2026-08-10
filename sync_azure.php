<?php
// Kết nối Database MySQL
include_once 'admin/connect.php'; 

require_once __DIR__ . '/env_loader.php';

// 2. Lấy thông tin Azure AI Search từ file .env
$searchService = $_ENV['AZURE_SEARCH_SERVICE'] ?? '';
$apiKey        = $_ENV['AZURE_SEARCH_API_KEY'] ?? ''; 
$indexName     = $_ENV['AZURE_SEARCH_INDEX'] ?? 'products-index';
$url           = "https://{$searchService}.search.windows.net/indexes/{$indexName}/docs/index?api-version=2023-11-01";

// 1. Mảng map ID thương hiệu sang tên thương hiệu
$brand_map = [
    1 => 'Rolex',
    2 => 'Hublot',
    3 => 'Omega',
    4 => 'Casio',
    5 => 'Seiko'
];

// 2. Lấy toàn bộ sản phẩm từ Database MySQL
$sql = "SELECT * FROM san_pham";
$result = $conn->query($sql);

$documents = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $brand_id = isset($row['id_thuong_hieu']) ? (int)$row['id_thuong_hieu'] : 1;
        $brand_name = isset($brand_map[$brand_id]) ? $brand_map[$brand_id] : 'Khác';

        // Đưa dữ liệu về đúng định dạng của Index mới
        $documents[] = [
            '@search.action' => 'upload',
            'id'             => (string)$row['id'],
            'ten_san_pham'   => $row['ten_san_pham'],
            'thuong_hieu'    => $brand_name,
            'gia'            => (int)($row['gia_ban'] ?? $row['gia'] ?? 0),
            'mo_ta'          => strip_tags($row['mo_ta'] ?? '')
        ];
    }
}

// 3. Gửi Yêu cầu đẩy dữ liệu sang Azure AI Search
$payload = json_encode(['value' => $documents]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. Hiển thị kết quả
echo "<div style='font-family: Arial; padding: 20px;'>";
echo "<h2>Đồng bộ Dữ liệu lên Azure AI Search</h2>";

if ($httpCode == 200 || $httpCode == 201) {
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>
            ✅ ĐÃ NẠP THÀNH CÔNG " . count($documents) . " SẢN PHẨM LÊN AZURE INDEX MỚI!
          </p>";
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>
            ❌ CÓ LỖI XẢY RA (Mã lỗi: $httpCode):
          </p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($response) . "</pre>";
}
echo "</div>";
?>