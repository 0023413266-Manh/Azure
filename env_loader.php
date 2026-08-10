<?php
// File: env_loader.php (Đặt tại thư mục gốc)
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        return;
    }

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

// Chạy tự động nạp môi trường
loadEnv();
?>