<?php
require_once __DIR__ . '/azure_blob_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['anh_test'])) {
    $filePath = $_FILES['anh_test']['tmp_name'];
    $fileName = $_FILES['anh_test']['name'];

    echo "<h3>⏳ Đang đẩy ảnh lên Azure Blob Storage...</h3>";
    
    $imageUrl = uploadToAzureBlob($filePath, $fileName);

    if ($imageUrl) {
        echo "<h2 style='color: green;'>🎉 UPLOAD THÀNH CÔNG 100%!</h2>";
        echo "<p><b>URL Azure Blob:</b> <a href='$imageUrl' target='_blank'>$imageUrl</a></p>";
        echo "<p><b>Hình ảnh thực tế từ Cloud:</b></p>";
        echo "<img src='$imageUrl' style='max-width: 300px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);'>";
    } else {
        echo "<h2 style='color: red;'>❌ UPLOAD THẤT BẠI!</h2>";
        echo "<p>Kiểm tra lại AZURE_STORAGE_KEY trong file .env nhé.</p>";
    }
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Upload Azure Blob Storage</title>
</head>
<body style="font-family: Arial; padding: 30px;">
    <h2>🔍 TEST UPLOAD ẢNH LÊN AZURE BLOB STORAGE</h2>
    <form method="POST" enctype="multipart/form-data">
        <p>Chọn một ảnh đồng hồ bất kỳ từ máy tính:</p>
        <input type="file" name="anh_test" accept="image/*" required>
        <br><br>
        <button type="submit" style="padding: 10px 20px; background: #0078d4; color: white; border: none; cursor: pointer;">
            Upload Lên Azure Blob
        </button>
    </form>
</body>
</html>