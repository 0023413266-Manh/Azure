<?php
// azure_backup_full.php - Sao lưu CẢ SOURCE CODE LẪN DATABASE (.ZIP) lên Azure
date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- 1. THÔNG TIN DATABASE ---
require_once __DIR__ . '/env_loader.php';

// --- 1. THÔNG TIN DATABASE (Lấy từ .env) ---
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? 'timeless';

// --- 2. THÔNG TIN AZURE STORAGE (Lấy từ .env) ---
$azureStorageAccount = $_ENV['AZURE_STORAGE_ACCOUNT'] ?? ''; 
$azureContainer      = $_ENV['AZURE_BACKUP_CONTAINER'] ?? 'database-backups';
$azureSasToken       = $_ENV['AZURE_SAS_TOKEN'] ?? '';

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

$timestamp = date('Y-m-d_H-i-s');
$sqlFileName = 'db_' . $db_name . '_' . $timestamp . '.sql';
$sqlFilePath = $backupDir . '/' . $sqlFileName;
$zipFileName = 'full_backup_' . $db_name . '_' . $timestamp . '.zip';
$zipFilePath = $backupDir . '/' . $zipFileName;

// -------------------------------------------------------------
// BƯỚC 1: XUẤT DATABASE MYSQL
// -------------------------------------------------------------
$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
if (!file_exists($mysqldumpPath)) $mysqldumpPath = 'mysqldump';

$cmd = "\"{$mysqldumpPath}\" --host={$db_host} --user={$db_user} " . ($db_pass ? "--password=\"{$db_pass}\" " : "") . "{$db_name} > \"{$sqlFilePath}\" 2>&1";
exec($cmd, $output, $returnVar);

if ($returnVar !== 0 || !file_exists($sqlFilePath)) {
    die("❌ Lỗi xuất Database!");
}

// -------------------------------------------------------------
// BƯỚC 2: NÉN SOURCE CODE + FILE .SQL THÀNH FILE .ZIP
// -------------------------------------------------------------
$zip = new ZipArchive();
if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // 1. Thêm file SQL vào Zip
    $zip->addFile($sqlFilePath, $sqlFileName);

    // 2. Thêm toàn bộ file Source Code (trừ thư mục backups để tránh bị lặp vô tận)
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(__DIR__) + 1);

            // Bỏ qua thư mục backups
            if (strpos($relativePath, 'backups') !== 0) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    $zip->close();
} else {
    die("❌ Lỗi tạo file ZIP!");
}

echo "1. ✅ Đã đóng gói thành công Full Backup (Code + DB): <code>{$zipFileName}</code> (" . round(filesize($zipFilePath)/(1024*1024), 2) . " MB)<br>";

// -------------------------------------------------------------
// BƯỚC 3: ĐẨY FILE .ZIP LÊN AZURE BLOB STORAGE
// -------------------------------------------------------------
$fileData = file_get_contents($zipFilePath);
$blobUrl = "https://{$azureStorageAccount}.blob.core.windows.net/{$azureContainer}/{$zipFileName}{$azureSasToken}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $blobUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-ms-blob-type: BlockBlob',
    'Content-Type: application/zip',
    'Content-Length: ' . strlen($fileData)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    echo "2. 🚀 <b>ĐÃ ĐẨY TRỌN BỘ CODE + DATABASE LÊN AZURE THÀNH CÔNG!</b>";
} else {
    echo "2. ❌ Lỗi đẩy lên Azure Storage! Mã lỗi: " . $httpCode;
}

// Xóa file tạm cục bộ sau khi đã đẩy xong để nhẹ máy
@unlink($sqlFilePath);
?>