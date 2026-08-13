<?php
require_once __DIR__ . '/env_loader.php';

function uploadToAzureBlob($filePath, $fileName) {
    $account   = $_ENV['AZURE_STORAGE_ACCOUNT'] ?? getenv('AZURE_STORAGE_ACCOUNT') ?: 'webdongho2026';
    $accessKey = $_ENV['AZURE_STORAGE_KEY']     ?? getenv('AZURE_STORAGE_KEY');
    $container = 'blob-anh-dongho';

    // Đơn giản hóa Key (xóa khoảng trắng thừa nếu có)
    $accessKey = trim($accessKey);

    if (!$accessKey) {
        echo "<p style='color:red;'>❌ Chưa đọc được AZURE_STORAGE_KEY từ file .env!</p>";
        return false;
    }

    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $cleanFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($fileName, PATHINFO_FILENAME));
    $blobName      = time() . '_' . $cleanFileName . '.' . $fileExtension;

    $date          = gmdate('D, d M Y H:i:s \G\M\T');
    $fileContent   = file_get_contents($filePath);
    $contentLength = strlen($fileContent);

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath) ?: 'image/jpeg';
    finfo_close($finfo);

    $canonicalizedHeaders  = "x-ms-blob-type:BlockBlob\nx-ms-date:$date\nx-ms-version:2020-10-02";
    $canonicalizedResource = "/$account/$container/$blobName";
    
    $stringToSign = "PUT\n\n\n$contentLength\n\n$mimeType\n\n\n\n\n\n\n$canonicalizedHeaders\n$canonicalizedResource";
    $signature    = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($accessKey), true));

    $endpoint = "https://$account.blob.core.windows.net/$container/$blobName";

    $headers = [
        "x-ms-blob-type: BlockBlob",
        "x-ms-date: $date",
        "x-ms-version: 2020-10-02",
        "Content-Type: $mimeType",
        "Content-Length: $contentLength",
        "Authorization: SharedKey $account:$signature"
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 201) {
        return $endpoint;
    } else {
        // IN LỖI CHI TIẾT
        echo "<div style='background: #fff0f0; border: 1px solid red; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h4 style='color: red; margin-top:0;'>⚠️ PHÂN TÍCH LỖI TỪ AZURE:</h4>";
        echo "<b>HTTP Code:</b> $httpCode <br>";
        if ($curlErr) echo "<b>cURL Error:</b> $curlErr <br>";
        echo "<b>Tên Account:</b> $account <br>";
        echo "<b>Container:</b> $container <br>";
        echo "<b>Azure Phản Hồi:</b> <pre>" . htmlspecialchars($response) . "</pre>";
        echo "</div>";
        return false;
    }
}
?>