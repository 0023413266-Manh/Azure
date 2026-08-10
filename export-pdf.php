<?php
// 1. Nội dung HTML bạn muốn chuyển thành PDF
$htmlContent = '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        h1 { color: #0078d4; text-align: center; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>HÓA ĐƠN BÁN HÀNG - TIMELESS2</h1>
    <p><strong>Ngày xuất:</strong> ' . date('d/m/Y') . '</p>
    
    <table class="table">
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
        </tr>
        <tr>
            <td>Dịch vụ PDF Azure</td>
            <td>1</td>
            <td>100.000 VNĐ</td>
        </tr>
    </table>
</body>
</html>
';

// 2. Link API Azure Functions của bạn
$apiUrl = 'https://timeless-pdf-service-c0cfbuewcfg6c6f0.southeastasia-01.azurewebsites.net/api/GeneratePDF';

// 3. Cấu hình cURL để gửi request POST dạng JSON sang Azure
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'html' => $htmlContent
]));

// Bỏ qua kiểm tra SSL nếu chạy ở Localhost XAMPP bị lỗi certificate
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

// 4. Giải mã kết quả trả về từ Azure
$data = json_decode($response, true);

if (isset($data['success']) && $data['success'] && !empty($data['pdfBase64'])) {
    // Giải mã chuỗi Base64 thành dữ liệu file PDF
    $pdfBinary = base64_decode($data['pdfBase64']);

    // Ép trình duyệt tự động tải về file PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="HoaDon_Timeless2.pdf"');
    echo $pdfBinary;
    exit;
} else {
    // Báo lỗi nếu không nhận được PDF
    echo 'Có lỗi xảy ra khi tạo PDF!';
}
?>