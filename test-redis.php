<?php
/**
 * File: test_redis.php - Kiểm tra kết nối Azure Redis (Cổng 10000)
 */
require_once __DIR__ . '/env_loader.php';

$host = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: 'redis-timeless-v3.southeastasia.redis.azure.net';
$port = $_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 10000;
$key  = $_ENV['REDIS_KEY']  ?? getenv('REDIS_KEY')  ?: 'du5a8MPPQFSYJHSnTSfNTAn_XmtpFvTxxAZCAGCaWio=';

echo "<h2>🔍 KIỂM TRA KẾT NỐI AZURE REDIS</h2>";
echo "<b>Host:</b> " . htmlspecialchars($host) . "<br>";
echo "<b>Port:</b> " . htmlspecialchars($port) . "<br>";
echo "<b>Key:</b> " . substr($key, 0, 8) . "****************...<br><hr>";

if (!class_exists('Redis')) {
    die("<h3 style='color:red;'>❌ LỖI: PHP của bạn chưa bật extension Redis (php_redis)!</h3>");
}

try {
    $redis = new Redis();
    // Cổng 10000 trên Azure Redis Enterprise bắt buộc sử dụng mã hóa TLS
    $tlsHost = (strpos($host, 'tls://') === 0) ? $host : 'tls://' . $host;

    echo "⏳ Đang thử kết nối tới $tlsHost...<br>";

    if ($redis->connect($tlsHost, (int)$port, 3.0)) {
        if ($redis->auth($key)) {
            echo "<h2 style='color:green;'>🎉 KẾT NỐI THÀNH CÔNG 100%!</h2>";

            // Thử ghi dữ liệu mẫu
            $testVal = "Timeless Watch Redis OK - " . date('H:i:s Y-m-d');
            $redis->setex("test_timeless", 60, $testVal);

            // Thử đọc lại dữ liệu
            $readVal = $redis->get("test_timeless");
            echo "✅ <b>Ghi & Đọc dữ liệu thành công từ Azure Redis:</b> <b style='color:blue;'>$readVal</b><br>";
        } else {
            echo "<h3 style='color:red;'>❌ LỖI XÁC THỰC: REDIS_KEY bị sai!</h3>";
        }
    } else {
        echo "<h3 style='color:red;'>❌ LỖI KẾT NỐI: Không kết nối được tới Host/Port!</h3>";
    }
} catch (Exception $e) {
    echo "<h3 style='color:red;'>❌ LỖI EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>