<?php
/**
 * SCRIPT CHUYỂN ĐỔI MẬT KHẨU: MD5 -> bcrypt (password_hash)
 * 
 * CẢNH BÁO: Chạy script này 1 lần duy nhất, rồi XÓA FILE NÀY ĐI!
 * Truy cập: http://localhost/Timeless2/migrate_passwords.php
 */

// Bảo vệ: Chỉ chạy từ localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('<h2 style="color:red;">Truy cập bị từ chối. Chỉ chạy từ localhost!</h2>');
}

include 'admin/connect.php';

echo '<style>body{font-family:Arial;padding:30px;max-width:700px;margin:auto;}</style>';
echo '<h2>🔐 Script Chuyển Đổi Mật Khẩu: MD5 → bcrypt</h2>';
echo '<hr>';

// =============================================
// 1. KIỂM TRA BẢNG nguoi_dung
// =============================================
echo '<h3>📋 Bảng: nguoi_dung</h3>';

$result = $conn->query("SELECT id, mat_khau FROM nguoi_dung");
$count_nd = 0;
$skip_nd = 0;

while ($row = $result->fetch_assoc()) {
    $old_hash = $row['mat_khau'];
    
    // Nếu đã là bcrypt (bắt đầu bằng $2y$), bỏ qua
    if (substr($old_hash, 0, 4) === '$2y$' || substr($old_hash, 0, 4) === '$2a$') {
        $skip_nd++;
        continue;
    }
    
    echo "<p style='color:#cc7700'>⚠️ User ID #{$row['id']}: Đang dùng MD5 cũ.</p>";
    $count_nd++;
}

if ($count_nd === 0 && $skip_nd > 0) {
    echo "<p style='color:green'>✅ Tất cả {$skip_nd} tài khoản đã dùng bcrypt. Không cần chuyển đổi!</p>";
} elseif ($count_nd > 0) {
    echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;border-radius:8px;margin:15px 0;'>";
    echo "<strong>⚠️ THÔNG BÁO QUAN TRỌNG:</strong><br><br>";
    echo "Có <strong>{$count_nd}</strong> tài khoản đang dùng mật khẩu MD5 cũ.<br><br>";
    echo "Vì MD5 không thể giải mã ngược, các tài khoản cũ sẽ không thể đăng nhập cho đến khi đặt lại mật khẩu.<br><br>";
    echo "<strong>Giải pháp:</strong> Yêu cầu người dùng dùng chức năng <a href='forgot.php'>Quên mật khẩu</a> để đặt lại mật khẩu mới.";
    echo "</div>";
}

// =============================================
// 2. KIỂM TRA BẢNG admin
// =============================================
echo '<h3>👑 Bảng: admin</h3>';

$result_admin = $conn->query("SELECT id, username, password FROM admin");
$count_admin = 0;
$skip_admin = 0;

while ($row = $result_admin->fetch_assoc()) {
    $old_hash = $row['password'];
    
    // Nếu đã là bcrypt, bỏ qua
    if (substr($old_hash, 0, 4) === '$2y$' || substr($old_hash, 0, 4) === '$2a$') {
        $skip_admin++;
        echo "<p style='color:green'>✅ Admin '<strong>{$row['username']}</strong>': Đã dùng bcrypt.</p>";
        continue;
    }
    
    echo "<p style='color:#cc7700'>⚠️ Admin '<strong>{$row['username']}</strong>': Đang dùng MD5.</p>";
    $count_admin++;
}

if ($count_admin > 0) {
    echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;border-radius:8px;margin:15px 0;'>";
    echo "<strong>Cần cập nhật thủ công mật khẩu admin.</strong><br><br>";
    echo "Chạy lệnh SQL sau trong phpMyAdmin (thay 'admin123' bằng mật khẩu thực):<br><br>";
    
    $sample_hash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<code style='background:#f1f1f1;padding:10px;display:block;word-break:break-all;'>";
    echo "UPDATE admin SET password = '" . htmlspecialchars($sample_hash) . "' WHERE username = 'admin';";
    echo "</code>";
    echo "</div>";
}

echo '<hr>';
echo '<p style="color:#dc3545;font-weight:bold;">⚠️ Sau khi xem xong, hãy XÓA FILE NÀY (migrate_passwords.php) để bảo mật!</p>';
?>
