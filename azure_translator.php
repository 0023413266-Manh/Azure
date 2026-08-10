<?php
/**
 * File: azure_translator.php
 * Mô tả: Thư viện tích hợp dịch thuật tự động sử dụng Azure AI Translator Service (v3.0)
 * Dự án: Website Đồng hồ Timeless
 */

// =========================================================================
// 1. CẤU HÌNH THÔNG TIN KẾT NỐI AZURE PORTAL (Đã dán Key & Region)
// =========================================================================
require_once __DIR__ . '/env_loader.php';

if (!defined('AZURE_TRANSLATOR_KEY')) {
    define('AZURE_TRANSLATOR_KEY', $_ENV['AZURE_TRANSLATOR_KEY'] ?? '');
}

if (!defined('AZURE_TRANSLATOR_REGION')) {
    define('AZURE_TRANSLATOR_REGION', $_ENV['AZURE_TRANSLATOR_REGION'] ?? 'global');
}


/**
 * 2. HÀM DỊCH VĂN BẢN THUẦN (Plain Text)
 */
function azureTranslate($text, $toLang = 'en') {
    if (empty(trim($text))) {
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
 * 3. HÀM DỊCH NGUYÊN TRANG HTML (Full Page HTML Translation)
 */
function azureTranslateHTML($htmlContent, $toLang = 'en') {
    if (empty(trim($htmlContent)) || $toLang == 'vi') {
        return $htmlContent;
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
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false, // BẮT BUỘC: Giúp chạy mượt trên Localhost XAMPP
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Ocp-Apim-Subscription-Key: ' . AZURE_TRANSLATOR_KEY,
            'Ocp-Apim-Subscription-Region: ' . AZURE_TRANSLATOR_REGION,
            'Content-Type: application/json; charset=UTF-8'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if (isset($result[0]['translations'][0]['text'])) {
            return $result[0]['translations'][0]['text'];
        }
    }

    // --- DEBUG TẠM THỜI: sẽ xoá sau khi tìm ra nguyên nhân ---
    echo "\n<!-- AZURE TRANSLATE DEBUG: httpCode=$httpCode | curlError=$curlError | response=" . htmlspecialchars(substr($response ?? '', 0, 500)) . " -->\n";

    return $htmlContent;
}