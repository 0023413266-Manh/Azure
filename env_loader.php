<?php
// File: env_loader.php (Đã tích hợp nạp .env Local + Azure Key Vault Cloud)

function loadEnv($path = __DIR__ . '/.env') {
    // =========================================================================
    // 1. ĐỌC FILE .ENV DƯỚI LOCAL (XAMPP)
    // =========================================================================
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Bỏ qua dòng comment bắt đầu bằng #
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Tách name = value
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Bỏ dấu ngoặc kép nếu có
                $value = trim($value, '"\'');

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    // =========================================================================
    // 2. TỰ ĐỘNG LẤY SECRET TỪ AZURE KEY VAULT (KHI CHẠY TRÊN AZURE CLOUD)
    // =========================================================================
    $identityEndpoint = getenv('IDENTITY_ENDPOINT');
    $identityHeader   = getenv('IDENTITY_HEADER');

    // Nếu phát hiện Managed Identity (Chỉ xuất hiện khi chạy trên Azure Web App)
    if ($identityEndpoint && $identityHeader) {
        try {
            // A. BƯỚC 1: XIN ACCESS TOKEN
            $tokenUrl = $identityEndpoint . "?resource=https://vault.azure.net&api-version=2019-08-01";
            $ch = curl_init($tokenUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ["X-IDENTITY-HEADER: $identityHeader"],
                CURLOPT_TIMEOUT        => 5
            ]);
            $tokenResponse = curl_exec($ch);
            curl_close($ch);

            $tokenData = json_decode($tokenResponse, true);

            // B. BƯỚC 2: RÚT DBPassword TỪ KEY VAULT kv-timeless-btl
            if (isset($tokenData['access_token'])) {
                $vaultName  = 'kv-timeless-btl';
                $secretName = 'DBPassword';
                $secretUrl  = "https://$vaultName.vault.azure.net/secrets/$secretName?api-version=7.4";

                $ch = curl_init($secretUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => ["Authorization: Bearer " . $tokenData['access_token']],
                    CURLOPT_TIMEOUT        => 5
                ]);
                $secretResponse = curl_exec($ch);
                curl_close($ch);

                $secretData = json_decode($secretResponse, true);

                // C. GHI ĐÈ DB_PASSWORD TỪ KEY VAULT VÀO HỆ THỐNG
                if (isset($secretData['value'])) {
                    $dbPass = $secretData['value'];
                    putenv("DB_PASSWORD={$dbPass}");
                    $_ENV['DB_PASSWORD']    = $dbPass;
                    $_SERVER['DB_PASSWORD'] = $dbPass;
                }
            }
        } catch (Exception $e) {
            // Lỗi Key Vault thì dùng cấu hình dự phòng từ .env
        }
    }
}

// Chạy tự động nạp môi trường
loadEnv();
?>