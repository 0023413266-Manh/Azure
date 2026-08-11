<?php
/**
 * File: azure_translator.php
 * Mô tả: Thư viện tích hợp dịch thuật tự động sử dụng Azure AI Translator Service (v3.0)
 * Dự án: Website Đồng hồ Timeless
 */

require_once __DIR__ . '/env_loader.php';

// 1. CẤU HÌNH THÔNG TIN KẾT NỐI (HỖ TRỢ CẢ XAMPP LẪN AZURE CLOUD)
if (!defined('AZURE_TRANSLATOR_KEY')) {
    $key = $_ENV['AZURE_TRANSLATOR_KEY'] ?? getenv('AZURE_TRANSLATOR_KEY') ?: '';
    define('AZURE_TRANSLATOR_KEY', trim($key, " \t\n\r\0\x0B\"'"));
}

if (!defined('AZURE_TRANSLATOR_REGION')) {
    $region = $_ENV['AZURE_TRANSLATOR_REGION'] ?? getenv('AZURE_TRANSLATOR_REGION') ?: 'global';
    define('AZURE_TRANSLATOR_REGION', trim($region, " \t\n\r\0\x0B\"'"));
}


/**
 * 2. HÀM DỊCH VĂN BẢN THUẦN (Plain Text)
 */
function azureTranslate($text, $toLang = 'en') {
    if (empty(trim($text)) || $toLang === 'vi') {
        return $text;
    }

    if (empty(AZURE_TRANSLATOR_KEY)) {
        return $text;
    }

    return sendAzureTranslateRequest($text, $toLang, 'plain');
}


/**
 * 3. HÀM DỊCH NGUYÊN TRANG HTML (TỰ ĐỘNG CẮT CHUNK THÔNG MINH - KHÔNG MẤT FOOTER)
 */
function azureTranslateHTML($htmlContent, $toLang = 'en') {
    if (empty(trim($htmlContent)) || $toLang === 'vi' || empty(AZURE_TRANSLATOR_KEY)) {
        return $htmlContent;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Kiểm tra bộ nhớ tạm Cache (Nếu đã dịch trang này thì trả về luôn - 0.01s)
    $cacheKey = 'trans_page_' . $toLang . '_' . md5($htmlContent);
    if (isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    $length = mb_strlen($htmlContent, 'UTF-8');

    // NẾU TRANG Quá 40.000 KÝ TỰ: Chia đôi trang tại thẻ </div> giữa trang để tránh lỗi 50,000 chars của Azure
    if ($length > 40000) {
        $half = (int)($length / 2);
        $splitPos = mb_strpos($htmlContent, '</div>', $half, 'UTF-8');

        if ($splitPos !== false) {
            $splitPos += 6; // Bao gồm thẻ </div>
            $part1 = mb_substr($htmlContent, 0, $splitPos, 'UTF-8');
            $part2 = mb_substr($htmlContent, $splitPos, null, 'UTF-8');

            $translatedPart1 = sendAzureTranslateRequest($part1, $toLang, 'html');
            $translatedPart2 = sendAzureTranslateRequest($part2, $toLang, 'html');

            $finalHTML = $translatedPart1 . $translatedPart2;
        } else {
            $finalHTML = sendAzureTranslateRequest($htmlContent, $toLang, 'html');
        }
    } else {
        $finalHTML = sendAzureTranslateRequest($htmlContent, $toLang, 'html');
    }

    // Lưu kết quả vào Cache Session
    $_SESSION[$cacheKey] = $finalHTML;

    return $finalHTML;
}


/**
 * 4. HÀM HELPER GỬI API SANG AZURE TRANSLATOR
 */
function sendAzureTranslateRequest($text, $toLang, $type = 'html') {
    $textTypeParam = ($type === 'html') ? '&textType=html' : '';
    $endpoint = "https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&to=" . urlencode($toLang) . $textTypeParam;

    $requestBody = json_encode([
        ['Text' => $text]
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false, // BẮT BUỘC BỎ QUA CHECK SSL TRÊN XAMPP
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Ocp-Apim-Subscription-Key: ' . AZURE_TRANSLATOR_KEY,
            'Ocp-Apim-Subscription-Region: ' . AZURE_TRANSLATOR_REGION,
            'Content-Type: application/json; charset=UTF-8'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if (isset($result[0]['translations'][0]['text'])) {
            return $result[0]['translations'][0]['text'];
        }
    }

    return $text;
}
?>