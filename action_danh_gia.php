<?php
session_start();
include 'admin/connect.php';

// -------------------------------------------------------------
// HÀM KIỂM DUYỆT NỘI DUNG BẰNG AZURE AI CONTENT SAFETY
// -------------------------------------------------------------
function checkContentSafetyAzure($text) {
require_once __DIR__ . '/env_loader.php';

// 2. Lấy Endpoint và Key của Azure Content Safety từ file .env
$azure_endpoint = $_ENV['CONTENT_SAFETY_ENDPOINT'] ?? ''; 
$azure_key      = $_ENV['CONTENT_SAFETY_KEY'] ?? '';
    $url = rtrim($azure_endpoint, '/') . "/contentsafety/text:analyze?api-version=2023-10-01";

    $data = array(
        "text" => $text,
        "categories" => array("Hate", "SelfHarm", "Sexual", "Violence")
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5s
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Ocp-Apim-Subscription-Key: ' . $azure_key
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (isset($result['categoriesAnalysis'])) {
            foreach ($result['categoriesAnalysis'] as $category) {
                // Severity > 0 là phát hiện nội dung độc hại / xúc phạm / vi phạm tiêu chuẩn
                if ($category['severity'] > 0) { 
                    return false; // VI PHẠM
                }
            }
        }
    }

    return true; // AN TOÀN
}

// -------------------------------------------------------------
// XỬ LÝ GỬI ĐÁNH GIÁ
// -------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];

if (isset($_POST['submit_danh_gia']) || isset($_POST['gui_danh_gia'])) {
    $sp_id   = (int)$_POST['id_san_pham'];
    $don_id  = isset($_POST['id_don_hang']) ? (int)$_POST['id_don_hang'] : 0;
    $so_sao  = (int)$_POST['so_sao'];
    $noi_dung = $conn->real_escape_string(trim($_POST['noi_dung']));
    $referer  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'profile-history.php';

    // 1. Kiểm tra đã mua hàng chưa
    $sql_check = "SELECT dh.id FROM don_hang dh
                  JOIN chi_tiet_don_hang ct ON ct.id_don_hang = dh.id
                  WHERE dh.id_nguoi_dung = $uid
                    AND ct.id_san_pham = $sp_id
                    AND dh.trang_thai IN ('Da giao', 'Đã giao', 'da_giao', 'Hoàn thành', 'hoan_thanh')
                  LIMIT 1";
    $result_check = $conn->query($sql_check);

    if (!$result_check || $result_check->num_rows == 0) {
        $_SESSION['toast_msg']  = "Bạn cần mua sản phẩm này trước khi đánh giá!";
        $_SESSION['toast_type'] = "error";
        header("Location: " . $referer);
        exit();
    }

    // 2. Kiểm tra trùng (chỉ kiểm tra trong cùng đơn hàng)
    $check_dup = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $uid AND id_san_pham = $sp_id AND id_don_hang = $don_id LIMIT 1");
    if ($check_dup && $check_dup->num_rows > 0) {
        $_SESSION['toast_msg']  = "Đơn hàng này bạn đã đánh giá rồi!";
        $_SESSION['toast_type'] = "warning";
        header("Location: " . $referer);
        exit();
    }

    // 3. 🛡️ BẢO VỆ AI: GỬI NỘI DUNG CHO AZURE CHECK TRƯỚC KHI LƯU
    if (!empty($noi_dung)) {
        $is_safe = checkContentSafetyAzure($noi_dung);
        if (!$is_safe) {
            $_SESSION['toast_msg']  = "Đánh giá chứa từ ngữ xúc phạm hoặc không phù hợp tiêu chuẩn cộng đồng!";
            $_SESSION['toast_type'] = "error";
            header("Location: " . $referer);
            exit();
        }
    }

    // 4. Upload ảnh (nếu có)
    $anh_danh_gia = null;
    if (isset($_FILES['anh_danh_gia']) && $_FILES['anh_danh_gia']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['anh_danh_gia']['tmp_name']);

        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['anh_danh_gia']['name'], PATHINFO_EXTENSION);
            $filename = 'review_' . $uid . '_' . $sp_id . '_' . time() . '.' . $ext;
            $upload_path = __DIR__ . '/image/review_images/' . $filename;
            
            if (!file_exists(__DIR__ . '/image/review_images/')) {
                mkdir(__DIR__ . '/image/review_images/', 0777, true);
            }

            if (move_uploaded_file($_FILES['anh_danh_gia']['tmp_name'], $upload_path)) {
                $anh_danh_gia = 'image/review_images/' . $filename;
            }
        }
    }

    if ($so_sao < 1 || $so_sao > 5) $so_sao = 5;

    // 5. Lưu vào CSDL
    $anh_val = $anh_danh_gia ? "'" . $conn->real_escape_string($anh_danh_gia) . "'" : "NULL";
    $conn->query("INSERT INTO danh_gia (id_san_pham, id_nguoi_dung, id_don_hang, so_sao, noi_dung, anh_danh_gia)
                  VALUES ($sp_id, $uid, $don_id, $so_sao, '$noi_dung', $anh_val)");

    $_SESSION['toast_msg']  = "Cảm ơn bạn đã đánh giá sản phẩm!";
    $_SESSION['toast_type'] = "success";
}

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'profile-history.php';
header("Location: " . $referer);
exit();
?>