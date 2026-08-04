<?php
// Tự động phát hiện môi trường Local hay Azure
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Local XAMPP
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $database = 'timeless';
} else {
    // Azure Production
    $host     = 'webbandongho-db2026.mysql.database.azure.com';
    $user     = 'dbadmin';
    $password = 'Manh2005';
    $database = 'timeless';
}

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>