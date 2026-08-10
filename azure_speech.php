<?php
header('Content-Type: application/json; charset=utf-8');

// =========================================================================
// THÔNG TIN CẤU HÌNH AZURE SPEECH
// =========================================================================
require_once __DIR__ . '/env_loader.php';

// 2. Lấy THÔNG TIN CẤU HÌNH AZURE SPEECH từ file .env
$subscriptionKey = $_ENV['AZURE_SPEECH_KEY'] ?? '';
$region          = $_ENV['AZURE_SPEECH_REGION'] ?? 'southeastasia';                                                       // Region Azure

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {
    $audioFile = $_FILES['audio']['tmp_name'];
    $lang      = isset($_POST['lang']) ? $_POST['lang'] : 'vi-VN';

    if (!file_exists($audioFile) || filesize($audioFile) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'File âm thanh rỗng']);
        exit;
    }

    $audioData = file_get_contents($audioFile);

    // Endpoint Azure Speech STT REST API
    $url = "https://{$region}.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1?language={$lang}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $audioData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Tắt SSL verify trên localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        'Content-Type: audio/webm; codecs=opus',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode == 200 && isset($result['RecognitionStatus']) && $result['RecognitionStatus'] === 'Success') {
        
        $text = $result['DisplayText'];

        // 1. XÓA CÁC TỪ PHÁT ÂM DẤU CÂU BỊ BẮT NHẦM (phẩy, chấm, dấu phẩy, dấu chấm...)
        $punctuation_words = ['/\bdấu phẩy\b/iu', '/\bphẩy\b/iu', '/\bdấu chấm\b/iu', '/\bchấm\b/iu'];
        $text = preg_replace($punctuation_words, ' ', $text);

        // 2. XÓA SẠCH TẤT CẢ DẤU CÂU (phẩy, chấm, chấm hỏi, cảm thán...) NẰM TRONG CÂU
        $text = preg_replace('/[.,?!;:"\'\(\)]/u', ' ', $text);

        // 3. CHUẨN HÓA KHOẢNG TRẮNG DƯ THỪA
        $cleanText = trim(preg_replace('/\s+/', ' ', $text));

        // 4. BỘ LỌC TỪ ĐỒNG ÂM: Tự động uốn nắn các từ Azure nghe nhầm thành tên Thương Hiệu chuẩn
        $brand_map = [
            '/\bmì gà\b/iu'       => 'Omega',
            '/\bô mê ga\b/iu'     => 'Omega',
            '/\bô mề gà\b/iu'     => 'Omega',
            '/\brô lếch\b/iu'     => 'Rolex',
            '/\brô lắc\b/iu'      => 'Rolex',
            '/\bhúp lốt\b/iu'     => 'Hublot',
            '/\bhúp lố\b/iu'      => 'Hublot',
            '/\bca xio\b/iu'      => 'Casio',
            '/\bsây cô\b/iu'      => 'Seiko',
            '/\bxê cô\b/iu'       => 'Seiko',
            '/\bxi ti zen\b/iu'   => 'Citizen',
            '/\bba tếch\b/iu'      => 'Patek Philippe'
        ];

        // Thay thế các từ nghe nhầm trong chuỗi thu được
        $cleanText = preg_replace(array_keys($brand_map), array_values($brand_map), $cleanText);

        echo json_encode([
            'status' => 'success',
            'text'   => $cleanText
        ]);
        exit;
    }

    echo json_encode([
        'status'   => 'error',
        'httpCode' => $httpCode,
        'azureRaw' => $result
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);