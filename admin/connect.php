<?php
ob_start();
// Nạp file .env nếu có (dùng env_loader.php đã tạo)
if (file_exists(__DIR__ . '/../env_loader.php')) {
    require_once __DIR__ . '/../env_loader.php';
} elseif (file_exists(__DIR__ . '/env_loader.php')) {
    require_once __DIR__ . '/env_loader.php';
}

// Tự động phát hiện môi trường Local hay Azure Production
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Local XAMPP
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $database = 'timeless';
} else {
    // Azure Production
    $host     = $_ENV['DB_HOST'] ?? 'webbandongho-db2026.mysql.database.azure.com';
    $user     = $_ENV['DB_USER'] ?? 'dbadmin';
    $password = getSecretFromKeyVault('DBPassword');
    $database = $_ENV['DB_NAME'] ?? 'timeless';
}

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ==========================================
// LẤY SECRET TỪ AZURE KEY VAULT
// ==========================================
function getSecretFromKeyVault($secretName) {
    $vaultName = $_ENV['AZURE_KEYVAULT_NAME'] ?? 'kv-timeless-btl';
    $identityEndpoint = getenv('IDENTITY_ENDPOINT');
    $identityHeader   = getenv('IDENTITY_HEADER');

    if (!$identityEndpoint || !$identityHeader) {
        return null;
    }

    $tokenUrl = $identityEndpoint . "?resource=https://vault.azure.net&api-version=2019-08-01";
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-IDENTITY-HEADER: $identityHeader"]);
    $tokenResponse = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($tokenResponse['access_token'])) {
        die("Không thể xác thực với Key Vault.");
    }

    $secretUrl = "https://$vaultName.vault.azure.net/secrets/$secretName?api-version=7.4";
    $ch = curl_init($secretUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $tokenResponse['access_token']]);
    $secretResponse = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $secretResponse['value'] ?? null;
}

// ==========================================
// CẤU HÌNH REDIS CACHE (LẤY TỪ FILE .ENV)
// ==========================================
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? 'redis-timeless-v3.southeastasia.redis.azure.net');
define('REDIS_PORT', $_ENV['REDIS_PORT'] ?? 10000);
define('REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? '');

function redis_connect() {
    if (empty(REDIS_PASSWORD)) return null;
    
    $fp = @stream_socket_client("tls://" . REDIS_HOST . ":" . REDIS_PORT, $errno, $errstr, 5);
    if (!$fp) return null;
    
    fwrite($fp, "*2\r\n\$4\r\nAUTH\r\n\$" . strlen(REDIS_PASSWORD) . "\r\n" . REDIS_PASSWORD . "\r\n");
    fgets($fp, 512);
    return $fp;
}

function redis_get($key) {
    $fp = redis_connect();
    if (!$fp) return null;
    fwrite($fp, "*2\r\n\$3\r\nGET\r\n\$" . strlen($key) . "\r\n" . $key . "\r\n");
    $line = fgets($fp, 4096);
    if ($line === false || $line[0] !== '$') { fclose($fp); return null; }
    $len = intval(substr($line, 1));
    if ($len === -1) { fclose($fp); return null; }
    $data = fread($fp, $len + 2);
    fclose($fp);
    return substr($data, 0, $len);
}

function redis_set($key, $value, $ttl = 60) {
    $fp = redis_connect();
    if (!$fp) return false;
    fwrite($fp, "*5\r\n\$3\r\nSET\r\n\$" . strlen($key) . "\r\n" . $key . "\r\n\$" . strlen($value) . "\r\n" . $value . "\r\n\$2\r\nEX\r\n\$" . strlen($ttl) . "\r\n" . $ttl . "\r\n");
    fgets($fp, 512);
    fclose($fp);
    return true;
}

function redis_del($key) {
    $fp = redis_connect();
    if (!$fp) return false;
    fwrite($fp, "*2\r\n\$3\r\nDEL\r\n\$" . strlen($key) . "\r\n" . $key . "\r\n");
    fgets($fp, 512);
    fclose($fp);
    return true;
}

// ==========================================
// HÀM CHUẨN HÓA ĐƯỜNG DẪN ẢNH TOÀN TRANG
// ==========================================
if (!function_exists('show_img_url')) {
    function show_img_url($path) {
        $path = trim($path ?? '');
        if (empty($path)) return '../image/seiko1-1.png'; // Ảnh mặc định nếu rỗng
        
        // 1. Link online (HTTP/HTTPS) -> Giữ nguyên tuyệt đối
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }
        
        // 2. Link cục bộ (Local) -> Đảm bảo có ../ ở đầu
        if (strpos($path, '../') === 0) {
            return $path;
        }
        return '../' . ltrim($path, '/');
    }
}
?>