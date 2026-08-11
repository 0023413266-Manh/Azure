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
    $password = getSecretFromKeyVault('DBPassword');
    $database = 'timeless';
}

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function getSecretFromKeyVault($secretName) {
    $vaultName = 'kv-timeless-btl';
    $identityEndpoint = getenv('IDENTITY_ENDPOINT');
    $identityHeader   = getenv('IDENTITY_HEADER');

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
?>