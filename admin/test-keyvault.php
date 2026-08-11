<?php
header('Content-Type: text/plain');

$identityEndpoint = getenv('IDENTITY_ENDPOINT');
$identityHeader   = getenv('IDENTITY_HEADER');

echo "=== KIỂM TRA MANAGED IDENTITY ===\n";
echo "IDENTITY_ENDPOINT: " . ($identityEndpoint ?: 'KHÔNG TỒN TẠI') . "\n";
echo "IDENTITY_HEADER tồn tại: " . (!empty($identityHeader) ? 'CÓ' : 'KHÔNG') . "\n\n";

if (!$identityEndpoint || !$identityHeader) {
    die("❌ Managed Identity chưa được cấu hình hoặc code đang chạy trên localhost.\n");
}

echo "=== BƯỚC 1: XIN ACCESS TOKEN ===\n";
$tokenUrl = $identityEndpoint . "?resource=https://vault.azure.net&api-version=2019-08-01";
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-IDENTITY-HEADER: $identityHeader"]);
$tokenResponse = curl_exec($ch);

if (curl_errno($ch)) {
    die("❌ Lỗi cURL khi xin token: " . curl_error($ch) . "\n");
}
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    die("❌ Không lấy được access_token. Phản hồi thô:\n" . $tokenResponse . "\n");
}
echo "✅ Lấy access_token thành công!\n\n";

echo "=== BƯỚC 2: LẤY SECRET TỪ KEY VAULT ===\n";
$vaultName = 'kv-timeless-btl';
$secretName = 'DBPassword';
$secretUrl = "https://$vaultName.vault.azure.net/secrets/$secretName?api-version=7.4";

$ch = curl_init($secretUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $tokenData['access_token']]);
$secretResponse = curl_exec($ch);

if (curl_errno($ch)) {
    die("❌ Lỗi cURL khi lấy secret: " . curl_error($ch) . "\n");
}
curl_close($ch);

$secretData = json_decode($secretResponse, true);
if (isset($secretData['value'])) {
    echo "✅ Lấy secret thành công! (Ẩn giá trị thật vì lý do bảo mật)\n";
    echo "Độ dài mật khẩu: " . strlen($secretData['value']) . " ký tự\n";
} else {
    echo "❌ Không lấy được secret. Phản hồi thô:\n" . $secretResponse . "\n";
}
?>