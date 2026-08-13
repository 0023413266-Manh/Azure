<?php
/**
 * Hàm ghi Log tự động vào Azure File Share
 */
function write_system_log($action, $detail = '') {
    // Đường dẫn thư mục shared trên Azure File Share
    $log_dir = __DIR__ . '/shared/logs/'; 
    
    // Nếu thư mục chưa có (khi chạy local) thì tự tạo
    if (!file_exists($log_dir)) {
        @mkdir($log_dir, 0777, true);
    }

    $file_path = $log_dir . 'app_log_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $log_message = "[$timestamp] [IP: $ip] - Action: $action | Detail: $detail" . PHP_EOL;

    // Ghi nối tiếp vào file log trên Azure File Share
    file_put_contents($file_path, $log_message, FILE_APPEND);
}

// 🧪 Thử nghiệm ghi log kiểm tra:
write_system_log("CHECK_AZURE_SHARE", "Kiem tra ghi log thanh cong len Azure File Share!");
?>