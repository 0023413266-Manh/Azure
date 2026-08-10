<?php
session_start();
include 'admin/connect.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// -------------------------------------------------------------
// HÀM QUÉT BẢO HÀNH BẰNG AZURE AI DOCUMENT INTELLIGENCE
// -------------------------------------------------------------
function analyzeDocumentAzure($filePath) {
    // ⚠️ SAU NÀY TẠO DỊCH VỤ TRÊN AZURE XONG BẠN ĐIỀN ENDPOINT VÀ KEY VÀO 2 DÒNG NÀY:
// 1. Nhúng file đọc biến môi trường (Lưu ý điều chỉnh đường dẫn nếu file nằm trong thư mục con)
require_once __DIR__ . '/env_loader.php';

// 2. Lấy Endpoint và Key từ file .env
$azure_endpoint = $_ENV['DOC_INTEL_ENDPOINT'] ?? ''; 
$azure_key      = $_ENV['DOC_INTEL_KEY'] ?? ''; 

// 3. Đường dẫn API Document Intelligence
$url = rtrim($azure_endpoint, '/') . "/documentintelligence/documentModels/prebuilt-invoice:analyze?api-version=2024-02-29-preview";

    $fileData = file_get_contents($filePath);

    // 1. Gửi request phân tích ảnh
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/octet-stream',
        'Ocp-Apim-Subscription-Key: ' . $azure_key
    ));

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);

    // Lấy URL kết quả từ Header
    preg_match('/Operation-Location:\s*(.*)\r\n/i', $headers, $matches);
    if (!isset($matches[1])) return null;

    $resultUrl = trim($matches[1]);

    // 2. Chờ Azure AI trả kết quả
    for ($i = 0; $i < 10; $i++) {
        sleep(2);
        $ch = curl_init($resultUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Ocp-Apim-Subscription-Key: ' . $azure_key
        ));
        $resJson = curl_exec($ch);
        curl_close($ch);

        $resData = json_decode($resJson, true);
        if (isset($resData['status']) && $resData['status'] === 'succeeded') {
            return $resData['analyzeResult'];
        }
    }
    return null;
}


// =============================================================
// THƯỜNG TRƯỜNG HỢP 1: XỬ LÝ KHÁCH BẤM "QUÉT BẰNG AI"
// =============================================================
if (isset($_POST['btn_scan_ai']) && isset($_FILES['file_bao_hanh'])) {
    if ($_FILES['file_bao_hanh']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['file_bao_hanh']['tmp_name'];
        $rawResult = analyzeDocumentAzure($tmpPath);

        $ai_result = [
            'ma_dong_ho' => '',
            'so_series'  => '',
            'ngay_mua'   => ''
        ];

        // Trích xuất các trường từ AI
        if ($rawResult && isset($rawResult['documents'][0]['fields'])) {
            $fields = $rawResult['documents'][0]['fields'];
            
            if (isset($fields['InvoiceId']['valueString'])) $ai_result['so_series'] = $fields['InvoiceId']['valueString'];
            if (isset($fields['InvoiceDate']['valueDate'])) $ai_result['ngay_mua'] = $fields['InvoiceDate']['valueDate'];
            if (isset($fields['Items']['valueArray'][0]['valueObject']['Description']['valueString'])) {
                $ai_result['ma_dong_ho'] = $fields['Items']['valueArray'][0]['valueObject']['Description']['valueString'];
            }
        }

        // Lưu vào Session để đẩy về lại form
        $_SESSION['ai_scan_result'] = $ai_result;
        $_SESSION['toast_msg']  = "AI đã quét xong! Vui lòng kiểm tra lại thông tin.";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg']  = "Tải ảnh thất bại!";
        $_SESSION['toast_type'] = "error";
    }

    header("Location: bao_hanh.php");
    exit();
}


// =============================================================
// TRƯỜNG HỢP 2: XỬ LÝ KHÁCH BẤM "GỬI YÊU CẦU BẢO HÀNH"
// =============================================================
if (isset($_POST['btn_gui_bao_hanh'])) {
    $uid       = (int)$_SESSION['user_id'];
    $ho_ten    = $conn->real_escape_string($_POST['ho_ten']);
    $sdt       = $conn->real_escape_string($_POST['so_dien_thoai']);
    $ma_sp     = $conn->real_escape_string($_POST['ma_dong_ho']);
    $series    = $conn->real_escape_string($_POST['so_series']);
    $ngay_mua  = !empty($_POST['ngay_mua']) ? "'" . $conn->real_escape_string($_POST['ngay_mua']) . "'" : "NULL";
    $mo_ta     = $conn->real_escape_string($_POST['mo_ta_loi']);

    $sql = "INSERT INTO yeu_cau_bao_hanh (id_nguoi_dung, ho_ten, so_dien_thoai, ma_dong_ho, so_series, ngay_mua, mo_ta_loi)
            VALUES ($uid, '$ho_ten', '$sdt', '$ma_sp', '$series', $ngay_mua, '$mo_ta')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['toast_msg']  = "Gửi yêu cầu bảo hành thành công! TIMELESS sẽ sớm liên hệ lại.";
        $_SESSION['toast_type'] = "success";
        header("Location: index.php");
    } else {
        $_SESSION['toast_msg']  = "Lỗi hệ thống, không thể gửi yêu cầu lúc này.";
        $_SESSION['toast_type'] = "error";
        header("Location: bao_hanh.php");
    }
    exit();
}