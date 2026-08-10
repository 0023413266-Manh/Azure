<?php
/**
 * File: azure_translator.php
 * Mô tả: Thư viện tích hợp dịch thuật tự động sử dụng Azure AI Translator Service (v3.0)
 * Dự án: Website Đồng hồ Timeless
 */

require_once __DIR__ . '/env_loader.php';

// 1. CẤU HÌNH THÔNG TIN KẾT NỐI (TỰ ĐỘNG LẤY KEY CHO CẢ XAMPP VÀ AZURE CLOUD)
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
    if (empty(trim($text)) || $toLang === 'vi' || empty(AZURE_TRANSLATOR_KEY)) {
        return $text;
    }

    $endpoint = "https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&to=" . urlencode($toLang);

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
        CURLOPT_SSL_VERIFYPEER => false,
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


/**
 * 3. HÀM DỊCH NGUYÊN TRANG HTML (DỊCH NGUYÊN TRANG - KHÔNG CẮT CODE)
 */
function azureTranslateHTML($htmlContent, $toLang = 'en') {
    // Nếu là Tiếng Việt hoặc chưa cấu hình Key thì giữ nguyên
    if (empty(trim($htmlContent)) || $toLang === 'vi' || empty(AZURE_TRANSLATOR_KEY)) {
        return $htmlContent;
    }

    // Khởi tạo Session lưu bộ nhớ tạm
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Cache kết quả dịch theo trang để web chạy siêu mượt
    $cacheKey = 'trans_page_' . $toLang . '_' . md5($htmlContent);
    if (isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    $endpoint = "https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&to=" . urlencode($toLang) . "&textType=html";

    $requestBody = json_encode([
        ['Text' => $htmlContent]
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
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
            $translatedHTML = $result[0]['translations'][0]['text'];
            $_SESSION[$cacheKey] = $translatedHTML;
            return $translatedHTML;
        }
    }

    return $htmlContent;
}
?>
