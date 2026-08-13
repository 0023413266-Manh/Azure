<?php
/**
 * File: azure_translator.php
 * Mô tả: Thư viện tích hợp dịch thuật tự động Azure AI Translator Service (v3.0) + Azure Cache for Redis
 * Dự án: Website Đồng hồ Timeless
 */

require_once __DIR__ . '/env_loader.php';

// =========================================================================
// 1. CẤU HÌNH THÔNG TIN KẾT NỐI
// =========================================================================
if (!defined('AZURE_TRANSLATOR_KEY')) {
    $key = $_ENV['AZURE_TRANSLATOR_KEY'] ?? getenv('AZURE_TRANSLATOR_KEY') ?: '';
    define('AZURE_TRANSLATOR_KEY', trim($key, " \t\n\r\0\x0B\"'"));
}

if (!defined('AZURE_TRANSLATOR_REGION')) {
    $region = $_ENV['AZURE_TRANSLATOR_REGION'] ?? getenv('AZURE_TRANSLATOR_REGION') ?: 'global';
    define('AZURE_TRANSLATOR_REGION', trim($region, " \t\n\r\0\x0B\"'"));
}

if (!defined('REDIS_HOST')) {
    $rHost = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: '';
    define('REDIS_HOST', trim($rHost, " \t\n\r\0\x0B\"'"));
}
if (!defined('REDIS_PORT')) {
    $rPort = $_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: '6380';
    define('REDIS_PORT', (int)trim($rPort, " \t\n\r\0\x0B\"'"));
}
if (!defined('REDIS_KEY')) {
    $rKey = $_ENV['REDIS_KEY'] ?? getenv('REDIS_KEY') ?: ($_ENV['REDIS_PASSWORD'] ?? getenv('REDIS_PASSWORD') ?: '');
    define('REDIS_KEY', trim($rKey, " \t\n\r\0\x0B\"'"));
}

// =========================================================================
// 2. HÀM KẾT NỐI AZURE REDIS
// =========================================================================
function getRedisInstance() {
    static $redis = null;
    if ($redis !== null) return $redis;
    if (empty(REDIS_HOST) || empty(REDIS_KEY)) return false;

    try {
        if (class_exists('Redis')) {
            $r = new Redis();
            $host = (REDIS_PORT === 6380 && strpos(REDIS_HOST, 'tls://') !== 0) ? 'tls://' . REDIS_HOST : REDIS_HOST;
            if ($r->connect($host, REDIS_PORT, 2.0)) {
                if ($r->auth(REDIS_KEY)) {
                    $redis = $r;
                    return $redis;
                }
            }
        }
    } catch (Exception $e) {}
    return false;
}

// =========================================================================
// 3. HÀM DỊCH TEXT THUẦN
// =========================================================================
function azureTranslate($text, $toLang = 'en') {
    if (empty(trim($text)) || $toLang === 'vi' || empty(AZURE_TRANSLATOR_KEY)) {
        return $text;
    }
    return sendAzureTranslateRequest($text, $toLang, 'plain');
}

// =========================================================================
// 4. HÀM DỊCH HTML AN TOÀN TỐI ĐA (BẢO VỆ CẤU TRÚC DOM 100%)
// =========================================================================
function azureTranslateHTML($htmlContent, $toLang = 'en') {
    if (empty(trim($htmlContent)) || $toLang === 'vi' || empty(AZURE_TRANSLATOR_KEY)) {
        return $htmlContent;
    }

    // Đổi Cache Key v5 để xóa hoàn toàn dữ liệu lỗi trước đó trong Redis
    $cacheKey = 'trans_page_v5_' . $toLang . '_' . md5($htmlContent);

    // 1. Kiểm tra Cache Redis
    $redis = getRedisInstance();
    if ($redis) {
        try {
            if ($redis->exists($cacheKey)) return $redis->get($cacheKey);
        } catch (Exception $e) {}
    }

    // 2. Kiểm tra Cache Session
    if (session_status() === PHP_SESSION_NONE) @session_start();
    if (isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    // 3. BẢO VỆ & NÉN NỘI DUNG: RÚT STYLE, SCRIPT VÀ URL RA KHỎI HTML TRƯỚC KHI DỊCH
    $styleMap  = [];
    $scriptMap = [];
    $urlMap    = [];

    // 3a. Tách toàn bộ khối CSS (<style>...</style>)
    $workHtml = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/is', function($m) use (&$styleMap) {
        $token = 'AZSTYLE' . count($styleMap) . 'AZ';
        $styleMap[$token] = $m[0];
        return $token;
    }, $htmlContent);

    // 3b. Tách toàn bộ khối JS (<script>...</script>)
    $workHtml = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/is', function($m) use (&$scriptMap) {
        $token = 'AZSCRIPT' . count($scriptMap) . 'AZ';
        $scriptMap[$token] = $m[0];
        return $token;
    }, $workHtml);

    // 3c. Tách các URL dài (src="...", href="...")
    $workHtml = preg_replace_callback('/(src|href)=["\']([^"\']+)["\']/i', function($m) use (&$urlMap) {
        $attr = $m[1];
        $url  = $m[2];
        if (strlen($url) > 20 || strpos($url, 'blob.core.windows.net') !== false) {
            $token = 'AZURL' . count($urlMap) . 'AZ';
            $urlMap[$token] = $url;
            return $attr . '="' . $token . '"';
        }
        return $m[0];
    }, $workHtml);

    // 4. GỬI API DỊCH NGUYÊN BẢN (KHÔNG CẮT CHUNK ĐỂ TRÁNH LỖI ĐÓNG THẺ DIV)
    $translatedHTML = sendAzureTranslateRequest($workHtml, $toLang, 'html');

    // 5. KHÔI PHỤC LẠI URL, SCRIPT VÀ STYLE
    if (!empty($urlMap)) {
        foreach ($urlMap as $token => $originalUrl) {
            $translatedHTML = str_replace($token, $originalUrl, $translatedHTML);
            // Xử lý trường hợp Azure tự thêm khoảng trắng vào Token
            $num = str_replace(['AZURL', 'AZ'], '', $token);
            $translatedHTML = preg_replace('/AZ\s*URL\s*' . $num . '\s*AZ/i', $originalUrl, $translatedHTML);
        }
    }

    if (!empty($scriptMap)) {
        foreach ($scriptMap as $token => $originalScript) {
            $translatedHTML = str_replace($token, $originalScript, $translatedHTML);
            $num = str_replace(['AZSCRIPT', 'AZ'], '', $token);
            $translatedHTML = preg_replace('/AZ\s*SCRIPT\s*' . $num . '\s*AZ/i', $originalScript, $translatedHTML);
        }
    }

    if (!empty($styleMap)) {
        foreach ($styleMap as $token => $originalStyle) {
            $translatedHTML = str_replace($token, $originalStyle, $translatedHTML);
            $num = str_replace(['AZSTYLE', 'AZ'], '', $token);
            $translatedHTML = preg_replace('/AZ\s*STYLE\s*' . $num . '\s*AZ/i', $originalStyle, $translatedHTML);
        }
    }

    // 6. LƯU BẢN DỊCH VÀO REDIS VÀ SESSION
    if ($redis && !empty($translatedHTML)) {
        try {
            $redis->setex($cacheKey, 604800, $translatedHTML);
        } catch (Exception $e) {}
    }
    $_SESSION[$cacheKey] = $translatedHTML;

    return $translatedHTML;
}

// =========================================================================
// 5. HÀM GỬI CURL SANG AZURE TRANSLATOR API
// =========================================================================
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
        CURLOPT_TIMEOUT        => 15,
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
?>