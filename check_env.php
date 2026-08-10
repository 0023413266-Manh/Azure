<?php
echo "<h2>🔍 BÁO CÁO CHẨN ĐOÁN CHI TIẾT .ENV</h2>";

$envFile = __DIR__ . '/.env';

// 1. Kiểm tra file .env có tồn tại không
if (file_exists($envFile)) {
    echo "<p style='color:green;'>✅ <b>Đã tìm thấy file .env chuẩn!</b></p>";
    
    // Đọc các tên biến có trong file
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<b>Danh sách tên biến đang có trong file .env của bạn:</b><ul>";
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || empty($line)) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            echo "<li><b>" . htmlspecialchars(trim($key)) . "</b></li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ <b>KHÔNG tìm thấy file .env!</b></p>";
    echo "<b>Các file liên quan đến 'env' đang có trong thư mục của bạn:</b><ul>";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if (strpos($file, 'env') !== false || strpos($file, '.') === 0) {
            echo "<li><code>" . htmlspecialchars($file) . "</code></li>";
        }
    }
    echo "</ul>";
}
?>