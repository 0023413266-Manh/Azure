<?php
// CẤU HÌNH AZURE SIGNALR CỦA BẠN
// THÔNG BÁO ĐƠN HÀNG CHO ADMIN KO CẦN F5

// 1. Nhúng file đọc biến môi trường
require_once __DIR__ . '/env_loader.php';

// 2. Định nghĩa các hằng số SignalR lấy từ file .env
if (!defined('SIGNALR_ENDPOINT')) {
    define('SIGNALR_ENDPOINT', $_ENV['SIGNALR_ENDPOINT'] ?? '');
}

if (!defined('SIGNALR_ACCESS_KEY')) {
    define('SIGNALR_ACCESS_KEY', $_ENV['SIGNALR_ACCESS_KEY'] ?? '');
}

if (!defined('SIGNALR_HUB')) {
    define('SIGNALR_HUB', $_ENV['SIGNALR_HUB'] ?? 'orderHub');
}

// Hàm tạo JWT Token chuẩn cho Azure SignalR
function generateSignalRToken($audience, $accessKey, $expiresIn = 3600) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'aud' => $audience,
        'exp' => time() + $expiresIn,
        'iat' => time()
    ]);

    $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
    $base64UrlPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $accessKey, true);
    $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

// Hàm PHP dùng để BẮN THÔNG BÁO từ Server sang Azure SignalR
function sendOrderNotification($orderData) {
    $hubUrl = SIGNALR_ENDPOINT . '/api/v1/hubs/' . SIGNALR_HUB;
    $token = generateSignalRToken($hubUrl, SIGNALR_ACCESS_KEY);

    $payload = json_encode([
        'target' => 'NewOrderEvent', // Tên sự kiện lắng nghe
        'arguments' => [$orderData]
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $hubUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Xử lý API Negotiate cho JavaScript Client kết nối
if (isset($_GET['action']) && $_GET['action'] === 'negotiate') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $clientUrl = SIGNALR_ENDPOINT . '/client/?hub=' . SIGNALR_HUB;
    $token = generateSignalRToken($clientUrl, SIGNALR_ACCESS_KEY);

    echo json_encode([
        'url' => $clientUrl,
        'accessToken' => $token
    ]);
    exit;
}
?>