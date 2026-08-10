<?php
header('Content-Type: application/json; charset=utf-8');

// =========================================================================
// THÔNG TIN CẤU HÌNH AZURE AI DOCUMENT INTELLIGENCE
// =========================================================================

require_once __DIR__ . '/env_loader.php';

$endpoint        = $_ENV['DOC_INTEL_ENDPOINT'] ?? '';
$subscriptionKey = $_ENV['DOC_INTEL_KEY'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $pdfPath = $_FILES['pdf_file']['tmp_name'];

    if (!file_exists($pdfPath) || filesize($pdfPath) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'File PDF không hợp lệ!']);
        exit;
    }

    $pdfData = file_get_contents($pdfPath);

    // 1. GỬI FILE PDF LÊN AZURE AI ĐỂ PHÂN TÍCH (Dùng model prebuilt-layout hoặc prebuilt-read)
    $analyzeUrl = rtrim($endpoint, '/') . '/formrecognizer/documentModels/prebuilt-layout:analyze?api-version=2023-07-31';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $analyzeUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdfData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // Lấy cả Header để lấy Operation-Location
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        'Content-Type: application/pdf'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 202) {
        echo json_encode(['status' => 'error', 'message' => 'Không thể gửi file tới Azure AI.']);
        exit;
    }

    // 2. LẤY URL TRẠNG THÁI TỪ HEADER "Operation-Location"
    preg_match('/Operation-Location:\s*(.*)\r\n/i', $response, $matches);
    $operationUrl = isset($matches[1]) ? trim($matches[1]) : '';

    if (empty($operationUrl)) {
        echo json_encode(['status' => 'error', 'message' => 'Không lấy được đường dẫn xử lý từ Azure.']);
        exit;
    }

    // 3. VÒNG LẶP CHO AI XỬ LÝ (Đợi khoảng 1 - 3 giây)
    $maxTries = 10;
    $status = 'running';

    while ($maxTries > 0 && $status === 'running') {
        sleep(1); // Chờ 1 giây trước khi hỏi lại
        
        $chState = curl_init();
        curl_setopt($chState, CURLOPT_URL, $operationUrl);
        curl_setopt($chState, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chState, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chState, CURLOPT_HTTPHEADER, [
            'Ocp-Apim-Subscription-Key: ' . $subscriptionKey
        ]);

        $resState = curl_exec($chState);
        curl_close($chState);

        $resultData = json_decode($resState, true);
        $status = $resultData['status'] ?? 'failed';

        if ($status === 'succeeded') {
            // 4. LẤY TOÀN BỘ CHỮ NỘI DUNG ĐÃ ĐƯỢC AI TRÍCH XUẤT
            $extractedContent = $resultData['analyzeResult']['content'] ?? '';

            echo json_encode([
                'status'  => 'success',
                'content' => $extractedContent, // Trả về toàn bộ nội dung PDF dưới dạng Text
                'raw'     => $resultData
            ]);
            exit;
        }

        $maxTries--;
    }

    echo json_encode(['status' => 'error', 'message' => 'Xử lý file quá thời gian quy định.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Request không hợp lệ']);