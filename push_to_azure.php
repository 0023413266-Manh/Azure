<?php
// 1. Kết nối đến Database MySQL của bạn
require_once __DIR__ . '/admin/connect.php';

// 2. Điền thông tin Azure Portal
$searchService = "webbandongho-search";
$apiKey        = "1fZNf5cu6rAANOVvanQlZZQNQKUOBKi8bzP8OjiUFtAzSeCZhQva"; 
$indexName     = "products-index";

// =========================================================================
// BƯỚC BỔ SUNG: TỰ ĐỘNG KHỞI TẠO INDEX VỚI QUYỀN TÌM KIẾM (SEARCHABLE = TRUE)
// =========================================================================
$createIndexUrl = "https://{$searchService}.search.windows.net/indexes/{$indexName}?api-version=2023-11-01";

$indexSchema = [
    'name' => $indexName,
    'fields' => [
        ['name' => 'id', 'type' => 'Edm.String', 'key' => true, 'retrievable' => true],
        ['name' => 'ten_san_pham', 'type' => 'Edm.String', 'searchable' => true, 'retrievable' => true],
        ['name' => 'thuong_hieu', 'type' => 'Edm.String', 'searchable' => true, 'retrievable' => true, 'filterable' => true, 'facetable' => true],
        ['name' => 'gia', 'type' => 'Edm.Int64', 'retrievable' => true, 'filterable' => true, 'sortable' => true],
        ['name' => 'mo_ta', 'type' => 'Edm.String', 'searchable' => true, 'retrievable' => true]
    ]
];

$chIndex = curl_init();
curl_setopt($chIndex, CURLOPT_URL, $createIndexUrl);
curl_setopt($chIndex, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($chIndex, CURLOPT_POSTFIELDS, json_encode($indexSchema));
curl_setopt($chIndex, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chIndex, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $apiKey
]);
$indexResponse = curl_exec($chIndex);
curl_close($chIndex);

// =========================================================================
// 3. LẤY DỮ LIỆU TỪ MYSQL
// =========================================================================
$sql = "SELECT id, ten_san_pham, gia_ban, mo_ta, anh_san_pham FROM san_pham"; 
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Lỗi kết nối MySQL: " . mysqli_error($conn));
}

$docs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $docs[] = [
        '@search.action' => 'upload',
        'id'            => (string)$row['id'],
        'ten_san_pham'  => $row['ten_san_pham'],
        'thuong_hieu'   => 'Rolex', // Hoặc để trống nếu chưa join bảng thương hiệu
        'gia'           => (int)$row['gia_ban'],
        'mo_ta'         => !empty($row['mo_ta']) ? strip_tags($row['mo_ta']) : 'Đồng hồ cao cấp'
    ];
}

// =========================================================================
// 4. GỬI DỮ LIỆU LÊN AZURE AI SEARCH
// =========================================================================
$url = "https://{$searchService}.search.windows.net/indexes/{$indexName}/docs/index?api-version=2023-11-01";

$data = json_encode(['value' => $docs]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'api-key: ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200 || $httpCode == 201) {
    echo "<h2 style='color:green;'>🎉 ĐÃ KHỞI TẠO INDEX VÀ ĐẨY THÀNH CÔNG " . count($docs) . " SẢN PHẨM LÊN AZURE SEARCH!</h2>";
} else {
    echo "<h2 style='color:red;'>❌ Báo lỗi từ Azure ({$httpCode}):</h2> <pre>" . htmlspecialchars($response) . "</pre>";
}
?>