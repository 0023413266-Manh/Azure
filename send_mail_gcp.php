<?php
require_once 'env_loader.php';

function sendOtpViaGoogleCloud($to_email, $otp_code) {
    $client_id     = $_ENV['GCP_CLIENT_ID'] ?? '';
    $client_secret = $_ENV['GCP_CLIENT_SECRET'] ?? '';
    $refresh_token = $_ENV['GCP_REFRESH_TOKEN'] ?? '';

    if (empty($client_id) || empty($client_secret) || empty($refresh_token)) {
        return false;
    }

    // 1. Dùng Refresh Token lấy Access Token mới từ Google Cloud
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refresh_token,
        'grant_type'    => 'refresh_token',
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($response['access_token'])) {
        return false;
    }

    $access_token = $response['access_token'];

    // 2. Tạo nội dung Email HTML gửi cho khách hàng
    $subject = "=?UTF-8?B?" . base64_encode("[" . $otp_code . "] Mã xác nhận khôi phục mật khẩu - Timeless") . "?=";
    $raw_message  = "To: $to_email\r\n";
    $raw_message .= "Subject: $subject\r\n";
    $raw_message .= "MIME-Version: 1.0\r\n";
    $raw_message .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $raw_message .= "
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
            <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 8px; border: 1px solid #ddd;'>
                <h2 style='color: #111; text-align: center; letter-spacing: 2px;'>TIMELESS WATCH STORE</h2>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>Xin chào,</p>
                <p>Bạn (hoặc ai đó) vừa yêu cầu đặt lại mật khẩu cho tài khoản tại Timeless. Mã OTP xác thực của bạn là:</p>
                <div style='text-align: center; margin: 25px 0;'>
                    <span style='font-size: 32px; font-weight: bold; color: #d9534f; letter-spacing: 6px; background: #fdf2f2; padding: 12px 25px; border-radius: 6px; border: 1px dashed #d9534f; display: inline-block;'>$otp_code</span>
                </div>
                <p style='font-size: 13px; color: #666;'>Mã này có hiệu lực trong <b>5 phút</b>. Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.</p>
            </div>
        </div>
    ";

    // Mã hóa Base64URL theo chuẩn của Gmail API
    $mime_base64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($raw_message));

    // 3. Gọi Gmail API trên Google Cloud gửi Email
    $gmail_url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';
    $ch = curl_init($gmail_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['raw' => $mime_base64]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);

    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code == 200);
}
?>